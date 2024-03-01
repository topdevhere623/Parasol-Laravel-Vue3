<?php

namespace App\Observers;

use App\Actions\Member\UpdateOrCreateMemberCouponAction;
use App\Jobs\Gems\GemsUpdateMemberStatus;
use App\Jobs\Member\SendWelcomeEmailJob;
use App\Jobs\Passkit\PasskitUpdateMember;
use App\Jobs\ProgramApi\ProgramApiSendMemberJob;
use App\Models\GemsApi;
use App\Models\Member\Member;
use App\Models\Plan;
use App\Models\Program;
use App\Models\ProgramApiRequest;
use App\Models\Reports\ReportHSBCMonthlyActiveMember;
use Illuminate\Support\Str;

class MemberObserver
{
    public function creating(Member $member)
    {
        $member->uuid = (string)Str::orderedUuid();
    }

    public function created(Member $member)
    {
        if (!$member->old_id) {
            $member->old_id = $member->id;
        }
        $member->save();
    }

    public function saving(Member $member): void
    {
        if ($member->isDirty('plan_id') && $member->plan && $plan = $member->plan) {
            if ($package = $plan->package) {
                $member->package_id = $package->id;
                $member->program_id = $package->program_id;
            }
        }

        $member->first_name = Str::title($member->first_name);
        $member->last_name = Str::title($member->last_name);
        if ($member->isDirty(['email', 'recovery_email', 'main_email']) || !$member->login_email) {
            $member->login_email = $member->isAvailableForLogin() ? $member->getLoginEmail() : null;
        }
    }

    public function saved(Member $member): void
    {
        if ($member->isDirty(
            [
                'member_id',
                'end_date',
                'first_name',
                'last_name',
                'avatar',
                'membership_status',
                'membership_type_id',
            ]
        ) && $member->isAvailableForLogin()) {
            $passkit = null;
            if ($member->hasPasskitAccess()) {
                $passkit = PasskitUpdateMember::dispatch(
                    $member,
                    $member->isDirty('avatar') || !$member->passKit
                )->onQueue('high');
            }

            if ($member->wasRecentlyCreated
                && $member->login_email
                && $member->booking
                && !$member->program->isProgramSource('gems')) {
                if ($member->program->isProgramSource('hsbc')
                    || $member->program->id == Program::ENTERTAINER_SOLEIL_ID) {
                    report_if(
                        !$passkit,
                        new \Exception('Unable to send welcome email for HSBC member: '.$member->member_id)
                    );
                    $passkit?->chain([new SendWelcomeEmailJob($member)]);
                } else {
                    SendWelcomeEmailJob::dispatch($member);
                }
            }
        }
        if ($member->isDirty('membership_status', 'end_date')
            && $member->program->isProgramSource('gems')
            && $member->gemsApi
            && in_array($member->membership_status, GemsApi::UPDATABLE_STATUSES)
        ) {
            GemsUpdateMemberStatus::dispatch($member);
        }

        if ($member->member_type === Member::MEMBER_TYPES['member']
            && $member->wasChanged('membership_status', 'end_date')) {
            (new UpdateOrCreateMemberCouponAction())->handle($member, true);
        }

        if (!$member->member_id && $member->program) {
            $member->member_id = $member::generateMemberId($member);
            $member->save();
        }

        if ($member->isDirty('membership_status')
            && $member->member_type == Member::MEMBER_TYPES['member']
            && $member->isActive()
            && in_array($member->plan_id, [Plan::HSBC_SINGLE_FREE, Plan::HSBC_SINGLE_FAMILY_FREE])
        ) {
            ReportHSBCMonthlyActiveMember::firstOrCreate(
                ['member_id' => $member->id, 'month_year' => now()->format('mY')]
            );
        }

        if (
            $member->isDirty('membership_status')
            && $member->isExpired()
            && $member->memberPaymentSchedule
        ) {
            $member->memberPaymentSchedule->markAsCompleted()
                ->save();
        }

        if ($member->isDirty(
            'member_id',
            'member_type',
            'membership_status',
            'first_name',
            'last_name',
            'email',
            'recovery_email',
            'phone',
            'start_date',
            'expiry_date',
            'photo',
        )
            && !$member->wasRecentlyCreated
            && in_array($member->membership_status, ProgramApiRequest::UPDATABLE_STATUSES)
            && $member->programApiRequest
        ) {
            ProgramApiSendMemberJob::dispatch($member)->delay(now()->addSeconds(3));
        }
    }

    public function deleted(Member $member): void
    {
        $member->load('passKit');

        if ($member->passKit) {
            $member->passKit->delete();
        }

        if ($member->member_type == Member::MEMBER_TYPES['member']) {
            (new UpdateOrCreateMemberCouponAction())->handle($member, true);
        }
    }

}
