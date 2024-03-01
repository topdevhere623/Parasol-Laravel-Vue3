<?php

namespace App\Http\Controllers\Api\Program;

use App\Http\Controllers\Api\PassportAuthController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends PassportAuthController
{
    protected string $oauthClientProvider = 'programs';

    public function login(Request $request): JsonResponse
    {
        $jsonResponse = parent::login($request);

        $data = $jsonResponse->getData();

        if ($jsonResponse->getStatusCode() == 200) {
            return response()->json([
                'status' => 'success',
                'token' => $data->access_token,
                'refresh_token' => $data->refresh_token,
            ]);
        }
        return response()->json(array_merge(['status' => 'error'], (array) $data), $jsonResponse->getStatusCode());
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $jsonResponse = parent::refreshToken($request);

        $data = $jsonResponse->getData();

        if ($jsonResponse->getStatusCode() == 200) {
            return response()->json([
                'status' => 'success',
                'token' => $data->access_token,
                'refresh_token' => $data->refresh_token,
            ]);
        }
        return response()->json(array_merge(['status' => 'error'], (array) $data), $jsonResponse->getStatusCode());
    }
}
