<?php

namespace App\Console\Commands\MembershipProcess;

use App\Models\Member\MembershipProcess;
use Illuminate\Console\Command;

use Illuminate\Database\Eloquent\Collection;

class MembershipProcessExpiredStatusCommand extends Command
{
    protected $signature = 'membershipprocess:update-overdue-status';

    protected $description = 'Change membership process status to Expired';

    public function handle()
    {
        $preQuery = MembershipProcess::where('status', MembershipProcess::STATUSES['pending'])->where('action_due_date', '<', now())
            ->orderBy('id', 'desc');

        $count = $preQuery->count();
        if ($count) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();
            $preQuery->chunkById(10, function (Collection $items) use ($bar) {
                $items->each(function (MembershipProcess $item) use ($bar) {
                    $item->status = MembershipProcess::STATUSES['overdue'];
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
