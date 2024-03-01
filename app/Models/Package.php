<?php

namespace App\Models;

use App\Casts\FileCast;
use App\Models\Member\MembershipSource;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Package extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;
    use Selectable;

    protected string $selectableValue = 'title';

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    public const RELATION_TYPES = [
        'corporate' => 'corporate',
        'reseller' => 'reseller',
        'b2c' => 'b2c',
    ];

    public const FILE_CONFIG = [
        'image' => [
            'path' => 'package',
            'size' => [100, 500, 1200],
            'action' => ['resize'],
        ],
        'mobile_image' => [
            'path' => 'package',
            'size' => [[888, 444]],
            'action' => ['resize'],
        ],
    ];

    protected $casts = [
        'image' => FileCast::class,
        'mobile_image' => FileCast::class,
        'show_merit_gift_code_block' => 'boolean',
        'show_on_homepage' => 'boolean',
        'is_booking_uae_phone' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = \Str::slugExtended($model->slug ?: $model->title);
        });

        static::creating(function ($model) {
            $model->uuid = (string)Str::orderedUuid();
        });

        static::deleted(function ($model) {
            $model->slug = Str::random();
            $model->save();
        });
    }

    // Relations

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class)->sort();
    }

    public function activePlans(): HasMany
    {
        return $this->plans()->active();
    }

    public function membershipSource(): BelongsTo
    {
        return $this->belongsTo(MembershipSource::class);
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function activeGiftCard(): BelongsTo
    {
        return $this->giftCard()
            ->active();
    }

    public function activityRules($value): array
    {
        return [
            'show_merit_gift_code_block' => fn () => $value ? 'Yes' : 'No',
            'show_on_homepage' => fn () => $value ? 'Yes' : 'No',
            'program_id' => fn () => optional(Program::find($value))->name,
        ];
    }
}
