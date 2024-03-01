<?php

namespace App\Services\GiftCard;

interface GiftCardService
{
    public function getCardDetails(string $cardNumber): array;

    public function spendPoints(string $cardNumber, float $amount): void;

    public function getDiscountAmount(string $cardNumber, float $amount): float;
}
