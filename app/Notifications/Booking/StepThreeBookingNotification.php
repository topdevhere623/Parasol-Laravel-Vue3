<?php

namespace App\Notifications\Booking;

use App\Enum\Booking\StepEnum;

class StepThreeBookingNotification extends BaseBookingNotification
{
    protected StepEnum $step = StepEnum::MembershipDetails;

    public function telegramData(): array
    {
        $baseData = parent::telegramData();
        $booking = $this->getBooking();
        $lead = $booking->lead;
        if ($lead) {
            $baseData[] = "BDM: <a href=\"https://adv.nocrm.io/leads/{$lead->nocrm_id}\">{$lead->backofficeUser?->full_name}</a>";
        }

        return $baseData;
    }
}
