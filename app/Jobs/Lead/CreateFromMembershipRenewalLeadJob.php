<?php

namespace App\Jobs\Lead;

use App\Actions\Lead\GetOrCreateFromMemberLeadAction;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use App\Models\Member\MembershipRenewal;
use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class CreateFromMembershipRenewalLeadJob extends BaseLeadJobClass implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected MembershipRenewal $membershipRenewal;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MembershipRenewal $membershipRenewal)
    {
        $this->membershipRenewal = $membershipRenewal->withoutRelations();
    }

    public function handle(GetOrCreateFromMemberLeadAction $getOrCreateFromMemberLeadAction)
    {
        $member = $this->membershipRenewal->member;
        $lead = $getOrCreateFromMemberLeadAction->handle($member);

        $lead->status = Lead::STATUSES['todo'];
        $lead->setStep('Incoming');
        $lead->save();

        $tags = ['renewal', 'b2c', $member->plan->duration.strtoupper($member->plan->duration_type[0])];

        if ($member->program_id == Program::ENTERTAINER_HSBC) {
            $tags[] = 'HSBC';
        }

        $lead->leadTags()->syncWithoutDetaching(
            LeadTag::getOrCreate($tags)->pluck('id')
        );

        $comment = [
            'name' => $member->full_name,
            'mobile' => $member->phone,
            'email' => $member->login_email,
            'membership_start' => app_date_format($member->start_date),
            'membership_expiry' => app_date_format($member->end_date),
            'program' => $member->program->name,
            'package' => $member->package->title,
            'plan' => $member->plan->title,
            'renewal_url' => $this->membershipRenewal->renewal_url,
            'number_of_club_visits' => $member->checkins()->count(),
        ];

        $comment = 'Membership Renewal'.PHP_EOL.Arr::humanizeKeyValue($comment);

        /** @var CrmComment $leadComment */
        $leadComment = $lead->crmComments()->create([
            'content' => $comment,
        ]);

        $this->pushNocrmData($lead, $leadComment);
    }
}
