<?php

namespace App\Exceptions\Payments;

use Exception;

class MakePaymentException extends Exception
{
    protected const ERRORS = [
        self::DEFAULT_ERROR => 'Something went wrong. Please try again.',
        self::DEFAULT_CARD_ERROR => 'It looks like your card has been blocked.',
        self::CARD_TOKEN_IS_REQUIRED => 'Card token is required.',
        self::HSBC_NOT_ALLOWED_CARD => 'It looks like you have not used your HSBC card to check out.',
        self::HSBC_ALREADY_USED_FREE_CARD => 'It seems that you have already availed of a complimentary membership using this card. Please select a different HSBC ENTERTAINER soleil plan to continue.',
        self::HSBC_ALREADY_USED_CARD => 'It looks like you have already used this card to register for membership.',
        self::HSBC_USED_FREE_CARD_FOR_PAID_PLAN => 'It looks like you have not used your complimentary HSBC card to check out.',

    ];

    public const DEFAULT_ERROR = 0;
    public const DEFAULT_CARD_ERROR = 1;

    public const CARD_TOKEN_IS_REQUIRED = 2;

    public const HSBC_NOT_ALLOWED_CARD = 4;
    public const HSBC_ALREADY_USED_FREE_CARD = 5;
    public const HSBC_ALREADY_USED_CARD = 6;
    public const HSBC_USED_FREE_CARD_FOR_PAID_PLAN = 7;

    public function __construct($code = 0, $previous = null)
    {
        parent::__construct(self::getMessageByCode($code), $code, $previous);
    }

    public static function getMessageByCode($code): string
    {
        if (key_exists((int)$code, self::ERRORS)) {
            return self::ERRORS[(int)$code];
        }
        return 'Error';
    }

    public static function getDefaultErrorMessage(): string
    {
        return self::ERRORS[self::DEFAULT_ERROR];
    }
}
