<?php

namespace App\Http\Resources\CRM\Checkin;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Kid */
class CheckinKidResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'age' => $this->age,
            'checked_in' => $this->activeCheckins->isNotEmpty(),
        ];
    }
}
