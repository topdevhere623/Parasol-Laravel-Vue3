<?php

namespace App\Actions\GiftCard;

use App\Models\GiftCard;

class GiftCardGetDiscountAction extends GiftCardBaseAction
{
    public function handle(GiftCard|string $giftCard, string $cardNumber, float $amount): float
    {
        $giftCardService = $this->getGiftCardService($giftCard);
        return $giftCardService->getDiscountAmount($cardNumber, $amount);
    }
}
