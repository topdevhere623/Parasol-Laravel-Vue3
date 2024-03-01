<?php

namespace App\Console\Commands\Nocrm;

use App\Models\Lead\CrmPipeline;
use App\Models\Lead\CrmStep;
use App\Services\NocrmService;
use Illuminate\Console\Command;

class NocrmSyncSteps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nocrm:import-steps';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(NocrmService $nocrmService)
    {
        $pipelines = [];
        foreach ($nocrmService->get('pipelines') as $item) {
            $pipelines[$item['id']] = CrmPipeline::firstOrCreate([
                'nocrm_id' => $item['id'],
            ]);

            $pipelines[$item['id']]->update([
                'name' => $item['name'],
            ]);
        }

        foreach ($nocrmService->get('steps') as $item) {
            CrmStep::firstOrCreate([
                'nocrm_id' => $item['id'],
            ])->updateQuietly([
                'name' => $item['name'],
                'position' => $item['position'],
                'crm_pipeline_id' => $pipelines[$item['pipeline_id']]->id,
            ]);
        }

        return Command::SUCCESS;
    }
}
