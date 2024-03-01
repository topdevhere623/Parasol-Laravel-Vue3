<?php

namespace App\Http\Requests\Web\GiftCard;

use Illuminate\Foundation\Http\FormRequest;

class GiftCardGetDiscountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'card_type' => 'required|string',
            'card_number' => 'required|string',
            'amount' => 'required|numeric',
        ];
    }
}
