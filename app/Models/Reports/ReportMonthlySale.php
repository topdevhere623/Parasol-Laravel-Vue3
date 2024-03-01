<?php

namespace App\Models\Reports;

use App\Models\BaseModel;

class ReportMonthlySale extends BaseModel
{
    protected $table = 'backoffice_users';

    public function getFullNameAttribute(): string
    {
        return $this->getRawOriginal('full_name') ?? "{$this->first_name} {$this->last_name}";
    }
}
