<?php

namespace App\Jobs\Nocrm;

use App\Actions\Lead\GetCommentsFromNocrmLeadAction;
use App\Models\Lead\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class CommentedWebhookNocrmJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 2;

    public $maxExceptions = 3;

    public function __construct(protected array $data)
    {
    }

    public function middleware()
    {
        return [(new WithoutOverlapping($this->data['id']))->releaseAfter(5)];
    }

    public function handle(GetCommentsFromNocrmLeadAction $action)
    {
        $lead = Lead::where('nocrm_id', $this->data['id'])
            ->first();

        if (!$lead) {
            AnyUpdateWebhookNocrmJob::dispatch($this->data)->chain(
                [new CommentedWebhookNocrmJob($this->data)]
            );

            return;
        }

        $action->handle($lead);
    }

}
