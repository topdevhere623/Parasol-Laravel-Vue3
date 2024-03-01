<?php

namespace App\Providers;

use App\Services\Zoho\ZohoConnector;
use App\Services\Zoho\ZohoOAuthClient;
use App\Services\Zoho\ZohoRestClient;
use Illuminate\Support\ServiceProvider;

class ZohoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ZohoOAuthClient::class,
            static fn ($app) => new ZohoOAuthClient(
                config('zoho.client_secret'),
                config('zoho.client_id'),
                config('zoho.redirect_uri')
            )
        );

        $this->app->bind(
            ZohoRestClient::class,
            static fn ($app) => new ZohoRestClient(
                new ZohoConnector(),
                settings('zoho_organization_id'),
                $app->make(ZohoOAuthClient::class)
            )
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function provides()
    {
        return [
            ZohoOAuthClient::class,
        ];
    }
}
