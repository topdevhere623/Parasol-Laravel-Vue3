<?php

namespace App\Actions\Booking\BookingPayment;

use App\Actions\Booking\BookingCompletePaymentAction;

class CashBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        (new BookingCompletePaymentAction())->handle($this->booking);

        return route('booking.step-3', $this->booking);
    }
}
