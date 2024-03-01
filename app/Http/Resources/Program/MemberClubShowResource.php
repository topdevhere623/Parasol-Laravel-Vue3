<?php

namespace App\Http\Resources\Program;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Club\Club */
class MemberClubShowResource extends JsonResource
{
    /**
     * @param  Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->old_id ?? $this->id,
            'title' => $this->title,
            'traffic' => $this->traffic,
            'photo' => $this->home_photo,
            'path' => \URL::uploads(static::getFilePath('home_photo')),
        ];
    }
}
