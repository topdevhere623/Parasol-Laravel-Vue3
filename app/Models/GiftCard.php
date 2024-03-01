<?php

namespace App\Models;

use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GiftCard extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    // Constants

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
