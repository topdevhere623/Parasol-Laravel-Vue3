<?php

namespace App\Http\Requests\Web\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class CouponCheckRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|string',
            'email' => 'email',
            'plan_id' => 'required|integer',
        ];
    }
}
