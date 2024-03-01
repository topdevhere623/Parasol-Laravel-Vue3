<?php

namespace App\Observers;

use App\Enum\Booking\StepEnum;
use App\Jobs\Booking\BookingSendTelegramNotificationJob;
use App\Jobs\Lead\CreateFromBookingCompletedLeadJob;
use App\Jobs\Lead\CreateFromBookingStepTwoLeadJob;
use App\Jobs\Plecto\PushBookingPlectoJob;
use App\Models\Booking;
use App\Models\Lead\Lead;
use Illuminate\Support\Str;

class BookingObserver
{
    public function creating(Booking $booking): void
    {
        $booking->uuid = Str::uuid()->toString();
        $booking->old_id ??= Booking::withTrashed()->max('old_id') + 1;
    }

    public function created(Booking $booking): void
    {
        if (!$booking->reference_id) {
            $booking->reference_id = 'ADVPLUS-'.str_pad($booking->old_id, 5, '0', STR_PAD_LEFT);
            $booking->save();
        }
    }

    public function saving(Booking $booking): void
    {
        $booking->name = Str::title($booking->name);
        if ($booking->isDirty(
            [
                'plan_third_party_commission_amount',
                'extra_child_third_party_commission_amount',
                'extra_junior_third_party_commission_amount',
            ]
        )
        ) {
            $booking->total_third_party_commission_amount = array_sum([
                $booking->plan_third_party_commission_amount,
                $booking->extra_child_third_party_commission_amount,
                $booking->extra_junior_third_party_commission_amount,
            ]);

            $booking->total_third_party_commission_amount = $booking->total_third_party_commission_amount < 1
                ? 0
                : $booking->total_third_party_commission_amount;
        }

        if ($booking->isDirty('step')) {
            $booking->last_step_changed_at = now();
        }
    }

    public function saved(Booking $booking)
    {
        if ($booking->isDirty('step')) {
            if ($booking->step == StepEnum::Completed) {
                CreateFromBookingCompletedLeadJob::dispatch($booking)->chain(
                    [new BookingSendTelegramNotificationJob($booking)]
                );
            } else {
                BookingSendTelegramNotificationJob::dispatch($booking);
            }

            if ($booking->step == StepEnum::Payment) {
                $lead = Lead::latest()->firstWhere(['email' => $booking->email]);
                if ($lead) {
                    if (!$lead->first_name && !$lead->last_name) {
                        $lead->first_name = $booking->name;
                        $lead->save();
                    }
                    $booking->updateQuietly(['lead_id' => $lead->id]);
                }

                CreateFromBookingStepTwoLeadJob::dispatch($booking)->delay(now()->addMinutes(30));
            }
        }

        if ($booking->isDirty(['step', 'lead_id']) && $booking->step == StepEnum::Completed) {
            PushBookingPlectoJob::dispatch($booking)->onQueue('low');
        }
    }

    public function deleted(Booking $booking)
    {
        $booking->hsbcUsedCard()->delete();
    }
}
