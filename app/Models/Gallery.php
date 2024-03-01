<?php

namespace App\Models;

use App\Casts\FileCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Gallery extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    /**
     * @var string[]
     */
    public $activityAttributes = ['name', 'sort'];

    public $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => FileCast::class,
    ];

    public function galleryable(): MorphTo
    {
        return $this->morphTo();
    }
}
