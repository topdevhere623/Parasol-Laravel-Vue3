<?php

namespace App\Http\Requests\Referral;

use App\Models\Referral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChooseRewardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reward' => [
                'required',
                'string',
                Rule::in(json_decode(Auth()->user()->program->rewards)),
            ],
            'club' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => request('reward') == Referral::REWARDS['additional_club']),
            ],
            'bank_name' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => request('reward') == Referral::REWARDS['cashback']),
            ],
            'account_name' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => request('reward') == Referral::REWARDS['cashback']),
            ],
            'iban' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => request('reward') == Referral::REWARDS['cashback']),
            ],
            'swift' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => request('reward') == Referral::REWARDS['cashback']),
            ],
            'currency' => [
                'nullable',
                'string',
                'in:aed',
                Rule::requiredIf(fn () => request('reward') == Referral::REWARDS['cashback']),
            ],
        ];
    }
}
