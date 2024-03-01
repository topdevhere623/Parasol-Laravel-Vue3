<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsSubscriptionRequest;
use App\Models\NewsSubscription;

class NewsletterController extends Controller
{
    public function subscribe(NewsSubscriptionRequest $request)
    {
        NewsSubscription::create($request->all());

        return response()->json(['success' => true]);
    }
}
