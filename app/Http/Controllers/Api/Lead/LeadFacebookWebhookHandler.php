<?php

namespace App\Http\Controllers\Api\Lead;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadFacebookWebhookHandler extends Controller
{
    public function __invoke(Request $request, $requestApiKey = ''): string|JsonResponse
    {
        abort_if(config('services.facebook.leads_webhook_token') !== $requestApiKey, 401, 'Invalid token');

        if ($request->hub_verify_token && $request->hub_verify_token === cache('fb_verify_token')) {
            return $request->hub_challenge;
        }

        Log::driver('webhook')->info(json_encode($request->toArray()));

        return response()->json([
            'message' => 'Lead has been created',
        ]);
    }
}
