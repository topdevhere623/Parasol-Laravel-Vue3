<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\CouponUnusableException;
use App\Http\Requests\Web\Coupon\CouponCheckRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends Controller
{
    public function check(CouponCheckRequest $request): JsonResponse
    {
        $coupon = Coupon::whereCode($request->code)->first();
        try {
            Coupon::checkUsable($coupon, $request->email, $request->plan_id);
            return response()->json(
                [
                    'data' => $coupon->only('amount_type', 'amount'),
                ]
            );
        } catch (CouponUnusableException $exception) {
            return response()->json(
                [
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
