<?php

namespace App\Actions\Booking;

use App\Actions\GiftCard\GiftCardSpendPointsAction;
use App\Enum\Booking\StepEnum;
use App\Jobs\Member\MemberReminderCompleteRegistrationJob;
use App\Models\Booking;
use App\Models\Program;
use Exception;

class BookingCompletePaymentAction
{
    public function handle(Booking $booking)
    {
        $payment = $booking->payment;

        if ($coupon = $booking->coupon) {
            if (!$coupon->isExpired()) {
                $payment->offer_code = $coupon->code;
                $coupon->incrementUsage()->save();
            }
        }

        if ($booking->gift_card_number && $booking->gift_card_amount && $booking->giftCard) {
            try {
                (new GiftCardSpendPointsAction())->handle(
                    $booking->giftCard,
                    $booking->gift_card_number,
                    $booking->gift_card_amount
                );
            } catch (Exception $exception) {
                report($exception);
            }
        }

        $booking->step = StepEnum::MembershipDetails;
        $booking->save();

        if ($booking->membershipRenewal && $memberActivePaymentSchedule = $booking->membershipRenewal->member->memberActivePaymentSchedule) {
            $memberActivePaymentSchedule->markAsCompleted()
                ->save();
        }

        $remindTime = $booking->plan->package->program->source === Program::SOURCE_MAP['hsbc']
            ? config('advplus.hsbc_member_reminder_complete_notify_after')
            : config('advplus.member_reminder_complete_notify_after');

        dispatch(new MemberReminderCompleteRegistrationJob($booking->id))->delay(now()->addMinutes($remindTime));
    }
}
