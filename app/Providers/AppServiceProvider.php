<?php

namespace App\Providers;

use App\Services\GemsApiService;
use App\Services\InstagramFeedService;
use App\Services\MeritCardService;
use App\Services\NocrmService;
use App\Services\PasskitService;
use App\Services\PlectoService;
use App\Services\WebsiteThemeService;
use FontLib\TrueType\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(InstagramFeedService::class, function () {
            return new InstagramFeedService(config('services.instagram_feed_access_token'));
        });

        $this->app->singleton(MeritCardService::class, function () {
            return new MeritCardService(
                config('services.merit.url'),
                config('services.merit.secret_key'),
                config('services.merit.store_id')
            );
        });

        $this->app->singleton(PasskitService::class, function () {
            return new PasskitService(
                config('services.passkit.api_url'),
                config('services.passkit.key'),
                config('services.passkit.secret')
            );
        });

        $this->app->singleton(GemsApiService::class, function () {
            return new GemsApiService(
                config('services.gems.secure_key'),
                config('services.gems.login'),
                config('services.gems.password'),
                !app()->isProduction()
            );
        });

        $this->app->singleton(NocrmService::class, function () {
            return new NocrmService(
                config('services.nocrm.api_key'),
                config('services.nocrm.subdomain'),
            );
        });

        $this->app->singleton(PlectoService::class, function () {
            return new PlectoService(
                config('services.plecto.username'),
                config('services.plecto.password'),
            );
        });

        $this->app->singleton(WebsiteThemeService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->environment('production', 'stage')) {
            \URL::forceScheme('https');
        }

        // Model::preventLazyLoading(!app()->isProduction());

        Queue::failing(function (JobFailed $event) {
            report($event->exception);
        });

        $this->urlMacros();
        $this->strMacros();
        $this->arrMacros();

        Builder::macro('getTableName', function (): string {
            assert($this instanceof Builder);
            return $this->from;
        });
    }

    public function urlMacros()
    {
        URL::macro('member', function (string $path = '', array $params = []) {
            $url = config('app.member_dashboard.url');
            $url .= $path ? "/{$path}" : '';
            $url .= $params ? ('?'.http_build_query($params)) : '';
            return $url;
        });

        URL::macro('website', function (string $path = '', array $params = []) {
            $url = $path ? "{$path}" : '';
            $url .= $params ? ('?'.http_build_query($params)) : '';

            return url($url);
        });

        URL::macro('uploads', function (string $path = '', array $params = []) {
            $path = ltrim($path, '/');
            $url = $path ? "uploads/{$path}" : '';
            $url .= $params ? ('?'.http_build_query($params)) : '';
            return URL::website($url);
        });

        URL::macro('backoffice', function (string $path = '', array $params = []) {
            if (str_starts_with(\Route::current()?->getAction('as'), 'apiCrm.v2.')) {
                $url = config('app.crm_url');
            } else {
                $url = config('app.backoffice_url');
            }
            $url .= $path ? "/{$path}" : '';
            $url .= $params ? ('?'.http_build_query($params)) : '';
            return $url;
        });
    }

    public function strMacros(): void
    {
        Str::macro('slugExtended', function ($title, $separator = '-', $language = 'en') {
            $title = $language == 'en' ? str_replace('&', 'and', $title) : $language;
            return Str::slug($title, $separator, $language);
        });

        Str::macro('onlyNumbers', function (?string $str) {
            return preg_replace('/\D/', '', $str);
        });
    }

    public function arrMacros(): void
    {
        Arr::macro('humanizeKeyValue', function (array|Collection $array) {
            return collect($array)->map(
                fn ($value, $key) => Str::of($key)->snake()->replace('_', ' ')->title().': '.$value
            )->implode(PHP_EOL);
        });
    }
}
