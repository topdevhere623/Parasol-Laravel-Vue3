<?php

namespace App\Observers;

use App\Jobs\Coupon\UpdateProgramCouponsJob;
use App\Jobs\ProgramGenerateClubDocumentJob;
use App\Models\Program;
use Illuminate\Support\Str;

class ProgramObserver
{
    public function creating(Program $program)
    {
        $program->uuid = (string)Str::orderedUuid();
    }

    public function saved(Program $program): void
    {
        if ($program->isDirty(['has_access_referrals', 'referral_amount', 'referral_amount_type'])) {
            UpdateProgramCouponsJob::dispatch($program->id);
        }
        if ($program->isDirty(
            [
                'name',
                'website_logo',
                'club_document_plan_id',
                'club_document_main_page_package_id',
                'club_document_join_today_available',
            ]
        ) && $program->club_document_available) {
            ProgramGenerateClubDocumentJob::dispatch($program->id)->onQueue('low');
        }
    }
}
