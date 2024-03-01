<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait AuthenticateMember
{
    /**
     * @param array $data
     * @return \Illuminate\Auth\Access\Response|\Illuminate\Http\JsonResponse
     */
    private function authMember(array $data)
    {
        try {
            $response = Http::post(
                config('services.passport.endpoint'),
                [
                    'grant_type' => 'password',
                    'client_id' => config('services.passport.members_client_id'),
                    'client_secret' => config('services.passport.members_client_secret'),
                    'username' => $data['email'],
                    'password' => $data['password'],
                    'provider' => 'members',
                ]
            );

            $response->throw();
            return response()->json($response->json());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            if ($e->getCode() === 400) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            report($e);
            return response()->json(['message' => 'Something went wrong on the server'], $e->getCode());
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseMemberAuthData(string $email, string $password)
    {
        try {
            $response = Http::post(
                config('services.passport.endpoint'),
                [
                    'grant_type' => 'password',
                    'client_id' => config('services.passport.members_client_id'),
                    'client_secret' => config('services.passport.members_client_secret'),
                    'username' => $email,
                    'password' => $password,
                    'provider' => 'members',
                ]
            );

            $response->throw();
            return response()->json($response->json());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            if ($e->getCode() === 400) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            report($e);
            return response()->json(['message' => 'Something went wrong on the server'], $e->getCode());
        }
    }
}
