<?php

namespace App\Models\Zoho;

use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\Model;

class ZohoChartOfAccount extends Model
{
    use Selectable;

    protected string $selectableValue = 'account_name';

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'zoho_chartofaccounts';
}
