<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function menu(): JsonResponse
    {
        $menu = (new Menu())->getAccessMenu();

        return response()->json(['data' => $menu]);
    }
}
