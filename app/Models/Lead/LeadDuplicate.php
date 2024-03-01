<?php

namespace App\Models\Lead;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadDuplicate extends BaseModel
{
    use SoftDeletes;

    public const STATUSES = [
        'potential_duplicate' => 'potential_duplicate',
        'duplicate' => 'duplicate',
        'not_duplicate' => 'not_duplicate',
    ];

    protected $guarded = [];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function duplicateLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'duplicate_lead_id');
    }
}
