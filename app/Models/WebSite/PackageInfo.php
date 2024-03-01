<?php

namespace App\Models\WebSite;

use App\Casts\FileCast;
use App\Models\BaseModel;
use App\Models\Package;
use App\Models\Program;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageInfo extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'inactive' => 'Inactive',
        'active' => 'Active',
    ];

    public const TYPES = [
        'link' => 'link',
        'package' => 'package',
        'corporate_offer' => 'corporate_offer',
    ];

    public const FILE_CONFIG = [
        'image' => [
            'path' => 'package-info',
            'size' => [[555, 360], [600, 360]],
            'action' => ['resize'],
        ],
    ];

    protected $casts = [
        'image' => FileCast::class,
    ];

    public function isCorporateOffer(): bool
    {
        return $this->type == self::TYPES['corporate_offer'];
    }

    // Accessors

    public function getUrl(): ?string
    {
        return match ($this->type) {
            self::TYPES['package'] => route('booking.step-1', ['package' => $this->package->slug]),
            self::TYPES['link'] => $this->url,
            default => null,
        };
    }

    // Relations

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
