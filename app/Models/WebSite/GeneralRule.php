<?php

namespace App\Models\WebSite;

use App\Casts\FileCast;
use App\Models\BaseModel;
use App\Models\Program;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralRule extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'inactive' => 'Inactive',
        'active' => 'Active',
    ];

    public const FILE_CONFIG = [
        'image' => [
            'path' => 'general-rule',
            'size' => [100, 500, 800],
            'action' => ['resize'],
        ],
    ];

    protected $casts = [
        'image' => FileCast::class,
    ];

    // Relations

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class);
    }
}
