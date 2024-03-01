<?php

namespace App\Traits;

trait UuidOnCreating
{
    public static function bootUuidOnCreating()
    {
        static::creating(function ($model) {
            $model->uuid = \Str::orderedUuid()->toString();
        });
    }
}
