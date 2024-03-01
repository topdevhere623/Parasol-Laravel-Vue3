<?php

namespace App\Http\Requests\CRM\Checkin;

use Illuminate\Foundation\Http\FormRequest;

class CheckinMemberActionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'member' => 'required',
            'kids' => 'array',
        ];
    }
}
