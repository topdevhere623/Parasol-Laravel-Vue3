<?php

namespace App\Jobs\Nocrm;

use App\Models\Lead\CrmComment;
use App\Services\NocrmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCommentLeadNocrmJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 2;

    public $maxExceptions = 3;

    public $uniqueFor = 10;

    protected CrmComment $crmComment;

    public function __construct(CrmComment $crmComment)
    {
        $this->onQueue('high')->delay(now()->addSeconds(10));
        $this->crmComment = $crmComment->withoutRelations();
    }

    public function uniqueId()
    {
        return self::class.$this->crmComment->id;
    }

    public function handle(NocrmService $nocrmService)
    {
        if (!$nocrmService->isAvailable()) {
            return;
        }
        $this->crmComment->refresh();
        $nocrmService->pushComment($this->crmComment);
    }
}
