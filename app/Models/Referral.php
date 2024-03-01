<?php

namespace App\Models;

use App\Models\Member\Member;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referral extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public $fillable = [
        'reward',
        'reward_status',
        'status',
        'name',
        'email',
        'mobile',
        'code',
        'member_no',
        'member_id',
        'used_member_id',
        'notes',
    ];

    public const STATUSES = [
        'active' => 'active',
        'declined' => 'declined',
        'joined' => 'joined',
        'contacted' => 'contacted',
        'lead' => 'lead',
        'not_responding' => 'not_responding',
    ];

    public const REWARD_STATUSES = [
        'not_selected' => 'not_selected',
        'pending' => 'pending',
        'complete' => 'complete',
    ];

    public const REWARDS = [
        'cashback' => 'cashback',
        'additional_month' => 'additional_month',
        'additional_club' => 'additional_club',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function usedMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'used_member_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'code', 'code');
    }

    public function scopeJoined(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['joined']);
    }

    public function scopeRewardAvailable(Builder $query): Builder
    {
        return $query->joined()
            ->where('reward_status', static::REWARD_STATUSES['not_selected']);
    }

    public function isRewardAvailable(): bool
    {
        return $this->status == static::STATUSES['joined'] && $this->reward_status == static::REWARD_STATUSES['not_selected'];
    }
}
