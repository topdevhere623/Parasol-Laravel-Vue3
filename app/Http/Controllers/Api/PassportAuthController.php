<?php

namespace App\Http\Controllers\Api;

use App\Mail\PasswordReset\BackofficeUserPasswordReset;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Laravel\Passport\Passport;

class PassportAuthController
{
    protected string $oauthClientProvider;

    protected string $emailField = 'email';

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        return $this->authRequest([
            'grant_type' => 'password',
            'username' => $request->input('email'),
            'password' => $request->input('password'),
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        return $this->authRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->input('refresh_token'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->api_logout();

        return response()->json(['message' => 'Successful logout']);
    }

    public function passwordResetRequest(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
        ]);

        $email = $request->email;

        $user = null;

        $model = config('auth.providers.'.$this->oauthClientProvider.'.model');

        if ($model && method_exists($model, 'findForPassport')) {
            $user = (new $model())->findForPassport($email);
        }

        if ($user) {
            $status = Password::broker($this->oauthClientProvider)
                ->sendResetLink(['id' => $user->id], function ($user, $token) {
                    $token = \Crypt::encrypt([
                        'token' => $token,
                        $this->emailField => $user->getEmailForPasswordReset(),
                    ]);
                    $this->sendPasswordResetNotification($user, $token);
                });

            if ($status == Password::RESET_THROTTLED) {
                return response()->json(
                    ['message' => 'Password reset email already sent. Please try again later'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        return response()->json(['message' => 'Password reset email sent']);
    }

    public function setPasswordRequest(Request $request, $firstCreate = false): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required',
        ]);

        try {
            $credentials = array_merge($request->toArray(), \Crypt::decrypt($request->input('token')));
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Invalid token'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $status = Password::broker($this->oauthClientProvider)
            ->reset($credentials, function ($user, $password) use ($firstCreate) {
                $this->setNewPassword($user, $password, $firstCreate);
            });

        if (in_array($status, [Password::INVALID_USER, Password::INVALID_TOKEN])) {
            return response()->json(['message' => 'Invalid token'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->authRequest([
            'grant_type' => 'password',
            'username' => $credentials[$this->emailField],
            'password' => $credentials['password'],
        ]);
    }

    public function changePasswordRequest(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (!Hash::check($value, $request->user()->password)) {
                        $fail('Your password was not updated, since the provided current password does not match.');
                    }
                },
            ],
            'password' => 'required|string|confirmed|min:8|different:current_password',
            'password_confirmation' => 'required',
        ]);

        if ($this->setNewPassword($request->user(), $request->input('password'))) {
            return response()->json(['message' => 'Password successful changed']);
        }
        return response()->json(['message' => 'Unable to change password'], 400);
    }

    public function sendPasswordResetNotification($user, $token): void
    {
        Mail::to($user->getEmailForPasswordReset())
            ->send(
                new BackofficeUserPasswordReset([
                    'first_name' => $user->first_name,
                    'url' => \URL::backoffice('reset-password', ['token' => $token]),
                ])
            );
    }

    public function setNewPassword($user, $password, $firstCreate = false): bool
    {
        $user->password = bcrypt($password);
        if ($firstCreate && key_exists(
            'password_created_at',
            $user->attributesToArray()
        ) && !$user->password_created_at) {
            $user->password_created_at = now();
        }
        return $user->save();
    }

    protected function authRequest(array $data): JsonResponse
    {
        try {
            $client = $this->getClient();

            $response = Http::withHeaders([
                'user-agent' => \request()->header('user-agent'),
                'x-forwarded-for' => \request()->ip(),
            ])
                ->post(
                    config('services.passport.endpoint'),
                    array_merge($data, [
                        'client_id' => $client->id,
                        'client_secret' => $client->secret,
                        'scope' => '',
                        'provider' => $this->oauthClientProvider,
                    ])
                );

            $response->throw();

            return response()->json($response->json());
        } catch (\Illuminate\Http\Client\RequestException $exception) {
            if (in_array($exception->getCode(), [400, 401])) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            if ($exception->getCode() === 500) {
                report($exception);
            }

            return response()->json(
                ['message' => 'Something went wrong on the server'],
                $exception->getCode()
            );
        }
    }

    public function getClient(): ?object
    {
        $cache = \Cache::supportsTags() ? \Cache::tags('passport') : \Cache::getFacadeRoot();

        return $cache->remember(
            'passport-client:'.$this->oauthClientProvider,
            now()->addHours(6)->diffInSeconds(),
            function () {
                $passportClient = Passport::client()::where('provider', $this->oauthClientProvider)
                    ->where('password_client', 1)
                    ->where('revoked', 0)
                    ->latest()
                    ->first();

                throw_unless(
                    $passportClient,
                    ModelNotFoundException::class,
                    'Passport client not found, please create new one'
                );

                return (object)$passportClient->only(['id', 'secret', 'provider']);
            }
        );
    }
}
