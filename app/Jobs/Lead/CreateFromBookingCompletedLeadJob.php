<?php

namespace App\Jobs\Lead;

use App\Actions\Lead\GetOrCreateLeadAction;
use App\Enum\Booking\StepEnum;
use App\Models\BackofficeUser;
use App\Models\Booking;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use App\Models\Member\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class CreateFromBookingCompletedLeadJob extends BaseLeadJobClass implements ShouldQueue
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
        $booking = $this->booking;
        $member = $booking->member;
        if ($booking->step != StepEnum::Completed) {
            return;
        }

        $tags = ['b2c', 'Mapped to Booking', $booking->plan->duration.$booking->plan->duration_type[0]];

        if ($booking->isProgramSource('hsbc')) {
            $ownerId = Lead::OWNERS['Ritesh'];
            $tags[] = 'HSBC';
        } else {
            $ownerId = Lead::DEFAULT_OWNER;
        }

        $emails = [];

        if ($booking->email) {
            $emails[] = $booking->email;
        }

        if ($member->login_email) {
            $emails[] = $member->login_email;
        }

        $lead = $leadGetOrCreateAction->handle(
            $emails,
            $member->first_name,
            $member->last_name,
            $member->phone,
            $ownerId
        );
        $booking->lead()->associate($lead);
        $booking->save();

        if (!$member->bdm_backoffice_user_id) {
            $member->updateQuietly(['bdm_backoffice_user_id' => $lead->backoffice_user_id]);
        }

        if (!$member->lead_id) {
            $member->updateQuietly(['lead_id' => $lead->id]);
        }

        $lead->leadTags()->syncWithoutDetaching(
            LeadTag::getOrCreate($tags)->pluck('id')
        );

        $comment = [
            'type' => ucfirst($booking->type),
            'reference' => $booking->reference_id,
            'program' => $booking->plan->package->program->name,
            'plan' => $booking->plan->title,
        ];

        if ($booking->coupon) {
            $comment['coupon'] = $booking->coupon->code;

            $couponOwner = $booking->coupon->couponable;
            if ($couponOwner instanceof Member && $couponOwner->bdmBackofficeUser) {
                $lead->backofficeUser()->associate($couponOwner->bdmBackofficeUser);
            } elseif ($couponOwner instanceof BackofficeUser) {
                $lead->backofficeUser()->associate($couponOwner);
            }
        }

        $lead->amount = $booking->total_price;
        $lead->status = Lead::STATUSES['won'];
        $lead->setStep('Closing');
        $lead->save();

        $comment = 'Booking Completed'.PHP_EOL.Arr::humanizeKeyValue($comment);

        /** @var CrmComment $leadComment */
        $leadComment = $lead->crmComments()->create([
            'content' => $comment,
        ]);

        $this->pushNocrmData($lead, $leadComment);
    }

}
