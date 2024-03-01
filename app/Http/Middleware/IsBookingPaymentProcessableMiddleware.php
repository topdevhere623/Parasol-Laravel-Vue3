<?php

namespace App\Http\Middleware;

use App\Enum\Booking\StepEnum;
use Closure;
use Illuminate\Http\Request;

class IsBookingPaymentProcessableMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $booking = $request->booking;

        if ($booking && in_array($booking->step, StepEnum::afterPaymentSteps())) {
            return redirect()->route('booking.step-'.$booking->step->getOldValue(), $booking->uuid);
        }

        return $next($request);
    }
}
