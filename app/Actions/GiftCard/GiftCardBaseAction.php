<?php

namespace App\Actions\GiftCard;

use App\Models\GiftCard;
use App\Services\GiftCard\GiftCardService;

class GiftCardBaseAction
{
    public function getGiftCardService(GiftCard|string $giftCard): GiftCardService
    {
        $giftCard = is_string($giftCard) ? GiftCard::where('uuid', $giftCard)->active()->firstOrFail() : $giftCard;

        /** @var GiftCardService $giftCardService */
        return app()->make(
            \Str::of($giftCard->code)
                ->camel()
                ->ucfirst()
                ->prepend('\App\Services\GiftCard\GiftCard')
                ->append('Service')
                ->toString()
        );
    }
}
