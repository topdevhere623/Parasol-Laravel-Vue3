<?php

namespace App\Services;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Symfony\Component\HttpFoundation\Response;

class PasskitService
{
    protected $httpClient;
    private ?string $api_url;
    private ?string $auth_key;
    private ?string $auth_secret;

    public function __construct(?string $api_url, ?string $auth_key, ?string $auth_secret)
    {
        $http = new PendingRequest();

        $this->api_url = $api_url;
        $this->auth_key = $auth_key;
        $this->auth_secret = $auth_secret;

        $http->baseUrl($this->api_url)
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(3, 5000, function ($exception) {
                return $exception instanceof RequestException && $exception->getCode(
                ) == Response::HTTP_REQUEST_TIMEOUT;
            })
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);

        $this->httpClient = $http;
    }

    public function isAvailable()
    {
        return $this->auth_key && $this->auth_secret;
    }

    public function getHttpClient(): PendingRequest
    {
        return $this->httpClient->withToken(
            JWT::encode(
                [
                    'uid' => $this->auth_key,
                    'iat' => time(),
                    'exp' => time() + 600,
                ],
                $this->auth_secret,
                'HS256'
            ),
            ''
        );
    }

    protected function prepareMemberRequestData(array $member): array
    {
        $data = [
            'tierId' => 'bronze',
            'programId' => $member['passkit_program_id'],
            'externalId' => $member['member_id'],
            'expiryDate' => Carbon::parse($member['expiry_date'])->endOfDay()->toIso8601String(),
            'person' => [
                'forename' => $member['first_name'],
                'surname' => $member['last_name'],
                'displayName' => $member['first_name'].' '.$member['last_name'],
                'gender' => 'NOT_KNOWN',
                'emailAddress' => $member['email'],
            ],
            'metaData' => [
                'memberClubList' => $member['clubs'],
                'liveClubUpdates' => $member['liveClubs'],
                'membershipType' => $member['membershipType'],
                'children' => $member['kids'],
            ],
        ];

        if (!empty($member['referralCode'])) {
            $data['metaData']['referralCode'] = $member['referralCode'];
        }

        if (!empty($member['avatar'])) {
            $data['profileImage'] = $member['avatar'];
        }

        return $data;
    }

    public function createMember(array $member)
    {
        $response = $this->getHttpClient()->post(
            'members/member',
            $this->prepareMemberRequestData($member)
        );
        return $response->ok() ? $response->json('id') : $response->throw();
    }

    public function updateMember(array $member)
    {
        $response = $this->getHttpClient()->put(
            'members/member',
            $this->prepareMemberRequestData($member)
        );
        return $response->ok() ? $response->json('id') : $response->throw();
    }

    public function deleteMember(string $passkitId)
    {
        try {
            $this->getHttpClient()->delete(
                'members/member',
                [
                    'id' => $passkitId,
                ]
            );

            return true;
        } catch (RequestException $e) {
            return false;
        }
    }

    public function getMember(string $passKitId)
    {
        return $this->getHttpClient()->get('members/member/id/'.$passKitId)->json();
    }

    public function getProgramMembers(string $passKitId)
    {
        $items = array_map(
            'json_decode',
            explode(
                PHP_EOL,
                $this->getHttpClient()->get('members/member/list/'.$passKitId, ['pagination.limit' => -1])
                    ->body()
            )
        );
        return array_slice($items, 0, count($items) - 1);
    }
}
