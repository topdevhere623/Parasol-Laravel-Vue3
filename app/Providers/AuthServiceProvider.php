<?php

namespace App\Providers;

use App\Models\BackofficeUser;
use App\Models\Program;
use App\Policies\BackofficeUserPolicy;
use Clockwork\Authentication\SimpleAuthenticator;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        BackofficeUser::class => BackofficeUserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (app()->isProduction()) {
            clock()->setAuthenticator(new SimpleAuthenticator('ivan##@'));
        }

        if (!$this->app->routesAreCached()) {
            Passport::routes(function ($router) {
                $router->forAccessTokens();
            });
        }

        Passport::tokensExpireIn(now()->addMinutes(config('services.passport.access_token_expires')));
        Passport::refreshTokensExpireIn(now()->addMinutes(config('services.passport.refresh_token_expires')));
        Passport::personalAccessTokensExpireIn(now()->addDay());

        \Auth::viaRequest('program-api', function (Request $request) {
            return $request->bearerToken() ? Program::where('api_key', $request->bearerToken())->hasAccessApi()->first(
            ) : null;
        });
    }
}
