<?php

namespace App\Actions\Booking\BookingPayment;

class TabbyFourBookingPaymentAction extends TabbyThreeBookingPaymentAction
{
    protected int $paymentsCount = 4;

    protected string $merchantId = 'advplus4';

}
