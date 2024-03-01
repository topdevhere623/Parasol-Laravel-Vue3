<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class ProgramPlanReferralPivot extends MorphPivot
{
    protected $table = 'program_plan_referral';
}
