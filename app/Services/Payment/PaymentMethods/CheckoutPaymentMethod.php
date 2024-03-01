<?php

namespace App\Services\Payment\PaymentMethods;

use App\Exceptions\Payments\MakePaymentException;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentTransaction;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;
use Checkout\CheckoutApi;
use Checkout\Library\Exceptions\CheckoutException;
use Checkout\Models\Payments\Capture;
use Checkout\Models\Payments\Customer as CheckoutCustomer;
use Checkout\Models\Payments\Method;
use Checkout\Models\Payments\Payment as CheckoutPayment;
use Checkout\Models\Payments\Voids;

class CheckoutPaymentMethod
{
    protected CheckoutApi $checkout;

    public function __construct($secretKey, $sandboxMode = -1)
    {
        $this->checkout = new CheckoutApi($secretKey, $sandboxMode);
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
        Method $checkoutPaymentMethod
    ): object {
        return $this->makePaymentRequest(
            $payment,
            $customer,
            $product,
            $checkoutPaymentMethod,
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
        Method $checkoutPaymentMethod
    ): object {
        return $this->makePaymentRequest(
            $payment,
            $customer,
            $product,
            $checkoutPaymentMethod
        );
    }

    /**
     * Capture or auth payment
     */
    protected function makePaymentRequest(
        Payment $payment,
        Customer $customer,
        Product $product,
        Method $checkoutPaymentMethod,
        bool $capture = false
    ): object {
        $transaction = $this->prepareTransaction(
            $payment,
            $capture ? PaymentTransaction::TYPES['capture'] : PaymentTransaction::TYPES['authorize']
        );

        $checkoutCustomer = new CheckoutCustomer();
        $checkoutCustomer->email = $customer->getEmail();
        $checkoutCustomer->name = $customer->getFullName();

        $amount = $payment->total_amount;
        $amount = (int)($amount * 100);

        $checkoutPayment = new CheckoutPayment($checkoutPaymentMethod, 'AED');
        $checkoutPayment->amount = $amount;
        $checkoutPayment->customer = $checkoutCustomer;
        $checkoutPayment->reference = $product->getReferenceId();
        $checkoutPayment->description = $product->getDescription();
        $checkoutPayment->payment_type = $payment->is_recurring ? 'Recurring' : 'Regular';
        $checkoutPayment->merchant_initiated = $payment->is_recurring;
        $checkoutPayment->capture = $capture;

        $checkoutPayment->metadata = [
            'udf1' => $product->getTitle(),
        ];

        try {
            $paymentResponse = $this->checkout->payments()->request($checkoutPayment);
        } catch (CheckoutException $exception) {
            report($exception);
            $transaction->attachResponse($exception->getBody());
            return $this->failAndThrow(
                $transaction,
                $exception,
                'Checkout Payment exception: '.ucfirst(str_replace('_', ' ', implode(',', $exception->getErrors())))
            );
        }

        $transaction->attachResponse(json_encode($paymentResponse));
        $transaction->remote_id = $paymentResponse->getId();

        if (!$paymentResponse->isSuccessful()) {
            $this->failAndThrow(
                $transaction,
                null,
                'Checkout error Code: '.$paymentResponse->response_code.'. Message: '.$paymentResponse->response_summary
            );
        }

        $transaction->markAsSuccess()
            ->save();

        return (object)[
            'transaction' => $transaction,
            'paymentResponse' => $paymentResponse,
        ];
    }

    /**
     * Capture authorized payment
     *
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function capture(string $checkoutPaymentID, Payment $payment): object
    {
        return $this->makeManageRequest($checkoutPaymentID, $payment);
    }

    /**
     * Void authorized payment
     *
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function void(string $checkoutPaymentID, Payment $payment): object
    {
        return $this->makeManageRequest($checkoutPaymentID, $payment, 'void');
    }

    /**
     * Void or capture authorized payment
     */
    protected function makeManageRequest(
        string $checkoutPaymentID,
        Payment $payment,
        string $action = 'capture'
    ): object {
        $transaction = $this->prepareTransaction(
            $payment,
            PaymentTransaction::TYPES[$action]
        );

        try {
            if ($action == 'capture') {
                $paymentResponse = $this->checkout->payments()->capture(new Capture($checkoutPaymentID));
            } else {
                $paymentResponse = $this->checkout->payments()->void(new Voids($checkoutPaymentID));
            }
        } catch (CheckoutException $exception) {
            $transaction->attachResponse($exception->getBody());
            $this->failAndThrow(
                $transaction,
                $exception,
                'Checkout Payment exception: '.ucfirst(str_replace('_', ' ', implode(',', $exception->getErrors())))
            );
        }

        $transaction->remote_id = $paymentResponse->getId();
        $transaction->attachResponse(json_encode($paymentResponse));

        if (!$paymentResponse->isSuccessful()) {
            $this->failAndThrow(
                $transaction,
                null,
                'Checkout error Code: '.$paymentResponse->response_code.'. Message: '.$paymentResponse->response_summary
            );
        }

        $transaction->markAsSuccess()->save();

        $details = $this->checkout->payments()->details($paymentResponse->id);

        return (object)[
            'transaction' => $transaction,
            'paymentResponse' => $details,
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
}
