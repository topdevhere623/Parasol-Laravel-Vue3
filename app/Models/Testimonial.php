<?php

namespace App\Models;

use App\Casts\FileCast;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public const FILE_CONFIG = [
        'photo' => [
            'path' => 'testimonial',
            'size' => [200, 300, 500],
            'action' => ['resize'],
        ],
    ];

    protected $casts = [
        'photo' => FileCast::class,
    ];

    // Relations

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
