<?php

namespace App\Notifications\Booking;

use App\Enum\Booking\StepEnum;

class StepOneBookingNotification extends BaseBookingNotification
{
    protected StepEnum $step = StepEnum::Default;

    public function telegramData(): array
    {
        $baseData = parent::telegramData();
        $booking = $this->getBooking();
        $clubsCount = $booking->clubs->count();
        $clubsMessage = $clubsCount > 5
            ? '<b>'.$clubsCount.' '.\Str::pluralStudly('club', $clubsCount).'</b>'
            : '<code>'.PHP_EOL.'- '.$booking->clubs->pluck('title')->implode(PHP_EOL.'- ').'</code>';
        $baseData[] = 'Clubs: '.$clubsMessage;
        if ($booking->gift_card_discount_amount > 0 && $booking->gift_card_number) {
            $baseData[] = 'Gift code: '.$booking->gift_card_number;
        }
        if ($coupon = $booking->coupon) {
            $couponOwner = $coupon->ownerTitle();
            $baseData[] = 'Coupon code:  <a href="'.\URL::backoffice(
                'coupons/'.$coupon->id
            ).'">'.$coupon->code.($couponOwner ? ' ('.$couponOwner.')' : '').'</a>';
        }
        return $baseData;
    }
}
