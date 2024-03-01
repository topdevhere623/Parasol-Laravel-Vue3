<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Resources\MemberPortal\GeneralRuleResource;
use App\Models\Program;
use App\Models\WebSite\GeneralRule;

class AboutMembershipController extends MemberPortalBaseController
{
    public function index()
    {
        $this->abortNoAccess('about_membership');

        $programId = $this->member->program_id;
        if (Program::ENTERTAINER_SOLEIL_ID !== $programId) {
            $programId = Program::ADV_PLUS_ID;
        }
        $generalRules = GeneralRule::active()
            ->orderBy('name')
            ->whereHas(
                'programs',
                function ($query) use ($programId) {
                    $query
                        ->select('id')
                        ->where('id', $programId);
                }
            )
            ->get();

        return GeneralRuleResource::collection($generalRules);
    }

    public function show($id)
    {
        $this->abortNoAccess('about_membership');

        $generalRule = GeneralRule::active()->findOrFail($id);

        return new GeneralRuleResource($generalRule);
    }
}
