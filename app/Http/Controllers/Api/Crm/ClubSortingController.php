<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\ClubSortRequest;
use App\Http\Resources\CRM\ClubSortingResource;
use App\Models\Club\Club;
use App\Models\Club\ProgramClubSort;
use App\Models\Program;
use Prsl;

class ClubSortingController extends Controller
{
    public function index(Program $program)
    {
        abort_unless(Prsl::checkGatePolicy('view', Club::class), 403, 'Not Allowed');

        return ClubSortingResource::collection(
            Club::with('city')
                ->sort($program->id)
                ->get()
        );
    }

    public function update(ClubSortRequest $request)
    {
        abort_unless(Prsl::checkGatePolicy('update', Club::class), 403, 'Not Allowed');

        $programId = $request->input('program');
        foreach ($request->input('clubs') as $sort => $club) {
            ProgramClubSort::where('program_id', $programId)
                ->where('club_id', $club['id'])
                ->update(['sort' => $sort + 1]);
        }

        Prsl::responseSuccess('Clubs sorting has been successfully updated');
    }
}
