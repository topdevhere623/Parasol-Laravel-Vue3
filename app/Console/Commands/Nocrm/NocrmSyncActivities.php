<?php

namespace App\Console\Commands\Nocrm;

use App\Models\Lead\CrmActivity;
use App\Services\NocrmService;
use Illuminate\Console\Command;

class NocrmSyncActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nocrm:import-activities';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(NocrmService $nocrmService)
    {
        $data = collect($nocrmService->get('activities'))
            ->sortBy('id')
            ->values();

        $activities = [];
        foreach ($data as $item) {
            $activities[$item['id']] = CrmActivity::firstOrCreate([
                'nocrm_id' => $item['id'],
            ]);

            $activities[$item['id']]->updateQuietly([
                'parent_id' => isset($activities[$item['parent_id']]) ? $activities[$item['parent_id']]->id : null,
                'name' => $item['name'],
                'icon' => $item['icon'],
                'color' => $item['color'],
                'type' => $item['kind'],
                'is_disabled' => $item['is_disabled'],
                'position' => $item['position'],
                'nocrm_parent_id' => $item['parent_id'],
            ]);
        }

        return Command::SUCCESS;
    }
}
