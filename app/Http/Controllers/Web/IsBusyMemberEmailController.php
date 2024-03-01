<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member\Member;
use Illuminate\Http\Request;

class IsBusyMemberEmailController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        return response()->json(['data' => Member::whereLoginEmail($request->input('email'))->count()]);
    }
}
