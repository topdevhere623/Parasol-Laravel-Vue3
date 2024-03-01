<?php

namespace App\Http\Controllers\Api\v1\Program;

use App\Http\Resources\v1\Program\Club\ClubDetailsResource;
use App\Http\Resources\v1\Program\Club\ClubResource;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ClubController extends BaseProgramApiController
{
    public function index(): JsonResponse
    {
        /** @var Program $program */
        $program = auth()->user();

        if (!$program->landingPagePlan) {
            return response()->json(['data' => []]);
        }

        $clubs = $program->landingPagePlan
            ->clubsQuery()
            ->visibleInPlan()
            ->sort()
            ->paginate(static::PER_PAGE);

        return response()->json([
            'data' => [
                'per_page' => $clubs->perPage(),
                'page' => $clubs->currentPage(),
                'total' => $clubs->total(),
                'last_page' => $clubs->lastPage(),
                'items' => ClubResource::collection($clubs),
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        /** @var Program $program */
        $program = auth()->user();

        if (!$program->landingPagePlan) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }

        $club = $program->landingPagePlan->clubsQuery()
            ->with('gallery', 'city')
            ->where('uuid', $uuid)
            ->visibleInPlan()
            ->sort()
            ->first();

        if (!$club) {
            return response()->json(['message' => 'Club not found']);
        }

        return response()->json(['data' => ClubDetailsResource::make($club)]);
    }
}
