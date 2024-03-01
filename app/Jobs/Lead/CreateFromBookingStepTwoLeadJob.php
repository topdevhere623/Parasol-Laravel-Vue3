<?php

namespace App\Jobs\Lead;

use App\Actions\Lead\GetOrCreateLeadAction;
use App\Enum\Booking\StepEnum;
use App\Models\Booking;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class CreateFromBookingStepTwoLeadJob extends BaseLeadJobClass implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Booking $booking;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->withoutRelations();
    }

    public function handle(GetOrCreateLeadAction $leadGetOrCreateAction)
    {
        $booking = $this->booking->refresh();

        $hasCompletedBooking = Booking::where('created_at', '>', now()->subDays(7))
            ->where('email', $booking->email)
            ->where('step', StepEnum::Completed)
            ->count() != 0;

        if ($booking->step != StepEnum::Payment || $hasCompletedBooking) {
            return;
        }
        $tags = ['Step 2 Unfinished', 'b2c', $booking->plan->duration.$booking->plan->duration_type[0]];

        if ($booking->isProgramSource('hsbc')) {
            $tags[] = 'HSBC';
        }
        $tagIds = LeadTag::getOrCreate($tags)->pluck('id');
        $ownerId = $booking->isProgramSource('hsbc') ? Lead::OWNERS['Ritesh'] : Lead::randomOwnerId($tagIds->first());

        $lead = $leadGetOrCreateAction->handle(
            $booking->email,
            $booking->name,
            '',
            $booking->phone,
            $ownerId
        );
        $booking->lead()->associate($lead);
        $booking->save();

        $lead->amount = $booking->total_price;
        $lead->status = Lead::STATUSES['todo'];
        $lead->save();

        $lead->leadTags()->syncWithoutDetaching($tagIds);

        $comment = [
            'type' => ucfirst($booking->type),
            'name' => $booking->name,
            'mobile' => $booking->phone,
            'email' => $booking->email,
            'program' => $booking->plan->package->program->name,
            'package' => $booking->plan->package->title,
            'plan' => $booking->plan->title,
            ...array_filter(
                $booking->only([
                    'plan_amount',
                    'extra_child_amount',
                    'extra_junior_amount',
                    'subtotal_amount',
                    'vat_amount',
                    'coupon_amount',
                    'gift_card_discount_amount',
                    'total_price',
                ]),
                fn ($value) => $value > 0
            ),
        ];

        if ($booking->payment) {
            $comment['last_payment_try'] = $booking->payment->paymentMethod->title;
        }

        $clubs = $booking->clubs->count();

        if ($clubs <= 5) {
            $clubs = PHP_EOL.$booking->clubs()->get(['title'])->implode('title', PHP_EOL);
        }

        $comment['clubs'] = $clubs;

        $comment = 'Request: Booking'.PHP_EOL.Arr::humanizeKeyValue($comment);

        /** @var CrmComment $leadComment */
        $leadComment = $lead->crmComments()->create([
            'content' => $comment,
        ]);

        $this->pushNocrmData($lead, $leadComment);
    }
}
