<?php

namespace App\Models\Zoho;

use App\Models\Member\MemberPrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZohoCustomer extends Model
{
    protected $table = 'zoho_customers';

    protected $guarded = [];

    public $timestamps = false;

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class, 'member_id');
    }
}
