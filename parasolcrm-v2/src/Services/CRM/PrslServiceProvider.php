<?php

namespace ParasolCRMV2\Services\CRM;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use ParasolCRMV2\Menu\MenuManager;

class PrslServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('PrslV2', fn () => new PrslService());
    }

    public function boot(): void
    {
        Route::macro('crud', function ($name, $controller) {
            Route::get($name.'/form', $controller.'@form');
            Route::get($name.'/document', $controller.'@document');
            Route::get($name.'/filters', $controller.'@filter');
            Route::get($name.'/chart', $controller.'@charts');
            Route::get($name.'/status', $controller.'@status');
            Route::get($name.'/table', $controller.'@table');
            Route::get($name.'/logs/{id}', $controller.'@logs');
            Route::get($name.'/relation-options/{field}', $controller.'@relationOptions');
            Route::apiResource($name, $controller);
        });

        $this->routes();

        MenuManager::register($this->menu());
    }

    protected function routes()
    {
        Route::prefix('api/crm/v2')
            ->middleware(['api'])
            ->namespace('App\Http\Controllers')
            ->group(base_path('routes/apiCrmV2.php'));

        Route::prefix('api/crm/v2')
            ->middleware(['api'])
            ->namespace('ParasolCRMV2\\Http\\Controllers')
            ->group(__DIR__.'/../../../routes/crm.php');
    }

    public function urlMacros()
    {
        //        URL::macro('backoffice', function (string $path = '', array $params = []) {
        //            $url = config('app.backoffice_url');
        //            $url .= $path ? "/{$path}" : '';
        //            $url .= $params ? ('?'.http_build_query($params)) : '';
        //            return $url;
        //        });
    }

    /** Иконки https://keenthemes.com/keenicons */
    protected function menu(): array
    {
        return [];
    }
}
