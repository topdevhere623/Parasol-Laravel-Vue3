<?php

namespace App\Http\Requests\Web\Booking;

use Illuminate\Foundation\Http\FormRequest;

class BookingStepOneRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'package_id' => 'required|string',
            'plan_id' => 'required|integer',
            'clubs' => 'required|string',
            'number_of_children' => 'nullable|integer',
            'allowed_juniors' => 'nullable|integer',
            'membership_source_id' => 'nullable|integer',
            'membership_source_other' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'gift_card_amount' => 'nullable|numeric',
            'gift_card_number' => 'nullable|string',
            'area_id' => 'required|integer',
        ];
    }
}
