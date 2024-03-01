<?php

namespace App\Models\Payments;

use App\Models\BaseModel;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\HSBCUsedCard;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\Zoho\ZohoInvoice;
use App\Scopes\ProgramAdminScope;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use UuidOnCreating;

    // VAT 5%
    public const VAT = 0.05;

    // Monthly payment card auth fee
    public const CARD_CHANGE_AUTH_FEE = 1;

    public const STATUSES = [
        'pending' => 'pending',
        'failed' => 'failed',
        'paid' => 'paid',
        'refunded' => 'refunded',
        'partial_refunded' => 'partial_refunded',
        'other' => 'other',
        'unknown' => 'unknown',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ProgramAdminScope());
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function hsbcUsedCard(): HasOne
    {
        return $this->hasOne(HSBCUsedCard::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function lastPaymentTransaction(): HasOne
    {
        return $this->hasOne(PaymentTransaction::class)
            ->latestOfMany();
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PaymentFile::class);
    }

    public function markAsFailed(): self
    {
        $this->status = static::STATUSES['failed'];
        return $this;
    }

    public function markAsPaid(): self
    {
        $this->status = static::STATUSES['paid'];
        return $this;
    }

    public function scopePaid($query)
    {
        return $query->where('status', static::STATUSES['paid']);
    }

    public function activityRules($value): array
    {
        return [
            'member_id' => fn () => optional(Member::find($value))->full_name,
            'payment_method_id' => fn () => optional(PaymentMethod::find($value))->title,
            'payment_type_id' => fn () => optional(PaymentType::find($value))->title,
            'coupon_id' => function () use ($value) {
                return Coupon::find($value)?->getDescription() ?? $value;
            },
        ];
    }

    public function isRefundable(float $amount = 0): bool
    {
        $checkStatus = in_array($this->status, [self::STATUSES['paid'], self::STATUSES['partial_refunded']]);
        $refundableAmount = $this->getRefundableAmount();

        return $checkStatus && $refundableAmount != 0 && $refundableAmount >= $amount;
    }

    public function getRefundableAmount(): float
    {
        return $this->total_amount - $this->refund_amount;
    }

    public function isPending(): bool
    {
        return $this->status == self::STATUSES['pending'];
    }

    public function calculateVatAndTotal(): void
    {
        $withAppliedDiscount = $this->subtotal_amount - $this->discount_amount;
        $this->total_amount_without_vat = $withAppliedDiscount;
        $this->vat_amount = booking_amount_round($withAppliedDiscount * Payment::VAT);
        $this->total_amount = $this->total_amount_without_vat + $this->vat_amount;
    }

    public function zohoInvoice(): BelongsTo
    {
        return $this->belongsTo(ZohoInvoice::class, 'zoho_invoice_id', 'id');
    }
}
