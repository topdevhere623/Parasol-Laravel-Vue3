<?php

namespace App\Models;

use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferType extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;

    protected $guarded = [
        'id',
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function activeOffers(): HasMany
    {
        return $this->offers()->active();
    }
}
