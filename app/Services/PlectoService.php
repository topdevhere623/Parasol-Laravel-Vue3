<?php

namespace App\Services;

use App\Models\BackofficeUser;
use App\Models\Booking;
use App\Models\Member\MembershipRenewal;
use App\Models\Program;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Symfony\Component\HttpFoundation\Response;

class PlectoService
{
    protected PendingRequest $httpClient;

    protected bool $isAvailable;

    public const DATA_SOURCES = [
        Program::class => 'b32ed931b43e46f294637293ee602da1',
        Booking::class => '45ff522fb5f04235b9d305322da4b81f',
        BackofficeUser::class => '7b075f22f11f49b5ae1eec0a834a72ab',
        MembershipRenewal::class => 'da6be7d1995f4872ba2c707dfdc6003f',
    ];

    public function __construct(?string $username, ?string $password)
    {
        if (!$this->isAvailable = $username && $password) {
            return;
        }

        $this->httpClient = new PendingRequest();
        $this->httpClient->baseUrl(
            'https://app.plecto.com/api/v2'
        )
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($username, $password)
            ->retry(3, 5000, function ($exception) {
                $code = $exception->getCode();
                return $exception instanceof RequestException && ($code == Response::HTTP_REQUEST_TIMEOUT || $code >= 500);
            })
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function pushData($data = [])
    {
        if (!$this->isAvailable()) {
            return;
        }
        $this->httpClient->post('registrations/', $data);
    }

}
