<?php

namespace ParasolCRMV2\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use ParasolCRMV2\Menu\MenuManager;

class MenuController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function menu(): JsonResponse
    {
        Cache::delete('menu-'.Auth::id());
        $menu = Cache::remember('menu-'.Auth::id(), 86400, function () {
            return app(MenuManager::class)->all()->map->toArray()->values();
        });

        return response()->json([
            'menu' => $menu,
        ]);
    }
}
