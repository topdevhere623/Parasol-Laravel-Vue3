<?php

namespace App\Jobs\Member;

use App\Enum\Booking\StepEnum;
use App\Mail\Booking\Reminder\HSBCMemberReminderCompleteRegistrationMail;
use App\Mail\Booking\Reminder\MemberReminderCompleteRegistrationMail;
use App\Mail\Booking\Reminder\TESoleilMemberReminderCompleteRegistrationMail;
use App\Models\Booking;
use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MemberReminderCompleteRegistrationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $bookingId;

    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function handle()
    {
        $booking = Booking::find($this->bookingId);

        if ($booking && $program = $booking->plan->package->program) {
            if ($program->source == Program::SOURCE_MAP['hsbc'] && $booking->step == StepEnum::MembershipDetails) {
                \Mail::to($booking->email)
                    ->send(
                        new HSBCMemberReminderCompleteRegistrationMail(
                            $booking->name,
                            route('booking.step-4', $booking)
                        )
                    );
            } elseif ($program->id == Program::ENTERTAINER_SOLEIL_ID && $booking->step == StepEnum::MembershipDetails) {
                \Mail::to($booking->email)
                    ->send(
                        new TESoleilMemberReminderCompleteRegistrationMail(
                            $booking->name,
                            route('booking.step-4', $booking),
                            $booking->plan->package->program->getWhatsappUrl()
                        )
                    );
            } elseif ($booking->step == StepEnum::BillingDetails || $booking->step == StepEnum::MembershipDetails) {
                \Mail::to($booking->email)
                    ->send(new MemberReminderCompleteRegistrationMail($booking->name, route('booking.step-3', $booking)));
            }
        }
    }
}
