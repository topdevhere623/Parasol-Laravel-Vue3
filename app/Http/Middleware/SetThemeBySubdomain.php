<?php

namespace App\Http\Middleware;

use App\Services\WebsiteThemeService;
use Closure;
use Illuminate\Http\Request;

class SetThemeBySubdomain
{
    public function handle(Request $request, Closure $next)
    {
        if (is_entertainer_subdomain()) {
            app(WebsiteThemeService::class)->setFromSoleil();
        }

        return $next($request);
    }
}
