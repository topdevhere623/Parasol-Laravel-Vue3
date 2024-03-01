<?php

namespace App\Actions\Booking\BookingPayment;

use App\Actions\Booking\BookingCompletePaymentAction;
use App\Exceptions\Payments\MakePaymentException;
use App\Services\Payment\PaymentMethods\AmazonPayfortPaymentMethod;

class AmazonPayfortBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        throw_if(
            empty($params['payment_data']['token']),
            new MakePaymentException(MakePaymentException::CARD_TOKEN_IS_REQUIRED)
        );

        $params = [
            'returnUrl' => route('booking.payment.amazon-response', [$this->booking, $this->payment]),
            'tokenizationResponse' => json_decode(base64_decode($params['payment_data']['token'])),
        ];

        $this->product->setReferenceId(
            $this->product->getReferenceId().'-'.\Str::random(5)
        );

        $paymentResult = \App::make(AmazonPayfortPaymentMethod::class)
            ->makePayment($this->payment, $this->customer, $this->product, $params);

        $this->payment->markAsPaid()
            ->save();

        (new BookingCompletePaymentAction())->handle($this->booking);

        return $paymentResult->url ?? route('booking.payment.success', $this->booking);
    }
}
