<?php

namespace App\Services\Payment\PaymentMethods;

use App\Exceptions\Payments\MakePaymentException;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentTransaction;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;
use Http\Client\Exception\HttpException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

// Docs: https://paymentservices-reference.payfort.com/docs/api/build/index.html#standard-merchant-page-integration
// Test card: 4188 8700 0000 0002, future date, cvv: 123

class AmazonPayfortPaymentMethod
{
    protected string $sandboxApiUrl = 'https://sbcheckout.payfort.com/FortAPI';

    protected string $productionApiUrl = 'https://checkout.payfort.com/FortAPI';

    protected string $apiUrl;

    protected PendingRequest $client;

    protected string $SHARequestPhrase;

    protected string $SHAResponsePhrase;

    protected string $SHAType = 'sha256';

    protected string $merchantIdentifier;

    protected string $accessCode;

    private bool $sandboxMode;

    public const SIGNATURE_ENCODE_REQUEST = 'request';

    public const SIGNATURE_ENCODE_RESPONSE = 'response';

    public const RESPONSE_CODES = [
        'purchase_success' => 14000,
        '3ds_required' => 20064,
    ];

    public function __construct(
        string $merchantIdentifier,
        string $accessCode,
        string $SHARequestPhrase,
        string $SHAResponsePhrase,
        string $SHAType = 'sha256',
        bool $sandboxMode = true
    ) {
        $this->merchantIdentifier = $merchantIdentifier;
        $this->accessCode = $accessCode;
        $this->SHARequestPhrase = $SHARequestPhrase;
        $this->SHAResponsePhrase = $SHAResponsePhrase;
        $this->SHAType = $SHAType;
        $this->sandboxMode = $sandboxMode;

        $this->apiUrl = $this->sandboxMode ? $this->sandboxApiUrl
            : $this->productionApiUrl;

        $this->client = Http::baseUrl($this->apiUrl)
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(3, 50)
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function makePayment(
        Payment $payment,
        Customer $customer,
        Product $product,
        array $params
    ): object {
        $tokenizationResponse = $params['tokenizationResponse'];

        $transaction = new PaymentTransaction();
        $transaction->amount = $payment->total_amount;
        $transaction->payment()->associate($payment);
        $transaction->paymentMethod()
            ->associate($payment->paymentMethod);
        $transaction->save();

        $data['command'] = 'PURCHASE';
        $data['access_code'] = $this->accessCode;
        $data['merchant_identifier'] = $this->merchantIdentifier;
        $data['merchant_reference'] = $product->getReferenceId();
        $data['amount'] = $product->getTotalPrice() * 100;
        $data['currency'] = 'AED';
        $data['language'] = 'en';
        $data['return_url'] = $params['returnUrl'];
        $data['check_3ds'] = 'NO';

        $data['installments'] = 'YES';
        $data['plan_code'] = $tokenizationResponse->plan_code;
        $data['issuer_code'] = $tokenizationResponse->issuer_code;

        $data['customer_email'] = $customer->getEmail();
        $data['customer_ip'] = request()->ip();
        $data['customer_name'] = $customer->getFullName();
        $data['token_name'] = $tokenizationResponse->token_name;

        $data['signature'] = $this->calculateSignature($data);

        $url = null;
        try {
            $httpQuery = $this->client->post('paymentApi', $data);
            $httpQuery->throw();

            $paymentResponse = $httpQuery->json();

            if ($paymentResponse['response_code'] == self::RESPONSE_CODES['purchase_success']) {
                $transaction->attachResponse(json_encode($paymentResponse));
                $transaction->remote_id = $paymentResponse['fort_id'];
                $transaction->markAsSuccess()
                    ->save();
            } else {
                $transaction->description = $paymentResponse['response_message'];
                report(new \Exception($httpQuery->body()));
                throw new MakePaymentException();
            }
        } catch (HttpException $exception) {
            $transaction->markAsFail()
                ->save();

            report($exception);
            throw $exception;
        } catch (\Exception $exception) {
            $transaction->attachResponse($httpQuery->body());
            $transaction->markAsFail()
                ->save();
            report($exception);
            throw $exception;
        }

        return (object)[
            'transaction' => $transaction,
            'paymentResponse' => $paymentResponse,
            'url' => $url,
        ];
    }

    public function calculateSignature($arrData, $signType = self::SIGNATURE_ENCODE_REQUEST)
    {
        $shaString = '';
        ksort($arrData);
        foreach ($arrData as $k => $v) {
            $shaString .= "{$k}={$v}";
        }

        if ($signType == self::SIGNATURE_ENCODE_REQUEST) {
            $shaString = $this->SHARequestPhrase.$shaString
                .$this->SHARequestPhrase;
        } else {
            $shaString = $this->SHAResponsePhrase.$shaString
                .$this->SHAResponsePhrase;
        }

        return hash($this->SHAType, $shaString);
    }

    public function getTokenizationApiUrl()
    {
        return $this->apiUrl.'/paymentPage';
    }

    public function tokenizationRequest(Product $product, array $additionalData = []): array
    {
        $data['service_command'] = 'TOKENIZATION';
        $data['amount'] = $product->getTotalPrice() * 100;
        $data['access_code'] = $this->accessCode;
        $data['merchant_identifier'] = $this->merchantIdentifier;
        $data['merchant_reference'] = $product->getReferenceId();
        $data['language'] = 'en';
        $data['currency'] = 'AED';

        // Installments data
        $data['installments'] = 'STANDALONE';
        $data['customer_country_code'] = 'UAE';

        $data = array_merge($data, $additionalData);

        $data['signature'] = $this->calculateSignature($data);

        return $data;
    }
}
