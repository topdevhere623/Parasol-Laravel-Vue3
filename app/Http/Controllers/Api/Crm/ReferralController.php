<?php

namespace App\Http\Controllers\Api\Crm;

use App\Repositories\MemberRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ReferralController extends BaseController
{
    protected MemberRepository $repository;

    public function __construct(MemberRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getMemberShotData(Request $request)
    {
        return $this->repository->getMemberShotData($request);
    }

    public function getMemberFullData(Request $request)
    {
        return $this->repository->getMemberFullData($request);
    }
}
