<?php

namespace App\Models;

use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const DUBAI_BLUE_WATER_ID = 34;

    // Constants

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    // Relationships

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
