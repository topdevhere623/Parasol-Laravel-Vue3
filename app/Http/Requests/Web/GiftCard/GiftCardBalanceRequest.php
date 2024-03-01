<?php

namespace App\Http\Requests\Web\GiftCard;

use Illuminate\Foundation\Http\FormRequest;

class GiftCardBalanceRequest extends FormRequest
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
        ];
    }
}
