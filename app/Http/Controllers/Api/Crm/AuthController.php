<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Api\PassportAuthController;
use App\Http\Resources\BackofficeUserResource;
use Illuminate\Http\Request;

class AuthController extends PassportAuthController
{
    protected string $oauthClientProvider = 'backoffice_users';

    public function user(Request $request)
    {
        return new BackofficeUserResource($request->user());
    }
}
