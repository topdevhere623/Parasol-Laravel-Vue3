<?php

namespace App\Console\Commands\Temp;

use App\Actions\Member\UpdateOrCreateMemberCouponAction;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FixRenewedMembersCoupons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:members-coupons';

    protected $i = 0;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MemberPrimary::with('coupon', 'program')->active()->chunkById(300, function (Collection $collection) {
            $collection->each(function (Member $member) {
                if (!$member->coupon) {
                    $this->info($member->id.' : '.$member->member_id);
                    $this->i++;
                    (new UpdateOrCreateMemberCouponAction())->handle($member, true);
                }
            });
        });
        $this->info($this->i);
        return Command::SUCCESS;
    }
}
