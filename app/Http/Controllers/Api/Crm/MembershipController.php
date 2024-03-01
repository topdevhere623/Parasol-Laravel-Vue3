<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\Member\MembershipDuration;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function getDurations(Request $request)
    {
        if ($request->has('parentValue')) {
            return MembershipDuration::where('id', $request->input('parentValue'))
                ->pluck('title', 'id')
                ->toArray();
        }

        if ($request->has('query')) {
            return MembershipDuration::where('title', 'like', '%'.$request->input('query').'%')
                ->limit(5)
                ->pluck('title', 'id')->toArray();
        }
        return MembershipDuration::pluck('title', 'id');
    }
}
