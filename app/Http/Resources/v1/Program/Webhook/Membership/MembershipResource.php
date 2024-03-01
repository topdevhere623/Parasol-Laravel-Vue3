<?php

namespace App\Http\Resources\v1\Program\Webhook\Membership;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Member */
class MembershipResource extends JsonResource
{
    use DynamicImageResourceTrait;

    public static $wrap = null;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'member' => MemberResource::make($this->resource),
            'partner' => $this->when(!!$this->partner, fn () => MemberResource::make($this->partner)),
            'juniors' => $this->when(
                !!$this->juniors->isNotEmpty(),
                fn () => MemberResource::collection($this->juniors)
            ),
            'kids' => $this->when($this->kids->isNotEmpty(), fn () => KidResource::collection($this->kids)),
        ];
    }
}
