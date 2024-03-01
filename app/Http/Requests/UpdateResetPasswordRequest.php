<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResetPasswordRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => 'nullable|string',
            'token' => 'required|string',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
