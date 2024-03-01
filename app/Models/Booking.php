<?php

namespace App\Models;

use App\Enum\Booking\StepEnum;
use App\Models\Club\Club;
use App\Models\Lead\Lead;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\Member\MembershipRenewal;
use App\Models\Member\MembershipSource;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentTransaction;
use App\Models\Zoho\ZohoInvoice;
use App\Scopes\ProgramAdminScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends BaseModel
{
    use SoftDeletes;

    protected $table = 'bookings';

    public const TYPES = [
        'booking' => 'booking',
        'renewal' => 'renewal',
    ];

    protected $casts = [
        'last_step_changed_at' => 'datetime',
        'step' => StepEnum::class,
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ProgramAdminScope());
    }

    // Relationships

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class);
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'booking_club')->oldest('title');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            PaymentTransaction::class,
            Payment::class,
            'id',
            'payment_id',
            'payment_id',
            'id'
        );
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(BookingSnapshot::class);
    }

    public function gemsApi(): HasOne
    {
        return $this->hasOne(GemsApi::class)->latest();
    }

    public function programApiRequest(): HasOne
    {
        return $this->hasOne(ProgramApiRequest::class)->latest();
    }

    public function membershipSource(): BelongsTo
    {
        return $this->belongsTo(MembershipSource::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function hsbcUsedCard(): HasOne
    {
        return $this->hasOne(HSBCUsedCard::class);
    }

    public function memberInstruction(): HasOne
    {
        return $this->hasOne(BookingMemberInstruction::class);
    }

    public function membershipRenewal(): HasOne
    {
        return $this->hasOne(MembershipRenewal::class);
    }

    public function addSnapshotData($data): bool
    {
        $snapshotRow = $this->snapshot()->firstOrNew();
        $snapshotRow->data = array_replace_recursive($snapshotRow->data ?? [], $data);

        return $snapshotRow->save();
    }

    public function getSnapshotData(): null|array
    {
        return $this->snapshot?->data;
    }

    public function isProgramSource(...$programSource): bool
    {
        return optional(optional(optional($this->plan)->package)->program)->isProgramSource(...$programSource);
    }

    public function getIsRenewalAttribute(): bool
    {
        return $this->type == Booking::TYPES['renewal'];
    }

    protected function discountAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value): float => $this->coupon_amount + $this->gift_card_discount_amount
        );
    }

    public function calculateMembershipEndDate(Carbon $startDate): Carbon
    {
        if ($this->paymentMethod->code == 'monthly') {
            return $startDate->clone()->addDays($this->plan->getDurationInDays())->endOfMonth();
        } elseif ($this->paymentMethod->code == 'hsbc_checkout' || !$this->plan->show_start_date_on_booking) {
            return $startDate->clone()->addDays($this->plan->getDurationInDays())->subDay();
        }
        return $startDate->clone()->addDays($this->plan->getDurationInDays())->subDay();
    }

    // Get available start date for select on booking step 3 form. Null if not available
    public function getStartDateOption(): ?Carbon
    {
        return match (true) {
            !$this->isStartDateSelectable() => null,
            !!$this->membershipRenewal => $this->membershipRenewal->calculateDueDate(),
            default => today(),
        };
    }

    public function getStartDate(Carbon $defaultDate): ?Carbon
    {
        return match (true) {
            !$this->isStartDateSelectable() => $this->membershipRenewal?->calculateDueDate() ?? today(),
            !!$this->membershipRenewal => $this->isStartDateSelectable(
            ) ? $defaultDate : $this->membershipRenewal->calculateDueDate(),
            default => $defaultDate,
        };
    }

    public function isStartDateSelectable(): bool
    {
        return $this->plan->show_start_date_on_booking
            && !in_array($this->paymentMethod?->code, ['monthly', 'hsbc_checkout']);
    }

    public function activityRules($value): array
    {
        return [
            'member_id' => fn () => optional(Member::find($value))->full_name,
            'plan_id' => fn () => optional(Plan::find($value))->title,
            'payment_method_id' => fn () => optional(PaymentMethod::find($value))->title,
            'coupon_id' => function () use ($value) {
                return Coupon::find($value)?->getDescription() ?? $value;
            },
            'is_recurring' => fn () => $value ? 'Yes' : 'No',
            'membership_source_id' => fn () => optional(MembershipSource::find($value))->title,
        ];
    }

    public function zohoInvoice(): HasOne
    {
        return $this->hasOne(ZohoInvoice::class, 'booking_id', 'id');
    }
}
