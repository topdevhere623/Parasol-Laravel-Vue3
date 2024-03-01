<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\Booking\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function view(Request $request, Booking $booking): JsonResponse
    {
        abort_unless(\Prsl::checkGatePolicy('view', Booking::class, $booking), 403, 'Not Allowed');

        return \Prsl::responseData(['booking' => BookingResource::make($booking)]);
    }
}
