<?php

namespace App\Jobs\Coupon;

use App\Actions\Member\UpdateOrCreateMemberCouponAction;
use App\Models\Member\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class UpdateProgramCouponsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected int $programId, protected bool $updatePlans = false)
    {
    }

    public function handle()
    {
        $updateOrCreateMemberCouponAction = new UpdateOrCreateMemberCouponAction();
        Member::where('program_id', $this->programId)
            ->select(['id', 'membership_status', 'end_date', 'referral_code', 'program_id'])
            ->with('coupon', 'program')
            ->chunkById(
                200,
                fn (Collection $members) => $members->each(
                    fn (Member $member) => $updateOrCreateMemberCouponAction->handle($member, $this->updatePlans)
                )
            );
    }
}
