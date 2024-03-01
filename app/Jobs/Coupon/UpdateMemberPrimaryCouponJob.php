<?php

namespace App\Jobs\Coupon;

use App\Actions\Member\UpdateOrCreateMemberCouponAction;
use App\Models\Member\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateMemberPrimaryCouponJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $memberId;

    public function __construct(Member|int $member)
    {
        $this->memberId = is_object($member) ? $member->id : $member;
    }

    public function handle()
    {
        /** @var Member $member */
        $member = Member::with('coupon', 'program')
            ->withTrashed()
            ->find($this->memberId);
        (new UpdateOrCreateMemberCouponAction())->handle($member, true);
    }
}
