<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Models\Member\Member;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class MemberPortalBaseController extends Controller
{
    protected ?Member $member = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->member = optional(auth()->user())->load('program');
            return $next($request);
        });
    }

    protected function abortNoAccess($name): void
    {
        if (app()->runningInConsole()) {
            return;
        }
        abort_unless($this->member->hasAccess($name), Response::HTTP_FORBIDDEN, 'Not allowed');
    }
}
