<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyUrlRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $redirect = \DB::table('file_path_maps')
            ->where('old_path', $request->route('path'))
            ->first();

        if ($redirect) {
            return redirect(url('uploads/'.$redirect->new_path), 301);
        }

        return abort(Response::HTTP_NOT_FOUND);
    }
}
