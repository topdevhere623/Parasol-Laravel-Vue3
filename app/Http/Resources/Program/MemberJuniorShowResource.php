<?php

namespace App\Http\Resources\Program;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Junior */
class MemberJuniorShowResource extends JsonResource
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
            'birthday' => $this->dob->format('Y-m-d'),
            'path' => \URL::uploads(static::getFilePath('avatar')),
        ];
    }
}
