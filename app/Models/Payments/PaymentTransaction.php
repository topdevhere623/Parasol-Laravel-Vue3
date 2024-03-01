<?php

namespace App\Models\Payments;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    public const STATUSES = [
        'pending' => 'pending',
        'success' => 'success',
        'fail' => 'fail',
        'cancel' => 'cancel',
        'expiry' => 'expiry',

    ];

    public const TYPES = [
        'capture' => 'capture',
        'authorize' => 'authorize',
        'refund' => 'refund',
        'void' => 'void',
    ];

    protected $guarded = ['id', 'uuid'];

    public function __construct(array $attributes = [])
    {
        $this->type = self::TYPES['capture'];
        $this->status = self::STATUSES['pending'];

        parent::__construct($attributes);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentTransactionResponse(): HasOne
    {
        return $this->hasOne(PaymentTransactionResponse::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', static::STATUSES['pending']);
    }

    public function markAsFail(): self
    {
        $this->status = static::STATUSES['fail'];
        return $this;
    }

    public function markAsSuccess(): self
    {
        $this->status = static::STATUSES['success'];
        return $this;
    }

    public function markAsCancel(): self
    {
        $this->status = static::STATUSES['cancel'];
        return $this;
    }

    public function markAsExpiry(): self
    {
        $this->status = static::STATUSES['expiry'];
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status = static::STATUSES['pending'];
    }

    public function attachResponse($responseJson): ?PaymentTransactionResponse
    {
        if (!$responseJson) {
            return null;
        }
        $paymentTransactionResponse = new PaymentTransactionResponse();
        $paymentTransactionResponse->response_json = $responseJson;
        $paymentTransactionResponse->paymentTransaction()->associate($this);
        $paymentTransactionResponse->save();
        return $paymentTransactionResponse;
    }
}
