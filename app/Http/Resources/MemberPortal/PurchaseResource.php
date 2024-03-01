<?php

namespace App\Http\Resources\MemberPortal;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Member */
class PurchaseResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        if (!in_array($this->program_id, [Program::ENTERTAINER_SOLEIL_ID])) {
            $link = route('booking.step-1', ['package' => 'visiting-family-2023-24']);
        } else {
            $link = 'https://entertainer.advplus.ae'.route(
                'booking.step-1',
                ['package' => 'soleil-visiting-family'],
                false
            );
        }
        return [
            'title' => 'Visiting family membership',
            'text' => 'Visiting family membership is a temporary access available to purchase on a monthly basis. Please purchase the membership at least 7 days ahead of start date to ensure you receive the membership cards on time. Access types available: Single & Family (from AED 399 per month)',
            'link' => $link,
        ];
    }
}
