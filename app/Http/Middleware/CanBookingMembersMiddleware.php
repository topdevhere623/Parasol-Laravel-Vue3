<?php

namespace App\Http\Middleware;

use App\Enum\Booking\StepEnum;
use Closure;
use Illuminate\Http\Request;

class CanBookingMembersMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $booking = $request->booking;

        if ($booking && $booking->step == StepEnum::MembershipDetails) {
            return $next($request);
        }
        if ($booking && $booking->step && !route_is('booking.step-'.$booking->step->getOldValue())) {
            return redirect()->route('booking.step-'.$booking->step->getOldValue(), $booking->uuid);
        }
        abort(404);
    }
}
