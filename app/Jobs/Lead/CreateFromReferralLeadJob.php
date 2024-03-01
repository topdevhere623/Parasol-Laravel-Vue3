<?php

namespace App\Jobs\Lead;

use App\Actions\Lead\GetOrCreateLeadAction;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class CreateFromReferralLeadJob extends BaseLeadJobClass implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Referral $referral;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Referral $referral)
    {
        $this->referral = $referral->withoutRelations();
    }

    public function handle(GetOrCreateLeadAction $leadGetOrCreateAction)
    {
        $member = $this->referral->member;
        $tags = ['Referral', 'b2c'];
        $tagIds = LeadTag::getOrCreate($tags)->pluck('id');

        $lead = $leadGetOrCreateAction->handle(
            $this->referral->email,
            $this->referral->name,
            '',
            $this->referral->mobile,
            $member->lead?->backoffice_user_id ?? Lead::DEFAULT_OWNER
        );

        $lead->leadTags()->syncWithoutDetaching($tagIds);

        $lead->status = Lead::STATUSES['todo'];
        $lead->save();

        $comment['referred_by'] = "{$member->full_name} ({$member->member_id})";
        $comment['code'] = $member->coupon?->code;
        $comment = 'Referral created'.PHP_EOL.Arr::humanizeKeyValue($comment);

        /** @var CrmComment $leadComment */
        $leadComment = $lead->crmComments()->create([
            'content' => $comment,
        ]);

        $this->pushNocrmData($lead, $leadComment);
    }
}
