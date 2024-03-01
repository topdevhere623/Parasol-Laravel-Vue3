<?php

namespace App\Http\Controllers\Api\v1\Program;

use App\Http\Requests\Program\MemberAuthRequest;
use App\Http\Resources\v1\Program\Member\MemberResource;
use App\Models\Member\Member;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends BaseProgramApiController
{
    public function index(): JsonResponse
    {
        /** @var Program $program */
        $program = \Auth::user();

        $members = $program->members()
            ->with(
                'parent',
                'juniors',
                'kids',
                'clubs',
            )
            ->paginate(static::PER_PAGE);

        return response()->json([
            'data' => [
                'per_page' => $members->perPage(),
                'page' => $members->currentPage(),
                'total' => $members->total(),
                'last_page' => $members->lastPage(),
                'items' => MemberResource::collection($members),
            ],
        ]);
    }

    public function show(string $membershipNumber): JsonResponse
    {
        /** @var Program $program */
        $program = \Auth::user();

        $member = $program->members()
            ->where('member_id', $membershipNumber)
            ->with(
                'parent',
                'juniors',
                'kids',
                'clubs',
            )
            ->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found']);
        }

        return MemberResource::make($member)->response();
    }

    public function auth(MemberAuthRequest $request): JsonResponse
    {
        /** @var Program $program */
        $program = \Auth::user();

        /** @var Member $member */
        $member = $program->members()->where('member_id', $request->membership_number)->first();

        return match (true) {
            !$member => response()->json(['message' => 'Member not found'], Response::HTTP_NOT_FOUND),
            !$member->isAvailableForLogin() => response()->json(
                ['message' => 'Unavailable to login'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            ),
            default => response()->json(
                [
                    'data' => [
                        'url' => \URL::member(
                            'authenticate',
                            ['token' => $member->createToken('Gems Member Portal')->accessToken]
                        ),
                    ],
                ]
            )
        };
    }
}
