<?php

namespace App\Http\Controllers\Api\Crm;

use App\Actions\Member\GetOrCreatePendingRenewalMemberAction;
use App\Http\Controllers\Controller;
use App\Mail\PasswordReset\MemberPasswordReset;
use App\Mail\WelcomeMemberPortal;
use App\Models\Member\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    public function authAsMemberWithToken(string $memberId)
    {
        if (Auth::user()->isAdmin() && $memberId) {
            $member = Member::findOrFail($memberId);

            if ($member && $token
                    = optional($member->createToken('Members Users Password Grant Client'))->accessToken) {
                return response()->json(['data' => \URL::member('authenticate', ['token' => $token])]);
            }
        }
        abort(403);
    }

    public function sendEmailToMember(Request $request)
    {
        $member = Member::findOrFail($request->input('member_id'));
        config(['auth.passwords.members.throttle' => '']);

        if ($request->input('type') == 'create_password_email') {
            Password::broker('members')
                ->sendResetLink(['id' => $member->id], function ($user, $token) {
                    $token = \Crypt::encrypt([
                        'token' => $token,
                        'login_email' => $user->getEmailForPasswordReset(),
                    ]);
                    Mail::to($user->login_email)
                        ->send(new WelcomeMemberPortal($user, $token));
                });
        }

        if ($request->input('type') == 'forgot_password_email') {
            Password::broker('members')
                ->sendResetLink(['id' => $member->id], function ($user, $token) {
                    $token = \Crypt::encrypt([
                        'token' => $token,
                        'login_email' => $user->getEmailForPasswordReset(),
                    ]);
                    Mail::to($user->getEmailForPasswordReset())
                        ->send(new MemberPasswordReset($user, \URL::member('reset-password', ['token' => $token])));
                });
        }

        return response('The email has been sent to the member.', 200);
    }

    public function generateRenewalLink(Request $request): JsonResponse
    {
        $member = Member::findOrFail($request->id);
        if ($member->member_type != Member::MEMBER_TYPES['member']) {
            return \Prsl::responseError('Invalid member type', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pendingRenewal = (new GetOrCreatePendingRenewalMemberAction())->handle($member);
        return \Prsl::responseData(['url' => $pendingRenewal->renewal_url], 'The renewal link has been generated.');
    }

}
