<?php

namespace App\Exceptions\GifCard;

class GiftCardUnableToSpendPointsException extends GiftCardException
{
    protected $message = 'Card code not found';
}
