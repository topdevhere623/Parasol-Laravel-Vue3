<?php

namespace App\Actions\GiftCard;

use App\Models\GiftCard;

class GiftCardSpendPointsAction extends GiftCardBaseAction
{
    public function handle(GiftCard|string $giftCard, string $cardNumber, float $amount)
    {
        $giftCardService = $this->getGiftCardService($giftCard);
        $giftCardService->spendPoints($cardNumber, $amount);
    }
}
