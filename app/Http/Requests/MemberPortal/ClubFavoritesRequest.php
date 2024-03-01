<?php

namespace App\Http\Requests\MemberPortal;

use Illuminate\Foundation\Http\FormRequest;

class ClubFavoritesRequest extends FormRequest
{
    public function rules()
    {
        return [
            'uuid' => 'required|string',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
