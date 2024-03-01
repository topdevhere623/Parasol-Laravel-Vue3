<?php

namespace App\Exceptions\GifCard;

class GiftCardNotFoundException extends GiftCardException
{
    protected $message = 'Card code not found';
}
