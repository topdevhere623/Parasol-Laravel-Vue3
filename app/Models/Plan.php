<?php

namespace App\Models;

use App\Models\Club\Club;
use App\Models\Member\MembershipDuration;
use App\Models\Member\MembershipType;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\HasMemberRelation;
use App\Models\Traits\Selectable;
use App\Scopes\ProgramAdminScope;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;
    use HasMemberRelation;
    use Selectable;

    // Using for clubs
    public const HSBC_LANDING_PLAN_ID = 94;

    public const HSBC_SINGLE_FREE = 87;

    public const HSBC_SINGLE_FAMILY_FREE = 92;

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    public const VAT_TYPES = [
        'exclude' => 'exclude',
        'include' => 'include',
    ];

    public const PLAN_CLUB_TYPES = [
        'exclude' => 'exclude',
        'include' => 'include',
        'fixed' => 'fixed',
    ];

    public const DURATION_TYPES = [
        'day' => 'day',
        'month' => 'month',
        'year' => 'year',
        'fixed_date' => 'fixed_date',
    ];

    public const RENEWAL_EMAIL_TYPES = [
        'default' => 'default',
        'corporate' => 'corporate',
        'special_offer' => 'special_offer',
    ];

    public const ALLOWED_CLUB_TYPES = [
        'all_available' => 'all_available',
        'limited' => 'limited',
    ];

    protected $casts = [
        'is_partner_available' => 'boolean',
        'show_children_block' => 'boolean',
        'is_coupon_conditional_purchase' => 'boolean',
        'is_giftable' => 'boolean',
        'is_family_plan_available' => 'boolean',
    ];

    protected string $selectableValue = 'title';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string)Str::orderedUuid();
        });

        static::saving(function ($model) {
            if ($model->allowed_club_type == static::ALLOWED_CLUB_TYPES['all_available']) {
                $model->number_of_clubs = 0;
            }

            $model->show_start_date_on_booking = $model->duration_type == static::DURATION_TYPES['fixed_date'] ? false : $model->show_start_date_on_booking;
        });

        static::addGlobalScope(new ProgramAdminScope());
    }

    // Relationships

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function renewalPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'renewal_package_id');
    }

    public function program(): HasOneThrough
    {
        return $this->hasOneThrough(
            Program::class,
            Package::class,
            'id',
            'id',
            'package_id',
            'program_id'
        );
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'plan_payment_method');
    }

    public function activePaymentMethods(): BelongsToMany
    {
        return $this->paymentMethods()->active();
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_plan')
            ->withPivot('type');
    }

    public function activeCoupons(): BelongsToMany
    {
        return $this->coupons()->where(function ($query) {
            return $query->where('status', 'active');
        });
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function membershipDuration(): BelongsTo
    {
        return $this->belongsTo(MembershipDuration::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function belongsToClubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'plan_club')
            ->withPivot('type');
    }

    public function excludedVisibleInPlanClubs(): BelongsToMany
    {
        return $this->belongsToClubs()
            ->wherePivot('type', 'exclude')
            ->visibleInPlan();
    }

    public function includedVisibleInPlanClubs(): BelongsToMany
    {
        return $this->belongsToClubs()
            ->wherePivot('type', 'include')
            ->visibleInPlan();
    }

    public function fixedVisibleInPlanClubs(): BelongsToMany
    {
        return $this->belongsToClubs()
            ->wherePivot('type', 'fixed')
            ->visibleInPlan();
    }

    public function clubsPivotSubquery($type, $plan_id): Closure
    {
        return fn ($query) => $query->select('club_id')
            ->from('plan_club')
            ->where('plan_id', $plan_id)
            ->where('type', $type);
    }

    public function clubsQuery(): Builder
    {
        return Club::where(function ($query) {
            $query->where(function ($query) {
                $query->whereExists($this->clubsPivotSubquery('include', $this->id));
                $query->whereIn('clubs.id', $this->clubsPivotSubquery('include', $this->id));
            });

            $query->orWhere(function ($query) {
                $query->whereExists($this->clubsPivotSubquery('exclude', $this->id));
                $query->whereNotIn('clubs.id', $this->clubsPivotSubquery('exclude', $this->id));
            });

            $query->orWhere(function ($query) {
                $query->whereNotExists($this->clubsPivotSubquery('exclude', $this->id));
                $query->whereNotExists($this->clubsPivotSubquery('include', $this->id));
            });
        });
    }

    public function scopeSort($query): Builder
    {
        return $query->orderBy('sort');
    }

    public function availableClubs(): Builder
    {
        return $this->clubsQuery()
            ->visibleInPlan()
            ->active();
    }

    public function activityRules($value): array
    {
        return [
            'is_block_children' => fn () => $value ? 'Yes' : 'No',
            'is_coupon_conditional_purchase' => fn () => $value ? 'Yes' : 'No',
            'package_id' => fn () => optional(Package::find($value))->title,
        ];
    }

    // Calculate duration for date or for current date (by default)
    public function calculateDuration(?Carbon $date = null): Carbon
    {
        $date ??= new Carbon();
        $date->endOfDay();

        return match ($this->duration_type) {
            self::DURATION_TYPES['day'] => $date->addDays($this->duration),
            self::DURATION_TYPES['month'] => $date->addMonths($this->duration),
            self::DURATION_TYPES['year'] => $date->addYears($this->duration),
            self::DURATION_TYPES['fixed_date'] => Carbon::parse($this->duration)->endOfDay(),

            default => $date,
        };
    }

    // Get duration in days for date or for current date (by default)
    public function getDurationInDays(?Carbon $date = null): int
    {
        return $this->calculateDuration($date)->diffInDays();
    }

    // Get duration in months for date or for current date (by default)
    public function getDurationInMonths(?Carbon $date = null): int
    {
        return $this->calculateDuration($date)->diffInMonths();
    }

    public function getFullPriceAttribute(): float
    {
        if ($this->vat_type == self::VAT_TYPES['exclude']) {
            return $this->price_without_vat + $this->vat_amount;
        }

        return $this->price;
    }

    public function getPriceWithoutVatAttribute(): float
    {
        return booking_amount_round(
            $this->vat_type == self::VAT_TYPES['exclude'] ? $this->price : $this->price / (Payment::VAT + 1),
        );
    }

    public function getVatAmountAttribute(): float
    {
        if ($this->vat_type == self::VAT_TYPES['exclude']) {
            return booking_amount_round($this->price * Payment::VAT);
        }

        return booking_amount_round($this->price - $this->price / (Payment::VAT + 1));
    }

    public function getBookingClubsDetails(): \stdClass
    {
        $availableClubs = $this->availableClubs()->pluck('id');
        $result = [
            'number_of_clubs' => $this->number_of_clubs,
            'available_clubs' => $availableClubs,
            'fixed_clubs' => $availableClubs,
        ];
        if ($this->allowed_club_type == static::ALLOWED_CLUB_TYPES['all_available']) {
            $result['number_of_clubs'] = count($availableClubs);
        } else {
            $result['fixed_clubs'] = $this->fixedVisibleInPlanClubs->pluck('id');
        }

        return (object)$result;
    }
}
