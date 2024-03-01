<?php

namespace App\Models;

class PassportLoginHistory extends BaseModel
{
    public $timestamps = false;
    public $activityActive = false;

    protected $casts = [
        'created_at' => 'datetime:d F Y H:i',
    ];

    public function userable()
    {
        return $this->morphTo();
    }
}
