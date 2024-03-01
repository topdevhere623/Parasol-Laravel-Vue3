<?php

namespace App\Repositories;

use App\Models\BackofficeUser;
use App\Models\Member\Member;
use App\Models\PasswordReset;
use Illuminate\Support\Str;

class PasswordRepository extends Repository
{
    public function getPasswordResetToken(): string
    {
        $key = config('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return hash_hmac('sha256', Str::random(40), $key);
    }

    public function savePasswordReset(string $email): string
    {
        $token = $this->getPasswordResetToken();
        PasswordReset::where('email', $email)->delete();

        $passwordReset = PasswordReset::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
        ]);

        return $passwordReset ? $token : $this->savePasswordReset($email);
    }

    public function getPasswordResetUrl(string $type, string $token): string
    {
        return $type === 'partner'
            ? \URL::backoffice('reset-password', ['token' => $token])
            : \URL::member('reset-password', ['token' => $token]);
    }

    public function getUserByTypeAndEmail(string $type, string $email)
    {
        if ($type === 'partner') {
            return BackofficeUser::where('email', $email)->first();
        }
        return Member::where('login_email', $email)->first();
    }

    public function updateUserByType(string $type, string $email, string $password)
    {
        if ($type === 'partner') {
            return BackofficeUser::where('email', $email)->update([
                'password' => bcrypt($password),
            ]);
        }
        return Member::where('login_email', $email)->update([
            'password' => bcrypt($password),
        ]);
    }

    public function getPasswordResetByToken(string $token)
    {
        return PasswordReset::where('token', $token)->first();
    }

    public function delete($passwordReset)
    {
        return PasswordReset::where('email', $passwordReset->email)
            ->where('token', $passwordReset->token)
            ->delete();
    }
}
