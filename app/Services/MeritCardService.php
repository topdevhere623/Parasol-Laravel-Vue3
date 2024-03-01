<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;

class MeritCardService
{
    public const SUCCESS_CODE = 200;

    private ?string $store_id;

    protected PendingRequest $httpClient;

    public function __construct($url, $secretKey, $storeId)
    {
        $this->store_id = $storeId;

        $http = new PendingRequest();

        $http->baseUrl($url.'/api/v1/currency/giftcards')
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(3, 50)
            ->withToken($secretKey)
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);

        $this->httpClient = $http;
    }

    public function getCard($cardNumber)
    {
        $result = $this->httpClient->get('balance_check', ['card_number' => $cardNumber])
            ->object();

        return $result->code == self::SUCCESS_CODE ? $result->data : null;
    }

    public function cardSpend($cardNumber, $amount, $email)
    {
        $data['giftcard'] = [
            'store_id' => $this->store_id,
            'card_number' => $cardNumber,
            'redemption_amount' => $amount,
            'email' => $email,
        ];

        $result = $this->httpClient->post('spend', $data)
            ->object();

        return $result->code == self::SUCCESS_CODE ? $result->data : false;
    }

    public function cardSpendWithCheck($cardNumber, $amount, $email)
    {
        $cardData = $this->getCard($cardNumber);

        if (optional($cardData)->remaining_value >= $amount) {
            $resultMeritData = $this->cardSpend(
                $cardNumber,
                $amount,
                $email
            );

            return $resultMeritData;
        }
    }
}
