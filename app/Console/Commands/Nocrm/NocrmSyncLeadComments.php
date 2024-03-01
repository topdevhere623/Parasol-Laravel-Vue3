<?php

namespace App\Console\Commands\Nocrm;

use App\Actions\Lead\GetCommentsFromNocrmLeadAction;
use App\Models\Lead\Lead;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;

class NocrmSyncLeadComments extends Command
{
    protected $signature = 'nocrm:import-lead-comments
    {--count=20 : Lead count to process}
    {--from_id= : The lead id to start from}
    ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GetCommentsFromNocrmLeadAction $action): int
    {
        $bar = $this->output->createProgressBar($this->option('count'));
        $this->line('Fetch Nocrm comments by each lead:');

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
            ->chunkById(200, function ($leads) use ($bar, $action) {
                foreach ($leads as $lead) {
                    try {
                        $action->handle($lead);
                    } catch (RequestException $e) {
                        $this->info($e->getMessage());
                        continue;
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        return Command::SUCCESS;
    }
}
