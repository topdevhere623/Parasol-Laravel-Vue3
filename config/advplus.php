<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AdvPlus Application
    |--------------------------------------------------------------------------
    |
    */

    'default_currency' => env('CURRENCY', 'AED'),
    'default_autocomplete_response_limit' => 20,
    'default_offer_response_limit' => 15,
    'default_club_response_limit' => 15,
    'default_referrals_response_limit' => 15,
    'default_payments_response_limit' => 10,

    'currencies' => ['AED'],

    'test' => [
        'program_login' => env('TEST_PROGRAM_LOGIN', 'entertainer@advplus.ae'),
        'program_password' => env('TEST_PROGRAM_PASSWORD', '111'),
    ],

    'member_reminder_complete_notify_after' => env('MEMBER_REMINDER_COMPLETE_NOTIFY_AFTER', 60),
    'hsbc_member_reminder_complete_notify_after' => env('HSBC_MEMBER_REMINDER_COMPLETE_NOTIFY_AFTER', 30),

    'autocomplete' => [
        'corporate' => 15,
    ],
];
