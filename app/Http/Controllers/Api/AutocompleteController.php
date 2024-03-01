<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Corporate;
use Illuminate\Http\Request;

class AutocompleteController extends Controller
{
    public function corporate(Request $request)
    {
        if ($request->has('title') && $request->input('title')) {
            $corporates = Corporate::showOnMain()
                ->where('title', 'LIKE', "{$request->input('title')}%")
                ->select('title')
                ->orderBy('title')
                ->limit(config('advplus.autocomplete.corporate'))
                ->pluck('title')
                ->toArray();

            return response()->json(['data' => count($corporates) ? $corporates : []]);
        }

        return response()->json(['data' => []]);
    }
}
