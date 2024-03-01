<?php

namespace App\Models;

use App\Models\Club\Club;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\ColumnLabelTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;
    use ColumnLabelTrait;

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    /** @var array */
    protected $guarded = ['id'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function activeAreas(): HasMany
    {
        return $this->areas()->active();
    }

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class);
    }

    public function activeClubs(): HasMany
    {
        return $this->clubs()->active();
    }
}
