<?php

namespace App\Repositories;

use App\Models\Member\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberRepository extends Repository
{
    public function getMemberShotData(Request $request): array
    {
        if ($request->has('parentValue')) {
            $members = Member::where('id', $request->input('parentValue'))
                ->orderBy('id', 'desc')
                ->get();
            return $members->pluck('member_short_data', 'id')
                ->toArray();
        }
        if ($request->has('query')) {
            $members = Member::where('member_type', '!=', 'junior')
                ->where(function ($query) use ($request) {
                    return $query->orWhere('member_id', 'like', '%'.$request->input('query').'%')
                        ->orWhere('first_name', '%'.$request->input('query').'%')
                        ->orWhere('last_name', '%'.$request->input('query').'%');
                })
                ->limit(config('advplus.default_autocomplete_response_limit'))
                ->orderBy('id', 'desc')
                ->get();
            return $members->pluck('member_short_data', 'id')
                ->toArray();
        }
        return [];
    }

    public function getMemberFullData(Request $request): array
    {
        if ($request->has('parentValue')) {
            $members = Member::where('id', $request->input('parentValue'))
                ->orderBy('id', 'desc')
                ->get();
            return $members->pluck('member_full_data', 'id')
                ->toArray();
        }
        if ($request->has('query')) {
            $members = Member::where('member_type', '!=', 'junior')
                ->where(function ($query) use ($request) {
                    return $query->orWhere('member_id', 'like', '%'.$request->input('query').'%')
                        ->orWhere('first_name', '%'.$request->input('query').'%')
                        ->orWhere('last_name', '%'.$request->input('query').'%');
                })
                ->limit(config('advplus.default_autocomplete_response_limit'))
                ->orderBy('id', 'desc')
                ->get();
            return $members->pluck('member_full_data', 'id')
                ->toArray();
        }
        return [];
    }

    public function checkExistReferralByEmail(string $email): bool
    {
        /** @var Member $member */
        $member = Auth::user();
        $referrals = $this->getReferralByEmail($member, $email);
        return $referrals && $referrals->count();
    }

    protected function getReferralByEmail($member, $email)
    {
        return $member->referrals()->where(function ($query) use ($email) {
            return $query->where('email', $email)
                ->whereNull('used_member_id');
        })->get();
    }
}
