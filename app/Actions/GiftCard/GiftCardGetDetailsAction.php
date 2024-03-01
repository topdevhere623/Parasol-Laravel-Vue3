<?php

namespace App\Actions\GiftCard;

use App\Models\GiftCard;

class GiftCardGetDetailsAction extends GiftCardBaseAction
{
    public function handle(GiftCard|string $giftCard, string $cardNumber)
    {
        $giftCardService = $this->getGiftCardService($giftCard);
        return collect($giftCardService->getCardDetails($cardNumber))->only('balance', 'min', 'max');
    }
}
