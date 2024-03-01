<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

class GemsApiService
{
    private string $secureKey;

    private string $cipherAlgo = 'AES-256-ECB';

    protected PendingRequest $httpClient;

    public function __construct(string $secureKey, string $login, string $password, $sandboxMode = false)
    {
        $this->secureKey = $secureKey;

        $apiUrl = $sandboxMode ? 'https://gemsapisimuat.clubclass.io' : 'https://gemsapisimp.clubclass.io';

        $this->httpClient = new PendingRequest();
        $this->httpClient->baseUrl(
            $apiUrl
        )
            ->withBasicAuth($login, $password)
            ->acceptJson()
            ->asJson()
            ->retry(3, 300)
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function sendMember($data)
    {
        $response = $this->httpClient->post('target/advplus/rest/V1/users/postbacktrn', $data);
        throw_unless(
            $response->ok() && $response->json('status'),
            'Unable to send GEMS member: '.PHP_EOL.$response->body().PHP_EOL.json_encode($data)
        );

        return $response->json();
    }

    public function updateMemberStatus($data)
    {
        $response = $this->httpClient->post('target/advplus/rest/V1/users/customer_adv_status', $data);

        throw_unless(
            $response->ok() && $response->json('status'),
            'Unable to update GEMS member status: '.PHP_EOL.$response->body().PHP_EOL.json_encode($data)
        );

        return $response->json();
    }

    public function getMemberPointBalance(string $membership_number): mixed
    {
        $response = $this->httpClient
            ->asForm()
            ->get(
                'target/GEMSCC/point/getUserPointBalance',
                ['membership_no' => $membership_number]
            );

        throw_if(!$response->ok() || !$response->json('status'), new RequestException($response));

        return $response->json();
    }

    public function spendMemberPoint(string $membership_number, float $points): mixed
    {
        $response = $this->httpClient
            ->asForm()
            ->post(
                '/target/GEMSCC/tp/burnPoints',
                [
                    'membership_no' => $membership_number,
                    'description' => 'Booking',
                    'debit_points' => $points,
                    'activity' => 'Advplus',
                ]
            );

        $response->throwIf(!$response->ok() || !$response->json('status'));

        return $response->json();
    }

    public function decryptString($encryptedString)
    {
        $decrypt = openssl_decrypt(
            base64_decode($encryptedString),
            $this->cipherAlgo,
            $this->secureKey,
            OPENSSL_RAW_DATA
        );

        if (!$decrypt) {
            report('Unable to decrypt string: '.$encryptedString.PHP_EOL.json_encode(request()->toArray()));
        }

        return $decrypt;
    }

    public function encryptString($string): string
    {
        return base64_encode(openssl_encrypt($string, $this->cipherAlgo, $this->secureKey, OPENSSL_RAW_DATA));
    }
}
