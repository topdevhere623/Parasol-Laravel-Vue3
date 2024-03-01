<?php

namespace ParasolCRM\Activities;

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
