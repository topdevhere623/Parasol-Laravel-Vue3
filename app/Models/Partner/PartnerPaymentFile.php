<?php

namespace App\Models\Partner;

use App\Casts\FileCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerPaymentFile extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const FILE_CONFIG = [
        'file' => [
            'path' => 'partner-payments/files',
        ],
    ];

    protected $casts = [
        'file' => FileCast::class,
    ];

    public function partnerPayment(): BelongsTo
    {
        return $this->belongsTo(PartnerPayment::class);
    }
}
