<?php

namespace App\Models\Partner;

use App\Models\BaseModel;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerTranche extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'active' => 'active',
        'pending' => 'pending',
        'awaiting_first_visit' => 'awaiting_first_visit',
        'inactive' => 'inactive',
        'expired' => 'expired',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function partner(): HasOneThrough
    {
        return $this->hasOneThrough(
            Partner::class,
            PartnerContract::class,
            'id',
            'id',
            'partner_contract_id',
            'partner_id'
        );
    }

    public function partnerPayments(): HasMany
    {
        return $this->hasMany(PartnerPayment::class);
    }

    public function partnerContract(): BelongsTo
    {
        return $this->belongsTo(PartnerContract::class);
    }

    public function scopeExpiredActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['active'])
            ->where('expiry_date', '<', today());
    }
}
