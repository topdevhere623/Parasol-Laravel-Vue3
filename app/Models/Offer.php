<?php

namespace App\Models;

use App\Casts\FileCast;
use App\Models\Club\Club;
use App\Models\Traits\Filterable;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends BaseModel
{
    use SoftDeletes;
    use Filterable;
    use UuidOnCreating;

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
        'expired' => 'expired',
    ];

    public const FILE_CONFIG = [
        'logo' => [
            'path' => 'offer/logo',
            'size' => [100, 180, 400],
            'action' => ['resize'],
        ],
        'gallery' => [
            'path' => 'offer/gallery',
            'size' => [100, 180, 400],
            'action' => ['resize'],
        ],
    ];

    protected $casts = [
        'expiry_date' => 'date:d F Y',
        'logo' => FileCast::class,
    ];

    public function offerType(): BelongsTo
    {
        return $this->belongsTo(OfferType::class, 'offer_type_id');
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'offer_club');
    }

    public function activeClubs(): BelongsToMany
    {
        return $this->clubs()->active();
    }

    public function gallery(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiredActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('expiry_date', '<', now());
    }

    public function activityRules($value): array
    {
        return [
            'offer_type_id' => fn () => optional(OfferType::find($value))->name,
        ];
    }
}
