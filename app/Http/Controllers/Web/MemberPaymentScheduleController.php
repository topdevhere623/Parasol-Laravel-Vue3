<?php

namespace App\Http\Controllers\Web;

use App\Models\Member\MemberPaymentSchedule;
use Illuminate\Http\RedirectResponse;

class MemberPaymentScheduleController extends Controller
{
    public function auth(string $token): RedirectResponse
    {
        $MemberPaymentSchedule = MemberPaymentSchedule::where('card_change_auth_token', $token)
            ->active()
            ->firstOrFail();

        $member = $MemberPaymentSchedule->member()
            ->availableForMonthlyCharge()
            ->firstOrFail();

        if ($token = $member->createToken('Members Users Password Grant Client')?->accessToken) {
            return response()->redirectTo(
                \URL::member('authenticate', ['token' => $token, 'redirect' => '/personal/payment-details'])
            );
        }

        return response()->redirectTo(\URL::member());
    }
}
