<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class NewsSubscription extends BaseModel
{
    use SoftDeletes;

    /** @var array */
    protected $guarded = ['id'];
}
