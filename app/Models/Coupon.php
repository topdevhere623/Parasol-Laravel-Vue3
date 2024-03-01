<?php

namespace App\Models;

use App\Exceptions\CouponUnusableException;
use App\Models\Member\Member;
use Closure;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;
use Throwable;

class Coupon extends BaseModel
{
    use SoftDeletes;

    // Todo: refactor (unused)
    public const HSBC_COUPON_CODE = 'z52XIdjBAs';

    public const STATUSES = [
        'active' => 'active',
        'inactive' => 'inactive',
        'referrals_inactive' => 'referrals_inactive',
        'expired' => 'expired',
        'redeemed' => 'redeemed',
        'member_unknown' => 'member_unknown',
    ];

    public const TYPES = [
        'bulk' => 'bulk',
        'individually' => 'individually',
        'referral' => 'referral',
    ];

    public const AMOUNT_TYPES = [
        'percentage' => 'percentage',
        'fixed' => 'fixed',
    ];

    public const COUPONABLE_TYPES = [
        'member' => Member::class,
        'backoffice-user' => BackofficeUser::class,
    ];

    public const PLAN_TYPES = [
        'exclude' => 'exclude',
        'include' => 'include',
    ];

    public const DEFAULT_CODE_LENGTH = 10;

    public const DEFAULT_AMOUNT = 10;

    public const DEFAULT_LIMIT = 50;

    public const DEFAULT_NOTE = 'Share your referral code or submit your friends details in "My referrals" and we will contact them for you';

    protected $fillable = [
        'code',
        'amount',
        'amount_type',
        'status',
        'couponable_id',
        'couponable_type',
        'type',
        'usage_limit',
        'expiry_date',
        'channel_id',
    ];

    protected $casts = [
        'expiry_date' => 'date:d F Y',
    ];

    public static function generateCode(int $length = self::DEFAULT_CODE_LENGTH, string $prefix = null): string
    {
        do {
            $code = $prefix.str_replace(['I', 'l'], ['i', 'L'], Str::random($length));
        } while (self::where('code', $code)->count());

        return $code;
    }

    // Relationships

    /**
     * Get the parent couponable model (Member or BackofficeUser).
     */
    public function couponable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function belongsToPlans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'coupon_plan');
    }

    public function excludedPlans(): BelongsToMany
    {
        return $this->belongsToPlans()
            ->wherePivot('type', 'exclude');
    }

    public function includedPlans(): BelongsToMany
    {
        return $this->belongsToPlans()
            ->wherePivot('type', 'include');
    }

    // Scopes

    public function scopeIsMember(Builder $query): Builder
    {
        return $query->where('couponable_type', self::COUPONABLE_TYPES['member']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['active']);
    }

    public function scopeExpiredDate(Builder $query): Builder
    {
        return $query->whereDate('expiry_date', '<', today());
    }

    // Accessors

    public function getPlansAttribute()
    {
        return $this->plansQuery()->get();
    }

    public function getMemberAttribute(): ?Member
    {
        return $this->isMember() ? $this->couponable : null;
    }

    public function getActivePlansAttribute()
    {
        return $this->activePlans()->get();
    }

    public function isActive(): bool
    {
        return $this->status == self::STATUSES['active'];
    }

    public function getUsageLimitRemainingAttribute(): int
    {
        return $this->usage_limit - $this->number_of_used;
    }

    // public function getIsAvailableToUseAttribute(): bool
    // {
    //    return !$this->is_expired && $this->status == self::STATUSES['active'] && $this->usage_limit_remaining;
    // }

    public function plansPivotSubquery($type, $coupon_id): Closure
    {
        return fn ($query) => $query->select('plan_id')
            ->from('coupon_plan')
            ->where('coupon_id', $coupon_id)
            ->where('type', $type);
    }

    public function plansQuery(): Eloquent|Builder
    {
        return Plan::where(function ($query) {
            $query->where(function ($query) {
                $query->whereExists($this->plansPivotSubquery('include', $this->id));
                $query->whereIn('id', $this->plansPivotSubquery('include', $this->id));
            });
            $query->orWhere(function ($query) {
                $query->whereExists($this->plansPivotSubquery('exclude', $this->id));
                $query->whereNotIn('id', $this->plansPivotSubquery('exclude', $this->id));
            });
        });
    }

    public function isMember(): bool
    {
        return $this->couponable_type === self::COUPONABLE_TYPES['member'];
    }

    public function isBackofficeUser(): bool
    {
        return $this->couponable_type === self::COUPONABLE_TYPES['backoffice-user'];
    }

    public function activePlans()
    {
        return $this->plansQuery()->active();
    }

    public function isExpired(): bool
    {
        return $this->status == self::STATUSES['expired'] || $this->expiry_date->endOfDay()->isPast();
    }

    public function validateEmailDomain($email): bool
    {
        if (!$this->email_domain) {
            return true;
        }
        $allowedDomains = explode(', ', $this->email_domain);
        $emailDomain = Str::afterLast(strtolower($email), '@');
        return in_array($emailDomain, $allowedDomains);
    }

    /**
     * @throws CouponUnusableException|Throwable
     */
    public static function checkUsable(?Coupon $coupon, ?string $email, int $planId): bool
    {
        throw_unless(
            $coupon,
            new CouponUnusableException('The coupon you have used is not valid. Please speak to adv+ team')
        );
        throw_if(
            !$coupon->isActive() || $coupon->usageLimitRemaining <= 0,
            new CouponUnusableException('Coupon has been expired')
        );
        throw_unless(
            $coupon->validateEmailDomain($email),
            new CouponUnusableException('Use your corporate email address to avail the offer')
        );
        throw_unless(
            !$coupon->activePlans()->count() || $coupon->activePlans()->where('plans.id', $planId)->count(),
            new CouponUnusableException('Your coupon is not available to redeem with this adv+ plan')
        );
        return true;
    }

    public function calculateDiscount($amount): float
    {
        $discount = $this->amount_type == self::AMOUNT_TYPES['percentage'] ? $amount * $this->amount / 100 : $this->amount;
        return booking_amount_round($discount);
    }

    public function getDiscountPercent($amount): float
    {
        if (self::AMOUNT_TYPES['percentage']) {
            return $this->amount;
        }

        $discount = $amount / $this->calculateDiscount($amount) * 100;
        return booking_amount_round($discount);
    }

    public function incrementUsage(): self
    {
        $this->number_of_used++;
        return $this;
    }

    public function getDescription(): string
    {
        $str = "Code: {$this->code} Type: {$this->type}";
        if (!$ownerTitle = $this->ownerTitle()) {
            return $str;
        }
        return "{$str} Owner: {$ownerTitle}";
    }

    public function ownerTitle(): ?string
    {
        if (!$couponable = $this->couponable) {
            return '';
        }
        return $this->isMember() ? $couponable->member_id : $couponable->full_name;
    }
}
