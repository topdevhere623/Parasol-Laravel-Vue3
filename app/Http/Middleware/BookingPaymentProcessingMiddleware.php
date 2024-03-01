<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookingPaymentProcessingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Random sleep (0.12-0.22 sec) to postpone query. Used for let cache react to many queries at once
        usleep((rand(60, 110) + rand(60, 110)) / 100 * 1000000);

        while (Cache::get("booking-payment-process-{$request->payment_id}-{$request->booking->id}", false)) {
            sleep(2);
        }

        return $next($request);
    }
}
