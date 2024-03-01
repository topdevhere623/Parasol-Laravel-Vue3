<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getCountries(Request $request)
    {
        if ($request->has('parentValue')) {
            return Country::where('id', $request->input('parentValue'))
                ->pluck('country_name', 'id')
                ->toArray();
        }
        if ($request->has('query')) {
            return Country::where('country_name', 'like', '%'.$request->input('query').'%')
                ->limit(5)
                ->pluck('country_name', 'id')->toArray();
        }
        // return Country::limit(5)
        //    ->get()
        //    ->pluck('country_name', 'id')
        //    ->toArray();
        return Country::pluck('country_name', 'id');
    }
}
