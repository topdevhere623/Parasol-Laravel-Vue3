<?php

namespace App\Http\Resources\SalesQuote;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sales_person' => $this->sales_person,
            'duration' => $this->duration,
            'clubs_count' => $this->clubs_count,
            'singles_count' => $this->singles_count,
            'families_count' => $this->families_count,
            'email_sent' => $this->email_sent,
            'corporate_client' => $this->corporate_client,
            'corporate_contact_name' => $this->corporate_contact_name,
            'corporate_contact_number' => $this->corporate_contact_number,
            'corporate_contact_email' => $this->corporate_contact_email,
            'display_monthly_value' => $this->display_monthly_value,
            'display_daily_per_club' => $this->display_daily_per_club,
            'calculated' => json_encode($this->json_data),
        ];
    }
}
