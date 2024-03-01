<?php

namespace App\Http\Requests\CRM\SalesQuote;

use Illuminate\Foundation\Http\FormRequest;

class CalculateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'nullable|integer',
            'corporate_client' => 'required|string',
            'display_monthly_value' => 'nullable|string',
            'clubs_count' => 'required|integer',
            'singles_count' => 'required|integer',
            'families_count' => 'required|integer',
            'duration' => 'required|numeric',
        ];
    }
}
