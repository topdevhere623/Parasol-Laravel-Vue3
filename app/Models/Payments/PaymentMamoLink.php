<?php

namespace App\Models\Payments;

use App\Models\BaseModel;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMamoLink extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use UuidOnCreating;

    protected $casts = [
        'is_active' => 'boolean',
        'response' => 'json',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }
}
