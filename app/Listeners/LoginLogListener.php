<?php

namespace App\Listeners;

use App\Models\BackofficeUser;
use App\Models\Member\Junior;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\Member\Partner;
use App\Models\PassportLoginHistory;
use App\Models\Program;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Events\AccessTokenCreated;

class LoginLogListener implements ShouldQueue
{
    public function handle(AccessTokenCreated $event)
    {
        /** do not remove */
    }

    /**
     * @return bool
     */
    public function shouldQueue(AccessTokenCreated $event): bool
    {
        $passportLoginHistory = new PassportLoginHistory();
        $passportLoginHistory->user_id = $event->userId;
        $passportLoginHistory->user_type = $this->getUserType($event);
        $passportLoginHistory->token_id = $event->tokenId;
        $passportLoginHistory->ip_address = request()->header('x-forwarded-for');
        $passportLoginHistory->user_agent = request()->header('user-agent');
        $passportLoginHistory->created_at = now();

        return $passportLoginHistory->save();
    }

    protected function getUserType(AccessTokenCreated $event): string
    {
        $userType = $event->clientId;

        $provider = DB::table('oauth_clients')->where('id', $event->clientId)->first()->provider;

        if ($provider === 'members' && $member = Member::find($event->userId)) {
            if ($member->member_type === Member::MEMBER_TYPES['member']) {
                $userType = MemberPrimary::class;
            }
            if ($member->member_type === Member::MEMBER_TYPES['partner']) {
                $userType = Partner::class;
            }
            if ($member->member_type === Member::MEMBER_TYPES['junior']) {
                $userType = Junior::class;
            }
        }
        if ($provider === 'backoffice_users') {
            $userType = BackofficeUser::class;
        }
        if ($provider === 'programs') {
            $userType = Program::class;
        }
        return $userType;
    }
}
