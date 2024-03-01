<?php

namespace App\Models\Reports;

use App\Models\Club\Checkin;

// Separate model for permissions check

class HSBCClubVisitsSummary extends Checkin
{
    protected $table = 'checkins';
}
