<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class DuplicateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
