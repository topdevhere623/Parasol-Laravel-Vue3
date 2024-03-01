<?php

namespace App\Http\Requests\CRM\SalesQuote;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'clubs_count' => 'required|integer',
            'corporate_client' => 'required|string',
            'corporate_contact_name' => 'required|string',
            'corporate_contact_number' => 'required|string',
            'corporate_contact_email' => 'required|email',
            'display_monthly_value' => 'nullable|string',
            'display_daily_per_club' => 'nullable|string',
            'duration' => 'required|numeric',
            'families_count' => 'required|integer',
            'salesPerson' => 'required|integer',
            'singles_count' => 'required|integer',
        ];
    }
}
