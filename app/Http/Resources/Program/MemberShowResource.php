<?php

namespace App\Http\Resources\Program;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\MemberPrimary */
class MemberShowResource extends JsonResource
{
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
            'phone' => $this->phone,
            'photo' => $this->avatar,
            'business_email' => $this->recovery_email,
            'start_date' => optional(Carbon::parse($this->start_date))->format('Y-m-d'),
            'path' => \URL::uploads(static::getFilePath('avatar')),
        ];
    }
}
