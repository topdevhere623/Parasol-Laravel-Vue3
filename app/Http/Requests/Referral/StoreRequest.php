<?php

namespace App\Http\Requests\Referral;

use App\Rules\Refferal\ReferralEmailExistRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                new ReferralEmailExistRule(),
            ],
            'mobile' => ['required', 'string'],
        ];
    }
}
