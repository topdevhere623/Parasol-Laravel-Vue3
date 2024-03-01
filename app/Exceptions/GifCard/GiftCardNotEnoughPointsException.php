<?php

namespace App\Exceptions\GifCard;

class GiftCardNotEnoughPointsException extends GiftCardException
{
    protected $message = 'Not enough points for discount';
}
