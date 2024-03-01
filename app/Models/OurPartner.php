<?php

namespace App\Models;

use App\Casts\FileCast;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurPartner extends BaseModel
{
    use SoftDeletes;

    public const FILE_CONFIG = [
        'logo' => [
            'path' => 'our-partner/logo',
            'size' => [100, 200, 250],
            'action' => ['resize'],
        ],
    ];

    protected $casts = [
        'logo' => FileCast::class,
    ];

    // Relations

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class);
    }
}
