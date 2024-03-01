<?php

namespace App\Console\Commands\Members;

use App\Models\Member\Member;
use Illuminate\Console\Command;

class MemberActivateByStartDateCommand extends Command
{
    protected $signature = 'members:activate-by-start-date';

    protected $description = 'Change member status to active by start date';

    protected int $count = 0;

    public function handle()
    {
        $preQuery = Member::whereMembershipStatus(Member::MEMBERSHIP_STATUSES['processing'])
            ->whereStartDate(today())
            ->whereHas('booking.paymentMethod', function ($query) {
                return $query->where('code', '!=', 'bank_transfer')
                    ->where('code', '!=', 'cash');
            });

        $this->count = $preQuery->count();
        $this->info('Start - '.$this->description.' : '.$this->count);
        $bar = $this->output->createProgressBar($this->count);
        $bar->start();

        $preQuery->chunkById(10, function ($members) use ($bar) {
            foreach ($members as $member) {
                $member->membership_status = Member::MEMBERSHIP_STATUSES['active'];
                if ($member->save()) {
                    $bar->advance();
                }
            }
        });

        echo "\n";
        $this->table([
            'Available',
            'Done',
        ], [
            [
                $this->count,
                $bar->getProgress(),
            ],
        ]);
    }
}
