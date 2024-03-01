<?php

namespace App\Services\Payment\PaymentMethods;

use App\Exceptions\Payments\MakePaymentException;

use App\Models\Payments\Payment;
use App\Models\Payments\PaymentTransaction;

use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;

use Illuminate\Http\Client\PendingRequest;

// Note: Last slash on tabby api is required

class TabbyPaymentMethod
{
    public const STATUSES = [
        'authorized' => 'AUTHORIZED',
        'rejected' => 'REJECTED',
        'expired' => 'EXPIRED',
        'created' => 'CREATED',
        'closed' => 'CLOSED',
    ];

    private readonly string $secretKey;

    protected PendingRequest $httpClient;

    protected string $merchantId = 'advplus';

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;

        $http = new PendingRequest();

        $this->httpClient = $http->baseUrl('https://api.tabby.ai/')
            ->withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            // ->retry(3, 50)
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function setMerchantId(string $merchantId): self
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    public function makePayment(Payment $payment, Customer $customer, Product $product, array $params): \stdClass
    {
        $total_amount = booking_amount_round($payment->total_amount);
        $transaction = new PaymentTransaction();
        $transaction->amount = $total_amount;
        $transaction->payment()->associate($payment);
        $transaction->paymentMethod()->associate($payment->paymentMethod);
        $transaction->save();

        $urls = [
            'success' => route('booking.payment.tabby-result', [
                'booking' => $params['booking'],
                'paymentTransaction' => $transaction,
            ]),
            'failure' => route('booking.payment.tabby-result', [
                'booking' => $params['booking'],
                'paymentTransaction' => $transaction,
                'status' => 'failed',
            ]),
            'cancel' => route('booking.payment.tabby-result', [
                'booking' => $params['booking'],
                'paymentTransaction' => $transaction,
                'status' => 'cancel',
            ]),
        ];

        $request = [
            'payment' => [
                'amount' => $total_amount,
                'currency' => 'AED',
                'buyer' => [
                    'phone' => $customer->getPhone(),
                    'email' => $customer->getEmail(),
                    'name' => $customer->getFullName(),
                ],

                'order' => [
                    'reference_id' => $product->getReferenceId(),
                    'tax_amount' => $product->getVat(),
                    'discount_amount' => $product->getDiscount(),
                    'items' => [
                        [
                            'title' => $product->getTitle(),
                            'quantity' => 1,
                            'unit_price' => $product->getPrice(),
                            'category' => 'Membership',
                        ],
                    ],
                ],
                'buyer_history' => [
                    'registered_since' => now()->format('c'),
                    'loyalty_level' => 0,
                ],
            ],

            'lang' => 'en',
            'merchant_code' => $this->merchantId,
            'merchant_urls' => $urls,
        ];

        try {
            $response = $this->httpClient->post('api/v2/checkout', $request);
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

        $transaction->remote_id = $response->json('payment.id');
        $transaction->save();

        $availablePayments = [];

        if ($installments = $response->json('configuration.available_products.installments')) {
            foreach ($installments as $installment) {
                $availablePayments[$installment['installments_count'] + 1] = $installment['web_url'];
            }
        }

        if ($installments = $response->json('configuration.available_products.credit_card_installments')) {
            foreach ($installments as $installment) {
                $availablePayments[$installment['installments_count'] + 1] = $installment['web_url'];
            }
        }
        return (object)[
            'transaction' => $transaction,
            'paymentResponse' => $response->object(),
            'url' => $availablePayments,
        ];
    }

    public function getPayment(string $paymentId): mixed
    {
        $response = $this->httpClient
            ->get('api/v1/payments/'.$paymentId);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function capturePayment(string $paymentId, float $amount = null): mixed
    {
        $payment = $this->getPayment($paymentId);
        if ($payment
            && $payment['status'] == static::STATUSES['authorized']
            && (!$amount || $payment['amount'] == $amount)) {
            $response = $this->httpClient
                ->post("api/v1/payments/{$paymentId}/captures", ['amount' => $amount]);

            if ($response->successful()) {
                return $response->json();
            }
        }

        return null;
    }
}
