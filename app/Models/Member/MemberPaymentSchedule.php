<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use App\Models\Booking;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberPaymentSchedule extends BaseModel
{
    use SoftDeletes;
    use HasFactory;

    public const STATUS = [
        'active' => 'active',
        'inactive' => 'inactive',
        'stopped' => 'stopped',
        'failed' => 'failed',
        'completed' => 'completed',
    ];

    public const CARD_STATUS = [
        'active' => 'active',
        'failed' => 'failed',
        'expired' => 'expired',
    ];

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'charge_date' => 'date',
        'card_expiry_date' => 'date',
    ];

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'member_payment_schedule_payment');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS['active']);
    }

    public function scopeCardChangeable($query)
    {
        return $query->whereIn('status', [self::STATUS['active'], self::STATUS['failed']]);
    }

    public function scopeShouldBeCharged($query)
    {
        $query->active()
            ->whereDate('charge_date', '<=', now())
            ->where('status', static::STATUS['active'])
            ->whereHas('member', function ($query) {
                $query->availableForMonthlyCharge();
            });
    }

    public function setCardExpiryDate(int $month, int $year): self
    {
        $this->card_expiry_date = Carbon::parse("{$year}-{$month}-01")
            ->lastOfMonth();

        return $this;
    }

    // https://docs.google.com/spreadsheets/d/1ge4jcYE5awo1NDzCwfB2UGBZ2kynn_B1l6i1lp2AFAs/edit#gid=0
    public static function calculate(float $totalPrice, int $durationInMonths, Carbon $startDate = null): object
    {
        $startDate ??= today();

        $recurringChargeDate = $startDate->clone()->addMonths(2)->startOfMonth();

        $days = $recurringChargeDate->diffInDays($startDate);

        $firstChargeAmount = booking_amount_round(($totalPrice / 365) * $days);

        $monthlyChargeAmount = $durationInMonths ? booking_amount_round(($totalPrice / $durationInMonths)) : 0;

        return (object)[
            'days' => $days,
            'next_charge_date' => $recurringChargeDate->format('d.m.Y'),
            'first_charge' => $firstChargeAmount,
            'monthly_charge' => $monthlyChargeAmount,
        ];
    }

    public function calculateOverdueMonths(): int
    {
        if ($this->charge_date->isFuture()) {
            return 0;
        }

        $date = $this->member->end_date->isPast() ? $this->member->end_date : now();

        return $this->charge_date->diffInMonths($date) + 1;
    }

    /**
     * Остаток суммы для полной оплаты подписки
     */
    public function calculateRemainAmount(): float
    {
        return ($this->member->end_date->diffInMonths($this->charge_date) + 1) * $this->monthly_amount;
    }

    public function calculateChargeAmount(): float
    {
        return $this->monthly_amount * $this->calculateOverdueMonths();
    }

    public function generateCardChangeAuthToken(): self
    {
        $this->card_change_auth_token = \Str::random(128);
        return $this;
    }

    public function markAsActive(): self
    {
        $this->status = static::STATUS['active'];
        return $this;
    }

    public function markAsCompleted(): self
    {
        $this->status = static::STATUS['completed'];
        return $this;
    }

    public function markAsFailed(): self
    {
        $this->status = static::STATUS['failed'];
        return $this;
    }
}
