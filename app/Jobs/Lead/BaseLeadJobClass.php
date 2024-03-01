<?php

namespace App\Jobs\Lead;

use App\Jobs\Nocrm\UpdateCommentLeadNocrmJob;
use App\Jobs\Nocrm\UpdateLeadNocrmJob;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;

abstract class BaseLeadJobClass
{
    public function pushNocrmData(Lead $lead, ?CrmComment $comment = null): void
    {
        $pendingJob = UpdateLeadNocrmJob::dispatch($lead);

        if ($comment) {
            $pendingJob->chain([
                new UpdateCommentLeadNocrmJob($comment),
            ]);
        }
    }
}
