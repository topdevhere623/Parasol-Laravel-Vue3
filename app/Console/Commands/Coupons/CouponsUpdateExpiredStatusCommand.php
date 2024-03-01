<?php

namespace App\Console\Commands\Coupons;

use App\Models\Coupon;
use Illuminate\Console\Command;

class CouponsUpdateExpiredStatusCommand extends Command
{
    protected $signature = 'coupons:update-expired-status';

    protected $description = 'Change coupon status to Expired';

    public function handle()
    {
        $preQuery = Coupon::active()
            ->orderBy('id', 'desc')
            ->expiredDate();

        $count = $preQuery->count();

        if ($count) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();
            $preQuery->chunkById(10, function ($coupons) use ($bar) {
                foreach ($coupons as $coupon) {
                    $coupon->status = Coupon::STATUSES['expired'];
                    $coupon->save();
                    $bar->advance();
                }
            });
            echo "\n";
        } else {
            $this->info('Nothing to do');
        }

        return self::SUCCESS;
    }
}
