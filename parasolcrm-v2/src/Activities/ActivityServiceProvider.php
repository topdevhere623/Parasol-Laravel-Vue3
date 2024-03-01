<?php

namespace ParasolCRMV2\Activities;

use Illuminate\Support\ServiceProvider;

class ActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton('Activity', fn () => new Activity());
    }
}
