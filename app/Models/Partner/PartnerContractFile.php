<?php

namespace App\Models\Partner;

use App\Casts\FileCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerContractFile extends BaseModel
{
    use HasFactory;

    public const FILE_CONFIG = [
        'file' => [
            'path' => 'partner-contract',
        ],
    ];

    protected $casts = [
        'file' => FileCast::class,
    ];

    public function partnerContract(): BelongsTo
    {
        return $this->belongsTo(PartnerContract::class);
    }
}
