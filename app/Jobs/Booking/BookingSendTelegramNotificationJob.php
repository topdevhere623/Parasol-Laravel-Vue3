<?php

namespace App\Jobs\Booking;

use App\Enum\Booking\StepEnum;
use App\Models\Booking;
use App\Notifications\Booking\StepOneBookingNotification;
use App\Notifications\Booking\StepThreeBookingNotification;
use App\Notifications\Booking\StepTwoBookingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class BookingSendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $bookingId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int|Booking $booking)
    {
        $this->bookingId = is_object($booking) ? $booking->id : $booking;
    }

    public function handle()
    {
        $booking = Booking::find($this->bookingId);

        $stepNotifications = [
            StepEnum::Payment->getPreviousStep()->value => StepOneBookingNotification::class,
            StepEnum::MembershipDetails->getPreviousStep()->value => StepTwoBookingNotification::class,
            StepEnum::Completed->getPreviousStep()->value => StepThreeBookingNotification::class,
        ];

        if ($booking->step->getPreviousStep() === null) {
            $this->fail(new \Exception('Invalid step'));
        }

        Notification::send(
            get_telegram_notifiable(),
            app()->make($stepNotifications[$booking->step->getPreviousStep()->value], ['bookingId' => $booking->id])
        );
    }
}
