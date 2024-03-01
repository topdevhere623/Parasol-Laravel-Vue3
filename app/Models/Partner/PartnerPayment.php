<?php

namespace App\Models\Partner;

use App\Casts\FileCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerPayment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'airtable_id',
        'cheque_number',
        'partner_id',
        'status',
        'amount',
        'date',
        'issued_by',
        'bank',
        'tax_invoice',
        'type',
    ];

    public const STATUSES = [
        'cashed' => 'cashed',
        'overdue' => 'overdue',
        'outstanding' => 'outstanding',
        'cancelled' => 'cancelled',
        'postponed' => 'postponed',
        'future payment' => 'future payment',
        'forecasted' => 'forecasted',
    ];

    public const ISSUED_BY = [
        'software' => 'software',
        'loyalty' => 'loyalty',
    ];

    public const BANKS = [
        'rakbank' => 'rakbank',
        'mashreq' => 'mashreq',
    ];

    public const TYPES = [
        'cheque' => 'cheque',
        'bank_transfer' => 'bank_transfer',
        'credit_card' => 'credit_card',
    ];

    public const FILE_CONFIG = [
        'tax_invoice' => [
            'path' => 'partner-payments/tax-invoices',
        ],
    ];

    protected $casts = [
        'tax_invoice' => FileCast::class,
    ];

    public function files(): HasMany
    {
        return $this->hasMany(PartnerPaymentFile::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function partnerTranche(): BelongsTo
    {
        return $this->belongsTo(PartnerTranche::class);
    }
}
