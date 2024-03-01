<?php

namespace App\Http\Middleware;

use App\Enum\Booking\StepEnum;
use Closure;
use Illuminate\Http\Request;

class IsBookingCompletedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $booking = $request->booking;

        if ($booking && $booking->step == StepEnum::Completed) {
            if (now()->subHours(24)->greaterThan($booking->last_step_changed_at)) {
                abort(404);
            }
            if (!route_is('booking.step-4')) {
                return redirect()->route('booking.step-4', $booking->uuid);
            }
        }

        return $next($request);
    }
}
