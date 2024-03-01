<?php

namespace App\Models\Payments;

use App\Casts\FileCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentFile extends BaseModel
{
    use HasFactory;

    public const FILE_CONFIG = [
        'file' => [
            'path' => 'payment',
        ],
    ];

    protected $casts = [
        'file' => FileCast::class,
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
