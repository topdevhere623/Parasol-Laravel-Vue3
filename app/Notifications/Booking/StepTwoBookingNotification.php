<?php

namespace App\Notifications\Booking;

use App\Enum\Booking\StepEnum;

class StepTwoBookingNotification extends BaseBookingNotification
{
    protected StepEnum $step = StepEnum::Payment;

    public function telegramData(): array
    {
        $baseData = parent::telegramData();
        $booking = $this->getBooking();

        $baseData[] = 'Payment method: '.$booking->paymentMethod->title;

        return $baseData;
    }
}
