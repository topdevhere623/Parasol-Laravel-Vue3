<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ZohoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'code' => 'required',
            'accounts-server' => 'sometimes',
            // непонятно для чего
            'location' => 'sometimes',
        ];
    }
}
