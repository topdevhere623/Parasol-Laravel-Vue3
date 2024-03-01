<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestorePasswordRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => 'nullable|string',
            'email' => 'required|email',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
