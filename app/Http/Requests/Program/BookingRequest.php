<?php

namespace App\Http\Requests\Program;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'external_id' => 'required|string',
            'extra_params' => 'sometimes|array',
            'package_id' => 'sometimes|required|string',
            ...$this->memberArrayRules(),
            ...$this->memberArrayRules('partner'),

        ];
    }

    public function authorize()
    {
        return true;
    }

    public function memberArrayRules($memberType = 'member'): array
    {
        return [
            $memberType => 'array',
            "{$memberType}.first_name" => 'sometimes|required|string',
            "{$memberType}.last_name" => 'sometimes|required|string',
            "{$memberType}.email" => 'sometimes|required|email',
            "{$memberType}.phone" => 'sometimes|required',
        ];
    }
}
