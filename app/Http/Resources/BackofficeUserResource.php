<?php

namespace App\Http\Resources;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\BackofficeUser */
class BackofficeUserResource extends JsonResource
{
    use DynamicImageResourceTrait;

    public static $wrap = null;

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar' => file_url($this->resource, 'avatar', 'medium'),
            'full_name' => $this->full_name,
            'home_url' => 'checkins',
            'is_sales' => $this->hasRole('sales'),
        ];

        return array_merge($data, $this->imageArray());
    }
}
