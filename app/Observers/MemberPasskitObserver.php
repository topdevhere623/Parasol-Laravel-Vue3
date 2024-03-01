<?php

namespace App\Observers;

use App\Jobs\Passkit\PasskitDeleteMember;
use App\Models\Member\MemberPasskit;

class MemberPasskitObserver
{
    public function deleted(MemberPasskit $model): void
    {
        $model->load('member.program');

        if ($model->member && $model->member->program) {
            PasskitDeleteMember::dispatch($model->passkit_id);
        }
    }
}
