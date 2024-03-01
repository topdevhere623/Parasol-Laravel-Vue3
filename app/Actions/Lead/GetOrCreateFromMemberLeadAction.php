<?php

namespace App\Actions\Lead;

use App\Models\Lead\Lead;
use App\Models\Member\Member;
use App\Models\Program;

class GetOrCreateFromMemberLeadAction
{
    public function handle(Member $member): Lead
    {
        if ($member->lead) {
            return $member->lead;
        }

        $ownerId = match (true) {
            $member->bdm_backoffice_user_id => $member->bdm_backoffice_user_id,
            $member->program_id == Program::ENTERTAINER_HSBC => Lead::OWNERS['Ritesh'],
            default => Lead::DEFAULT_OWNER,
        };

        $lead = (new GetOrCreateLeadAction())->handle(
            $member->login_email,
            $member->first_name,
            $member->last_name,
            $member->phone,
            $ownerId
        );

        $member->lead()->associate($lead);
        $member->saveQuietly();

        return $lead;
    }
}
