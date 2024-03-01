<?php

namespace App\Http\Controllers\Api\Program;

use App\Http\Controllers\Controller;
use App\Http\Resources\Program\ClubResource;
use App\Models\Club\Club;

class ClubController extends Controller
{
    public function index()
    {
        $clubs = Club::checkinAvailable()
            ->orderBy('title', 'ASC')
            ->get();

        return response()->json(['status' => 'success', 'result' => ClubResource::collection($clubs)]);
    }

    public function show($id)
    {
        $club = Club::checkinAvailable()
            ->where('old_id', $id)
            ->firstOrFail();

        return response()->json(['status' => 'success', 'result' => new ClubResource($club)]);
    }
}
