<?php

namespace App\Console\Commands\Members;

use App\Actions\Booking\BookingApplyMembershipRenewalAction;
use App\Models\Member\Member;
use Illuminate\Console\Command;

use function today;

class MembersUpdateExpiredStatusCommand extends Command
{
    protected $signature = 'members:update-expired-status';

    protected $description = 'Change Membership Status To Expired Command';

    public function handle(BookingApplyMembershipRenewalAction $bookingApplyMembershipRenewalAction)
    {
        $preQuery = Member::active()
            ->with('awaitingDueDateMembershipRenewal.booking', 'program')
            ->where('end_date', '<', today());

        $count = $preQuery->count();

        if ($count) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();
            $preQuery->chunkById(1, function ($members) use ($bar, $bookingApplyMembershipRenewalAction) {
                foreach ($members as $member) {
                    if ($member->awaitingDueDateMembershipRenewal) {
                        $bookingApplyMembershipRenewalAction->handle($member->awaitingDueDateMembershipRenewal);
                    } else {
                        $member->membership_status = Member::MEMBERSHIP_STATUSES['expired'];
                        $member->save();
                    }
                }
                $bar->advance();
            });
            echo "\n";
        } else {
            $this->info('Nothing to do');
        }

        return self::SUCCESS;
    }
}
