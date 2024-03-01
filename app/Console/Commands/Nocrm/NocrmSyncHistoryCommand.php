<?php

namespace App\Console\Commands\Nocrm;

use App\Models\BackofficeUser;
use App\Models\Lead\CrmHistory;
use App\Models\Lead\Lead;
use App\Services\NocrmService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;

class NocrmSyncHistoryCommand extends Command
{
    protected $signature = 'nocrm:import-history
    {--count=20 : Lead count to process}
    {--from_id= : The lead id to start from}
    ';

    public function handle(NocrmService $nocrmService)
    {
        $bar = $this->output->createProgressBar($this->option('count'));
        $this->line('Fetch Nocrm histories by each lead:');

        $backofficeUsers = BackofficeUser::whereNotNull('nocrm_id')
            ->pluck('id', 'nocrm_id')
            ->toArray();

        $query = Lead::query()
            ->take($this->option('count'));

        $maxIdQuery = Lead::query()
            ->orderBy('id')
            ->limit($this->option('count'))
            ->select('id');

        if ($this->option('from_id') !== null) {
            $query->where('id', '>=', $this->option('from_id'))
                ->orderBy('id');

            $maxIdQuery->where('id', '>=', $this->option('from_id'));
        }

        $maxId = $maxIdQuery->get()->max('id');

        $query->where('id', '<', $maxId)
            ->chunkById(200, function ($leads) use ($nocrmService, $bar, $backofficeUsers) {
                foreach ($leads as $lead) {
                    try {
                        $histories = collect($nocrmService->get("leads/{$lead->nocrm_id}/action_histories"));
                    } catch (RequestException) {
                        continue;
                    }

                    /** @var CrmHistory[] $items */
                    $items = [];

                    foreach ($histories as $historyItem) {
                        $items[$historyItem['id']] = CrmHistory::firstOrCreate([
                            'nocrm_id' => $historyItem['id'],
                            'nocrm_lead_id' => $lead->nocrm_id,
                            'nocrm_user_id' => $historyItem['user']['id'] ?? null,
                            'lead_id' => $lead->id,
                        ]);

                        $items[$historyItem['id']]->update([
                            'user_id' => $backofficeUsers[$historyItem['user']['id'] ?? null] ?? null,
                            'action_type' => $historyItem['action_type'],
                            'action_item' => $historyItem['action_item'],
                            'created_at' => $historyItem['created_at'],
                        ]);
                    }
                    $bar->advance();
                }
            });

        $bar->finish();

        return Command::SUCCESS;
    }
}
