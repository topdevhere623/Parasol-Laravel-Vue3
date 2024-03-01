<?php

namespace App\Actions\Booking\BookingPayment;

use App\Actions\Booking\BookingCompletePaymentAction;
use App\Models\Payments\Payment;

class FocBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        $this->payment->status = Payment::STATUSES['unknown'];
        $this->payment->save();

        (new BookingCompletePaymentAction())->handle($this->booking);

        return route('booking.step-3', $this->booking);
    }
}
