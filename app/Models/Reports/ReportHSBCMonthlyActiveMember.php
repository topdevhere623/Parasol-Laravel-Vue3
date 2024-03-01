<?php

namespace App\Models\Reports;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

// Log all active HSBC members in month
class ReportHSBCMonthlyActiveMember extends BaseModel
{
    use SoftDeletes;

    protected $table = 'report_hsbc_monthly_active_members';

    protected $guarded = [];
}
