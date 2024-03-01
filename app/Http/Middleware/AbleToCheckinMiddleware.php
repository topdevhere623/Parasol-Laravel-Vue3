<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AbleToCheckinMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (\Auth::user()->hasTeam('club_admins')) {
            abort_if(!\Auth::user()->club || !\Auth::user()->club->checkin_availability, 403, 'Unable to check-in');
        } else {
            abort_unless(\Auth::user()->club, 403, 'You are not a club manager');
        }

        return $next($request);
    }
}
