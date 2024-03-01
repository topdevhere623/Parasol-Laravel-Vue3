<?php

namespace App\Models\Partner;

use App\Models\BaseModel;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerContract extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'active' => 'active',
        'pending' => 'pending',
        'inactive' => 'inactive',
        'expired' => 'expired',
    ];

    public const TYPES = [
        'first_year' => 'first_year',
        'renewal' => 'renewal',
        'addendum' => 'addendum',
    ];

    public const ACCESS_TYPES = [
        'prepaid' => 'prepaid',
        'postpaid' => 'postpaid',
    ];

    public const KIDS_ACCESS_TYPES = [
        'linked' => 'linked',
        'individual' => 'individual',
    ];

    public const SLOTS_TYPES = [
        'revolving' => 'revolving',
        'slots' => 'slots',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $guarded = ['id'];

    // Relations

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function tranches(): HasMany
    {
        return $this->hasMany(PartnerTranche::class);
    }

    public function activePartnerTranches(): HasMany
    {
        return $this->tranches()->active();
    }

    public function files(): HasMany
    {
        return $this->hasMany(PartnerContractFile::class);
    }

    // Scopes

    public function scopeExpiredActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['active'])
            ->where('expiry_date', '<', today());
    }

    // Mutators & Accessors

    protected function billingPeriod(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => self::yearToBillingPeriod($value),
            set: fn (string $value) => self::billingPeriodToYear($value),
        );
    }

    public static function billingPeriodToYear($year): int
    {
        return (int)substr($year, 0, strpos($year, '-'));
    }

    public static function yearToBillingPeriod($billingPeriod): string
    {
        return sprintf('%s-%s', $billingPeriod, ++$billingPeriod);
    }

    public static function getBillingPeriodOptions(): array
    {
        $options = [];
        $start = 2019;
        $end = (int)date('Y') + 2;
        for ($year = $start; $year < $end; $year++) {
            $billingPeriod = self::yearToBillingPeriod($year);
            $options[$billingPeriod] = $billingPeriod;
        }

        return $options;
    }
}
