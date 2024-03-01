<?php

namespace App\Console\Commands\Partner;

use App\Models\Partner\PartnerTranche;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class PartnerTrancheTriggerObserverCommand extends Command
{
    protected $signature = 'partner:tranche-observer-trigger';

    protected $description = 'Trigger Partner Tranche observer';

    public function handle()
    {
        $preQuery = PartnerTranche::expiredActive()
            ->orderBy('id', 'desc');

        $count = $preQuery->count();

        if ($count) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();
            $preQuery->chunkById(100, function (Collection $items) use ($bar) {
                $items->each(function (PartnerTranche $item) use ($bar) {
                    $item->save();
                    $bar->advance();
                });
            });
            echo PHP_EOL;
        } else {
            $this->info('Nothing to do');
        }

        return self::SUCCESS;
    }
}
