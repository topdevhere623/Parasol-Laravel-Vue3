<?php

namespace App\Services\GiftCard;

use App\Exceptions\GifCard\GiftCardNotEnoughPointsException;
use App\Exceptions\GifCard\GiftCardNotFoundException;
use App\Exceptions\GifCard\GiftCardUnableToSpendPointsException;
use App\Services\GemsApiService;

class GiftCardGemsPointsService implements GiftCardService
{
    protected GemsApiService $gemsApiService;

    public function __construct()
    {
        $this->gemsApiService = app(GemsApiService::class);
    }

    /**
     * @throws GiftCardNotFoundException
     */
    public function getCardDetails(string $cardNumber): array
    {
        try {
            $response = $this->gemsApiService->getMemberPointBalance($cardNumber);
        } catch (\Throwable $exception) {
            report($exception);
            throw new GiftCardNotFoundException();
        }

        $values = $response['values'];
        return [
            'balance' => $values['point_balance'] ?? 0,
            'min' => $values['min_points_allowed'] ?? 0,
            'max' => !!$values['max_points_allowed'] ? $values['max_points_allowed'] : $values['point_balance'],
            'point_rate' => $values['point_rate'] ?? 0,
        ];
    }

    /**
     * @throws GiftCardUnableToSpendPointsException|\Throwable
     */
    public function spendPoints(string $cardNumber, float $amount): void
    {
        $response = $this->gemsApiService->spendMemberPoint($cardNumber, $amount);
        throw_unless($response, GiftCardUnableToSpendPointsException::class);
    }

    /**
     * @throws GiftCardNotEnoughPointsException
     * @throws GiftCardNotFoundException
     */
    public function getDiscountAmount(string $cardNumber, float $amount): float
    {
        $balance = $this->getCardDetails($cardNumber);

        throw_if($amount > $balance['balance'], GiftCardNotEnoughPointsException::class);
        return booking_amount_round($amount * $balance['point_rate']);
    }
}
