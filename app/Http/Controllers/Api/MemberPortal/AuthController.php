<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Controllers\Api\PassportAuthController;
use App\Http\Resources\MemberPortal\MemberResource;
use App\Mail\PasswordReset\MemberPasswordReset;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends PassportAuthController
{
    protected string $oauthClientProvider = 'members';

    protected string $emailField = 'login_email';

    public function user(Request $request): MemberResource
    {
        return new MemberResource(
            $request->user()->load(
                'passkit',
                'program',
                'membershipType',
                'memberPortalPaymentSchedule',
                'coupon',
                'kids',
                'pendingMembershipRenewal'
            )
        );
    }

    public function sendPasswordResetNotification($user, $token): void
    {
        $urlParams = ['token' => $token];
        if ($user->program_id == Program::ENTERTAINER_SOLEIL_ID) {
            $urlParams['source'] = 'entertainer';
        }

        Mail::to($user->getEmailForPasswordReset())
            ->send(
                new MemberPasswordReset(
                    $user,
                    \URL::member('reset-password', $urlParams),
                )
            );
    }

    public function createPassword(Request $request): JsonResponse
    {
        return parent::setPasswordRequest($request, true);
    }
}
