<?php

return [
    'api_url' => 'https://www.zohoapis.com/books/v3',
    'client_secret' => env('ZOHO_CLIENT_SECRET'),
    'client_id' => env('ZOHO_CLIENT_ID'),
    'redirect_uri' => env('APP_URL').'/zoho/oauth2callback',
    'scopes' => [
        'ZohoBooks.contacts.READ',
        'ZohoBooks.contacts.UPDATE',
        'ZohoBooks.contacts.Create',
        'ZohoBooks.invoices.READ',
        'ZohoBooks.invoices.Create',
        'ZohoBooks.accountants.READ',
        'ZohoBooks.settings.READ',
        'ZohoBooks.customerpayments.CREATE',
    ],
];
