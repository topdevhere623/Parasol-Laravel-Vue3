<?php

namespace App\Actions\Booking\BookingPayment;

use App\Actions\Booking\BookingCompletePaymentAction;
use App\Exceptions\Payments\MakePaymentException;
use App\Services\Payment\PaymentMethods\CheckoutPaymentMethod;
use Checkout\Models\Payments\TokenSource;

class CheckoutBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        throw_if(
            empty($params['payment_data']['token']),
            new MakePaymentException(MakePaymentException::CARD_TOKEN_IS_REQUIRED)
        );

        //        $checkoutPaymentMethod = new CardSource('4242424242424242', 10, 2024);
        //        $checkoutPaymentMethod->cvv = '100';

        \App::make(CheckoutPaymentMethod::class)
            ->makePayment(
                $this->payment,
                $this->customer,
                $this->product,
                new TokenSource($params['payment_data']['token'])
            );

        $this->payment->markAsPaid()
            ->save();

        (new BookingCompletePaymentAction())->handle($this->booking);

        return route('booking.payment.success', $this->booking);
    }
}
