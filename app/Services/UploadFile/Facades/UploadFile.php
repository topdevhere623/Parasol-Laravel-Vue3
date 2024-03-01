<?php

namespace App\Services\UploadFile\Facades;

use Illuminate\Support\Facades\Facade;

class UploadFile extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'UploadFile';
    }
}
