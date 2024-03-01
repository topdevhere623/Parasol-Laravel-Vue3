<?php

namespace App\Models\Payments;

use App\Models\BaseModel;
use App\Models\Plan;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentMethod extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;
    use Selectable;

    protected string $selectableValue = 'title';

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    public const SPOTII_PAYMENT_ID = 1;
    public const CHECKOUT_PAYMENT_ID = 2;
    public const CHECKOUT_MONTHLY_PAYMENT_ID = 5;
    public const FOC_PAYMENT_ID = 7;
    public const ENTERTAINER_PAYMENT_ID = 8;
    public const HSBC_CHECKOUT_PAYMENT_ID = 10;
    public const AMAZON_PAYFORT_PAYMENT_ID = 11;
    public const TABBY_THREE_PAYMENT_ID = 12;
    public const TABBY_SIX_PAYMENT_ID = 13;
    public const TABBY_FOUR_PAYMENT_ID = 14;

    public const HSBC_CHECKOUT_CODE = 'hsbc_checkout';

    public const CREDIT_CARD_PAYMENT_CODES = [
        'checkout',
        'monthly',
        'hsbc_checkout',
    ];

    protected $guarded = ['id', 'uuid'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string)Str::orderedUuid();
        });
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_payment_method');
    }

    public function activePlans(): BelongsToMany
    {
        return $this->plans()->active();
    }

    public function getIsCreditCardPaymentAttribute(): bool
    {
        return $this->getIsCreditCardPaymentByCode($this->code);
    }

    public function getIsCreditCardPaymentByCode(string $code): bool
    {
        return in_array($code, static::CREDIT_CARD_PAYMENT_CODES);
    }
}
