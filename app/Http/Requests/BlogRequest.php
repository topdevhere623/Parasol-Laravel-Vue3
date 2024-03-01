<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'nullable|numeric',
            'sort' => 'nullable|string',
            'query' => 'nullable|string',
        ];
    }
}
