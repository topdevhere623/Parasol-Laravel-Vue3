<?php

namespace App\Http\Resources\Program;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\MemberPrimary */
class MemberPrimaryIndexResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->old_id ?? $this->id,
            'member_id' => $this->member_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'business_email' => $this->recovery_email,
            'main_email' => $this->login_email,
            'start_date' => optional(Carbon::parse($this->start_date))->format('Y-m-d'),
            'end_date' => optional(Carbon::parse($this->end_date))->format('Y-m-d'),
            'avatar' => $this->avatar,
            'membership_status' => ucfirst($this->membership_status),
            'membership_type' => $this->membershipType->title ?? null,
            'membership_source' => $this->membershipSource->title ?? null,
            'corporate_name' => $this->corporate_name,
            'referral_code' => $this->referral_code,
            'offer_code' => $this->offer_code,
            'phone' => $this->phone,
            'role' => 'user',
            'status' => 1,
            'is_deleted' => 0,
            'source' => $this->source,
            'airtable_id' => $this->airtable_id,
            'plan_id' => $this->plan_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
