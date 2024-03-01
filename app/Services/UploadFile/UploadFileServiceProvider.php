<?php

namespace App\Services\UploadFile;

use Illuminate\Support\ServiceProvider;

class UploadFileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton('UploadFile', fn () => new UploadFileService());
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        //
    }
}
