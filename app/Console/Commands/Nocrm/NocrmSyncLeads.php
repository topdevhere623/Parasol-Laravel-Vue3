<?php

namespace App\Console\Commands\Nocrm;

use App\Jobs\Nocrm\AnyUpdateWebhookNocrmJob;
use App\Services\NocrmService;
use Illuminate\Console\Command;
use Illuminate\Queue\InvalidPayloadException;
use Str;

class NocrmSyncLeads extends Command
{
    // max 100
    protected const PAGE_SIZE = 100;

    protected $signature = 'nocrm:sync-leads';

    protected $description = 'Sync nocrm leads';

    public function handle(NocrmService $nocrmService)
    {
        $stopFetch = 0;
        $page = 0;
        $bar = $this->output->createProgressBar();
        $this->line('Fetch Nocrm leads:');
        $bar->start();
        while ($stopFetch === 0) {
            $page++;
            $leads = $nocrmService->getLeads([
                'offset' => $page * static::PAGE_SIZE,
                'limit' => static::PAGE_SIZE,
                'direction' => 'asc',
            ]);

            foreach ($leads as $lead) {
                $lead['extended_info']['fields_by_name'] = $this->leadFromDescription($lead['description']);
                try {
                    AnyUpdateWebhookNocrmJob::dispatch($lead)->onQueue('low');
                } catch (InvalidPayloadException $e) {
                    report($e);
                }

                $bar->advance();
            }

            $stopFetch = count($leads) - static::PAGE_SIZE;
        }

        $bar->finish();
        $this->newLine();

        return Command::SUCCESS;
    }

    private function leadFromDescription(string $description): array
    {
        return collect(explode(PHP_EOL, $description))
            ->mapWithKeys(function ($item) {
                $item = explode(':', $item);

                return [Str::of($item[0] ?? '')->snake()->trim()->toString() => trim($item[1] ?? '', " \n ")];
            })->toArray();
    }
}
