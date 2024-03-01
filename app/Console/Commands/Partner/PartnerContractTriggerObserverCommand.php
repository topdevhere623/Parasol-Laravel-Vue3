<?php

namespace App\Console\Commands\Partner;

use App\Models\Partner\PartnerContract;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class PartnerContractTriggerObserverCommand extends Command
{
    protected $signature = 'partner:contract-observer-trigger';

    protected $description = 'Trigger Partner Tranche observer';

    public function handle()
    {
        $preQuery = PartnerContract::expiredActive()
            ->orderBy('id', 'desc');

        $count = $preQuery->count();

        if ($count) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();
            $preQuery->chunkById(100, function (Collection $items) use ($bar) {
                $items->each(function (PartnerContract $item) use ($bar) {
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
