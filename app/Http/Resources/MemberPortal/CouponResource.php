<?php

namespace App\Http\Resources\MemberPortal;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Coupon */
class CouponResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'amount' => $this->amount.($this->amount_type == Coupon::AMOUNT_TYPES['percentage'] ? '%' : ' AED'),
            'status' => $this->status,
        ];
    }
}
