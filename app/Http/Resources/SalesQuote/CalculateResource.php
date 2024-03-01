<?php

namespace App\Http\Resources\SalesQuote;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalculateResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'duration' => $request->duration,
            'clubs_count' => $request->clubs_count,
            'singles_count' => $request->singles_count,
            'families_count' => $request->families_count,
            'corporate_client' => $request->corporate_client,
            'corporate_contact_name' => $request->corporate_contact_name,
            'corporate_contact_number' => $request->corporate_contact_number,
            'corporate_contact_email' => $request->corporate_contact_email,
            'display_monthly_value' => in_array($request->display_monthly_value, ['true', 1]),
            'display_daily_per_club' => in_array($request->display_daily_per_club, ['true', 1]),
        ];
    }
}
