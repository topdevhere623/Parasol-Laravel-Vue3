<?php

namespace App\Models\Partner;

use App\Casts\FileCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerInventoryFile extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const FILE_CONFIG = [
        'file' => [
            'path' => 'partner-inventory/files',
        ],
    ];

    protected $casts = [
        'file' => FileCast::class,
    ];

    public function partnerInventory(): BelongsTo
    {
        return $this->belongsTo(PartnerInventory::class);
    }
}
