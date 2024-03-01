<?php

namespace App\Jobs\Lead;

use App\Actions\Lead\GetOrCreateLeadAction;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;

class CreateFromWebhookRequestLeadJob extends BaseLeadJobClass implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(protected array $data, protected string $source = 'webhook')
    {
    }

    public function handle(GetOrCreateLeadAction $leadGetOrCreateAction)
    {
        $requestData = &$this->data;
        $tagIds = LeadTag::getOrCreate([$requestData['tags']])->pluck('id');
        $lead = $leadGetOrCreateAction->handle(
            $requestData['email'],
            $requestData['first_name'],
            $data['last_name'] ?? '',
            $requestData['phone'],
            Lead::randomOwnerId($tagIds->first())
        );

        $lead->leadTags()->syncWithoutDetaching($tagIds);

        /** @var CrmComment $leadComment */
        $leadComment = $lead->crmComments()->create([
            'content' => 'Triggered by '.$this->source.PHP_EOL.Arr::humanizeKeyValue(
                ['tag' => $requestData['tags']['0']]
            ),
        ]);

        $this->pushNocrmData($lead, $leadComment);
    }
}
