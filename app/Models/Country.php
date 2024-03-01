<?php

namespace App\Models;

use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends BaseModel
{
    use HasFactory;
    use ActiveStatus;

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    public $timestamps = false;

    protected $guarded = ['id'];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function activeCities(): HasMany
    {
        return $this->cities()->active();
    }
}
