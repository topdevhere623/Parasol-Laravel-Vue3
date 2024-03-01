<?php

namespace App\Console\Commands\Lead;

use App\Models\Lead\Lead;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class LeadsUpdateStandbyStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:update-standby-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change lead status to Todo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $preQuery = Lead::standbyExpired();

        $count = $preQuery->count();

        if ($count) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();
            $preQuery->chunkById(10, function (Collection $items) use ($bar) {
                $items->each(function (Lead $item) use ($bar) {
                    $item->status = Lead::STATUSES['todo'];
                    $item->reminder_at = null;
                    $item->reminder_duration = null;
                    $item->save();
                    $bar->advance();
                });
            });
            echo "\n";
        } else {
            $this->info('Nothing to do');
        }

        return self::SUCCESS;
    }
}
