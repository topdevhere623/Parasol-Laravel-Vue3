<?php

namespace App\Http\Controllers\Api\Lead;

use App\Http\Controllers\Controller;
use App\Jobs\Lead\CreateFromWebhookRequestLeadJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadZapierWebhookHandler extends Controller
{
    public function __invoke(Request $request, $requestApiKey = ''): JsonResponse
    {
        abort_if(config('services.zapier.leads_webhook_token') !== $requestApiKey, 401, 'Invalid token');

        CreateFromWebhookRequestLeadJob::dispatchSync([
            'email' => $request->input('email', $request->input('Email')),
            'first_name' => $request->input('name', $request->input('FullName')),
            'phone' => $request->input('phone', $request->input('PhoneNumber')),
            'tags' => [$request->input('Source', $request->input('utm_source'))],
        ], 'Zapier');

        Log::driver('webhook')->info('zapier: '.json_encode($request->toArray()));

        return response()->json([
            'message' => 'Lead has been created',
        ]);
    }
}
