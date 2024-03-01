<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait AuthenticateBackofficeUser
{
    /**
     * @param string $email
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseBackofficeUserAuthData(string $email, string $password)
    {
        try {
            $response = Http::post(config('services.passport.endpoint'), [
                'grant_type' => 'password',
                'client_id' => config('services.passport.backoffice_users_client_id'),
                'client_secret' => config('services.passport.backoffice_users_client_secret'),
                'username' => $email,
                'password' => $password,
                'provider' => 'backoffice_users',
            ]);

            $response->throw();
            return response()->json($response->json());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            if ($e->getCode() === 400) {
                return response()->json(['message' => 'Unauthorized'], $e->getCode());
            }
            report($e);
            return response()->json(['message' => 'Something went wrong on the server'], 500);
        }
    }
}
