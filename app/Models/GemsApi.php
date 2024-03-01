<?php

namespace App\Models;

use App\Models\Member\Member;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GemsApi extends Model
{
    use SoftDeletes;

    protected $table = 'gems_api';

    protected $casts = [
        'request' => 'json',
    ];

    public const UPDATABLE_STATUSES = [
        Member::MEMBERSHIP_STATUSES['active'],
        Member::MEMBERSHIP_STATUSES['expired'],
        Member::MEMBERSHIP_STATUSES['cancelled'],
        Member::MEMBERSHIP_STATUSES['processing'],
        Member::MEMBERSHIP_STATUSES['payment_defaulted_on_hold'],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::orderedUuid()->toString();
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
