<?php

namespace App\Console\Commands\Offers;

use App\Models\Offer;
use Illuminate\Console\Command;

use Illuminate\Database\Eloquent\Collection;

class OffersUpdateExpiredStatusCommand extends Command
{
    protected $signature = 'offers:update-expired-status';

    protected $description = 'Change offer status to Expired';

    public function handle()
    {
        $preQuery = Offer::expiredActive()
            ->orderBy('id', 'desc');

        $count = $preQuery->count();

        if ($count) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();
            $preQuery->chunkById(10, function (Collection $items) use ($bar) {
                $items->each(function (Offer $item) use ($bar) {
                    $item->status = Offer::STATUSES['expired'];
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
