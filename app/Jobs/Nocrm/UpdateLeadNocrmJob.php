<?php

namespace App\Jobs\Nocrm;

use App\Models\Lead\Lead;
use App\Services\NocrmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLeadNocrmJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 2;

    public $maxExceptions = 3;

    public $uniqueFor = 10;

    protected Lead $lead;

    public function __construct(Lead $lead)
    {
        $this->onQueue('high')->delay(now()->addSeconds(5));
        $this->lead = $lead->withoutRelations();
    }

    public function uniqueId()
    {
        return self::class.$this->lead->id;
    }

    public function handle(NocrmService $nocrmService)
    {
        if (!$nocrmService->isAvailable()) {
            return;
        }

        $this->lead->refresh();
        $nocrmService->pushLead($this->lead);
    }
}
