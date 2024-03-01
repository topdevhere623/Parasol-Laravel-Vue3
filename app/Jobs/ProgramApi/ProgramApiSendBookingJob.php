<?php

namespace App\Jobs\ProgramApi;

use App\Actions\ProgramApi\ProgramSendApiWebhookAction;
use App\Http\Resources\v1\Program\Webhook\Booking\BookingResource;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProgramApiSendBookingJob implements ShouldQueue
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
    public function __construct($booking)
    {
        $this->bookingId = is_object($booking) ? $booking->id : $booking;
        $this->onQueue('high');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Booking $booking */
        $booking = Booking::with(
            'membershipRenewal',
            'member.partner.clubs',
            'member.membershipType',
            'member.kids',
            'member.juniors.clubs',
            'member.memberBillingDetail',
            'member.program',
            'member.programApiRequest',
            'member.clubs',
        )
            ->where('id', $this->bookingId)
            ->first();

        throw_unless($booking, new \Exception('Booking not found id:'.$this->bookingId));

        $programApiRequest = $booking->member?->programApiRequest;

        throw_unless($programApiRequest, new \Exception('Program API not found for booking id:'.$booking->id));

        $payload = BookingResource::make($booking)->resolve();

        (new ProgramSendApiWebhookAction())->handle($booking->member->program, $booking->type, $payload);

        $programApiRequest->booking_webhook_sent = true;
        $programApiRequest->save();
    }
}
