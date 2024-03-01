<?php

namespace App\Models;

use App\Models\Member\Member;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberUsedCoupon extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'coupon_id',
        'plan_id',
        'type',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function channel(): HasOneThrough
    {
        return $this->hasOneThrough(
            Channel::class,
            Coupon::class,
            'id',
            'id',
            'coupon_id',
            'channel_id'
        );
    }
}
