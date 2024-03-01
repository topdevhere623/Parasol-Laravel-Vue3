<?php

namespace App\Console\Commands\Temp;

use App\Models\Lead\Lead;
use App\Services\NocrmService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CleanLeads extends Command
{
    protected $signature = 'leads:clean';

    public function handle(NocrmService $nocrmService)
    {
        $bar = $this->output->createProgressBar(Lead::count());

        Lead::orderBy('status')
            ->chunk(500, function (Collection $collection) use ($bar, $nocrmService) {
                $collection->each(function (Lead $lead) use ($bar, $nocrmService) {
                    try {
                        $nocrmService->getLead($lead->nocrm_id);
                    } catch (\Exception $e) {
                        $lead->delete();
                    }

                    $bar->advance();
                });
            });

        $bar->finish();
        $this->newLine();

        return Command::SUCCESS;
    }
}
