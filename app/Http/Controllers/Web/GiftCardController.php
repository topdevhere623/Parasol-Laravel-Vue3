<?php

namespace App\Http\Controllers\Web;

use App\Actions\GiftCard\GiftCardGetDetailsAction;
use App\Actions\GiftCard\GiftCardGetDiscountAction;
use App\Exceptions\GifCard\GiftCardException;
use App\Http\Requests\Web\GiftCard\GiftCardBalanceRequest;
use App\Http\Requests\Web\GiftCard\GiftCardGetDiscountRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GiftCardController extends Controller
{
    public function balance(GiftCardBalanceRequest $request): JsonResponse
    {
        try {
            $balance = (new GiftCardGetDetailsAction())->handle(
                $request->card_type,
                $request->card_number
            );
        } catch (GiftCardException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return response()->json(
            [
                'data' => $balance,
            ]
        );
    }

    public function discount(GiftCardGetDiscountRequest $request): JsonResponse
    {
        try {
            $balance = (new GiftCardGetDiscountAction())->handle(
                $request->card_type,
                $request->card_number,
                $request->amount
            );
        } catch (GiftCardException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return response()->json(
            [
                'data' => $balance,
            ]
        );
    }

    public function bb()
    {
        return \response()->json(
            json_decode(
                '{
    "status": true,
    "status_code": "CC4001",
    "message": "Points Fetched Successfully.",
    "values": {
        "point_balance": 1908796,
        "tentative_points": 0,
        "first_name": "PUSHPA",
        "last_name": "KRISHNAMOORTHY VENKITTA RAMA IYER",
        "point_rate": 0.1,
        "min_points_allowed": 0,
        "max_points_allowed": 0
    }
}'
            )
        );
    }
}
