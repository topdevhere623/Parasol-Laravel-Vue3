<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class ClubSortRequest extends FormRequest
{
    public function rules()
    {
        return [
            'clubs' => 'required',
            'program' => 'required',
        ];
    }
}
