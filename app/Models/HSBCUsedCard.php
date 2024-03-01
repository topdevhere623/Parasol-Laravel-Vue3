<?php

namespace App\Models;

use App\Models\Member\Member;
use App\Models\Payments\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HSBCUsedCard extends BaseModel
{
    use SoftDeletes;

    public const STATUSES = [
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        'refunded' => 'refunded',
    ];

    protected $table = 'hsbc_used_cards';

    protected $casts = [
        'card_expiry_date' => 'date:d F Y',
        'canceled_at' => 'date:d F Y',
        'refunded_at' => 'date:d F Y',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (HSBCUsedCard $model) {
            if (!$model->card_token) {
                $model->card_token = self::generateToken(
                    $model->bin,
                    $model->card_last4_digits,
                    $model->card_expiry_date
                );
            }
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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

    public function scopeFreeCheckout(Builder $query): Builder
    {
        return $query->whereNotNull($this->getTable().'.total_price');
    }

//    public function getBinAttribute($value)
//    {
//        return substr_replace($value, ' ', 4, 0);
//    }

    public static function generateToken(string $bin, string $last_4_digits, Carbon $expiryDate): string
    {
        return $bin.$last_4_digits.$expiryDate->format('mY');
    }
}
