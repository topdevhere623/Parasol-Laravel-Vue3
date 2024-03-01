<?php

namespace App\Services\Payment\PaymentMethods;

use App\Exceptions\Payments\MakePaymentException;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentTransaction;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;
use Carbon\Carbon;
use Checkout\Library\Exceptions\CheckoutException;
use Checkout\Models\Payments\Capture;
use Checkout\Models\Payments\Customer as CheckoutCustomer;
use Checkout\Models\Payments\Method;
use Checkout\Models\Payments\Payment as CheckoutPayment;
use Checkout\Models\Payments\Voids;
use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Symfony\Component\HttpFoundation\Response;

class PaytabsPaymentMethod
{
    public const TRAN_TYPE = [
        PaymentTransaction::TYPES['capture'] => 'sale',
        PaymentTransaction::TYPES['authorize'] => 'auth',
        PaymentTransaction::TYPES['refund'] => 'refund',
        PaymentTransaction::TYPES['void'] => 'void',
    ];

    protected PendingRequest $httpClient;

    public function __construct(private readonly string $serverKey, private readonly string $profileId)
    {
        $http = new PendingRequest();

        $this->httpClient = $http->baseUrl('https://secure.paytabs.com/')
            ->withToken($this->serverKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(3, 5000, function ($exception) {
                $code = $exception->getCode();
                return $exception instanceof RequestException && ($code == Response::HTTP_REQUEST_TIMEOUT || $code >= 500);
            })
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    protected function prepareTransaction(
        Payment $payment,
        string $type = PaymentTransaction::TYPES['capture']
    ): PaymentTransaction {
        $transaction = new PaymentTransaction();
        $transaction->type = $type;
        $transaction->amount = $payment->total_amount;
        $transaction->payment()->associate($payment);
        $transaction->paymentMethod()->associate($payment->paymentMethod);
        $transaction->save();
        return $transaction;
    }

    /**
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    protected function failAndThrow(
        PaymentTransaction $transaction,
        \Exception $exception = null,
        ?string $message = null
    ) {
        $transaction->description = $message;
        $transaction->markAsFail()
            ->save();
        throw new MakePaymentException(MakePaymentException::DEFAULT_CARD_ERROR, $exception);
    }

    // Capture payment

    /**
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function makePayment(
        Payment $payment,
        Customer $customer,
        Product $product,
        Closure $returnCallback,
    ): object {
        return $this->makePaymentRequest(
            $payment,
            $customer,
            $product,
            $returnCallback,
            true
        );
    }

    /**
     * Auth payment
     *
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function makeAuth(
        Payment $payment,
        Customer $customer,
        Product $product,
        Closure $returnCallback,
    ): object {
        return $this->makePaymentRequest(
            $payment,
            $customer,
            $product,
            $returnCallback,
            false
        );
    }

    /**
     * Capture or auth payment
     */
    protected function makePaymentRequest(
        Payment $payment,
        Customer $customer,
        Product $product,
        Closure $returnCallback,
        bool $capture = false,
    ): object {
        $transaction = $this->prepareTransaction(
            $payment,
            $capture ? PaymentTransaction::TYPES['capture'] : PaymentTransaction::TYPES['authorize']
        );

        $data = [
            'profile_id' => $this->profileId,
            'tran_type' => static::TRAN_TYPE[$transaction->type],
            'tran_class' => 'ecom',
            'cart_id' => $transaction->uuid,
            'cart_description' => $product->getReferenceId(),
            'cart_currency' => 'AED',
            'cart_amount' => $product->getTotalPrice(),
            'callback' => 'https://iplog.prsl.cc',
            'hide_shipping' => true,
            'return' => call_user_func($returnCallback, $transaction),
            'customer_details' => [
                'name' => $customer->getFullName(),
                'email' => $customer->getEmail(),
                'phone_number' => $customer->getPhone(),
                'country_code' => 'AE',
            ],
        ];

        try {
            $response = $this->httpClient->post('payment/request', $data);
        } catch (\Exception $exception) {
            report($exception);
            $transaction->attachResponse(json_encode(['message' => $exception->getMessage()]));
            $transaction->markAsFail()
                ->save();
            throw new MakePaymentException(0, $exception);
        }

        $transaction->attachResponse(json_encode($response->json()));

        if ($response->failed()) {
            $transaction->markAsFail()
                ->save();
            throw new MakePaymentException(0);
        }

        $transaction->remote_id = $response->json('tran_ref');
        $transaction->save();

        return (object)[
            'transaction' => $transaction,
            'paymentResponse' => $response->object(),
        ];
    }

    /**
     * Capture authorized payment
     *
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function capture(string $tranRef, Payment $payment): object
    {
        return $this->makeManageRequest($tranRef, $payment);
    }

    /**
     * Void authorized payment
     *
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function void(string $tranRef, Payment $payment): object
    {
        return $this->makeManageRequest($tranRef, $payment, 'void');
    }

    /**
     * Void or capture authorized payment
     */
    protected function makeManageRequest(
        string $tranRef,
        Payment $payment,
        string $action = 'capture'
    ): object {
        $action = $action == 'capture' ? 'capture' : 'void';

        $transaction = $this->prepareTransaction(
            $payment,
            PaymentTransaction::TYPES[$action]
        );

        $data = [
            'profile_id' => $this->profileId,
            'tran_ref' => $tranRef,
            'tran_type' => static::TRAN_TYPE[$transaction->type],
            'tran_class' => 'ecom',
            'cart_id' => $transaction->uuid,
            'cart_description' => $payment->booking->reference_id,
            'cart_currency' => 'AED',
            'cart_amount' => $payment->total_amount,
        ];

        try {
            $response = $this->httpClient->post('payment/request', $data);
        } catch (\Exception $exception) {
            report($exception);
            $transaction->attachResponse(json_encode(['message' => $exception->getMessage()]));
            $transaction->markAsFail()
                ->save();
            throw new MakePaymentException(0, $exception);
        }

        $transaction->markAsSuccess()
            ->save();

        return (object)[
            'transaction' => $transaction,
            'paymentResponse' => $response->object(),
        ];
    }

    /**
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function attachCard(Customer $customer, Product $product, Method $checkoutPaymentMethod): mixed
    {
        $checkoutCustomer = new CheckoutCustomer();
        $checkoutCustomer->email = $customer->getEmail();
        $checkoutCustomer->name = $customer->getFullName();

        $checkoutPayment = new CheckoutPayment($checkoutPaymentMethod, 'AED');
        $checkoutPayment->amount = $product->getPrice() * 100;
        $checkoutPayment->customer = $checkoutCustomer;
        $checkoutPayment->reference = $product->getReferenceId();
        $checkoutPayment->description = $product->getDescription();
        $checkoutPayment->payment_type = 'Regular';
        $checkoutPayment->capture = false;

        $checkoutPayment->metadata = [
            'udf1' => $product->getReferenceId(),
        ];

        try {
            $authResponse = $this->checkout->payments()->request($checkoutPayment);
            $this->checkout->payments()->void(new Voids($authResponse->id));
        } catch (CheckoutException $exception) {
            throw new MakePaymentException(MakePaymentException::DEFAULT_CARD_ERROR, $exception);
        }

        return $authResponse;
    }

    public function getPayment(string $tranRef): mixed
    {
        $response = $this->httpClient
            ->post('payment/query', [
                'profile_id' => $this->profileId,
                'tran_ref' => $tranRef,
            ]);

        if ($response->successful()) {
            return $response->object();
        }

        return null;
    }

    public static function parseCardNumberDetails(object $paymentInfo): object
    {
        $responseCardNumber = str_replace(' ', '', $paymentInfo->payment_description);

        return (object)[
            'bin' => substr($responseCardNumber, 0, 6),
            'last_4_digits' => substr($responseCardNumber, 12, 4),
            'card_scheme' => str_contains(strtolower($paymentInfo->card_scheme), 'visa') ? 'visa' : 'mastercard',
            'card_expiry' => Carbon::createFromDate($paymentInfo->expiryYear, $paymentInfo->expiryMonth)->endOfMonth(),
        ];
    }
}
