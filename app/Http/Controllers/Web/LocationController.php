<?php

namespace App\Http\Controllers\Web;

use App\Models\City;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function areas(City $city): JsonResponse
    {
        return response()->json([
            'data' => $city->areas,
        ]);
    }
}
