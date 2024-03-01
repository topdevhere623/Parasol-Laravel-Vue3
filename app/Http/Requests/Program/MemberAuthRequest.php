<?php

namespace App\Http\Requests\Program;

use Illuminate\Foundation\Http\FormRequest;

class MemberAuthRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'membership_number' => 'required|string',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
