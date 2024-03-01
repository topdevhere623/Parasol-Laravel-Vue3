<?php

namespace App\Jobs\Lead;

use App\Actions\Lead\GetOrCreateLeadAction;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use App\Models\WebFormRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class CreateFromWebFormRequestLeadJob extends BaseLeadJobClass implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected WebFormRequest $webFormRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WebFormRequest $webFormRequest)
    {
        $this->webFormRequest = $webFormRequest->withoutRelations();
    }

    public function handle(GetOrCreateLeadAction $leadGetOrCreateAction)
    {
        $webFormRequest = $this->webFormRequest;

        $isB2C = $webFormRequest->type == 'Club Information';

        $tags = [
            'Website',
            $isB2C ? 'b2c' : 'b2b',
            $webFormRequest->type,
        ];
        if ($webFormRequest->is_entertainer) {
            $tags[] = 'soleil';
        }

        if (!empty($webFormRequest->data['utm_source'])) {
            $tags[] = $webFormRequest->data['utm_source'];
        }
        $tagIds = LeadTag::getOrCreate($tags)->pluck('id');

        $lead = $leadGetOrCreateAction->handle(
            $webFormRequest->email,
            $webFormRequest->name,
            '',
            $webFormRequest->phone,
            $isB2C ? Lead::randomOwnerId($tagIds->first()) : Lead::DEFAULT_OWNER
        );

        $lead->status = Lead::STATUSES['todo'];

        $webFormRequest->lead()->associate($lead);
        $webFormRequest->save();

        $lead->leadTags()->syncWithoutDetaching($tagIds);

        $comment['request'] = $webFormRequest->type;
        if (!empty($webFormRequest->data['memberships'])) {
            $comment['memberships'] = $webFormRequest->data['memberships'];
        }

        /** @var CrmComment $leadComment */
        $leadComment = $lead->crmComments()->create([
            'content' => Arr::humanizeKeyValue($comment),
        ]);

        $this->pushNocrmData($lead, $leadComment);
    }
}
