<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ZohoRequest;
use App\Services\Zoho\ZohoOAuthClient;

class ZohoController extends Controller
{
    public function oauth2callback(ZohoRequest $request, ZohoOAuthClient $client)
    {
        $client->generateAccessToken($request->get('code'));

        return response()->json(['success']);
    }
}
