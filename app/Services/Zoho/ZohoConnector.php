<?php

namespace App\Services\Zoho;

use App\Exceptions\ZohoHTTPClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class ZohoConnector
{
    public const ACCOUNTS_URL = 'https://accounts.zoho.com';

    public const STATE = 'state';

    public const CLIENT_ID = 'client_id';

    public const CLIENT_SECRET = 'client_secret';

    public const REDIRECT_URL = 'redirect_uri';

    public const GRANT_TYPE = 'grant_type';

    public const GRANT_TYPE_AUTH_CODE = 'authorization_code';

    public const GRANT_TYPE_REFRESH = 'refresh_token';

    public const CODE = 'code';

    public const ACCESS_TOKEN = 'access_token';

    public const REFRESH_TOKEN = 'refresh_token';

    public const EXPIRES_IN = 'expires_in';

    public const EXPIRES_IN_SEC = 'expires_in_sec';

    public const OAUTH_HEADER_PREFIX = 'Zoho-oauthtoken ';

    private string $url;

    private array $requestParams = [];

    private array $requestQueryParams = [];

    protected PendingRequest $httpClient;

    public function __construct()
    {
        $this->httpClient = new PendingRequest();

        $this->httpClient
            ->retry(3, 5000, function ($exception) {
                $code = $exception->getCode();
                return $exception instanceof RequestException && ($code == \Symfony\Component\HttpFoundation\Response::HTTP_REQUEST_TIMEOUT || $code >= 500);
            })
            ->throwIf(
                fn (Response $response) => in_array(
                    $response->status(),
                    ZohoHTTPClientException::getFaultyResponseCodes()
                )
            )
            ->throw(function (Response $response, $e) {
                throw new ZohoHTTPClientException(response: $response, message: $response->json('message', $response->body()), code: $response->status(), previous: $e, requestData: array_merge($this->requestParams, $this->requestQueryParams));
            });
    }

    public function put(): Response
    {
        return $this->httpClient->put(self::getUrl(), $this->requestParams);
    }

    public function post(): Response
    {
        $url = self::getUrl();
        if (!empty($this->requestQueryParams)) {
            $url .= '?'.http_build_query($this->requestQueryParams);
        }

        return $this->httpClient->asMultipart()->post($url, $this->requestParams);
    }

    public function get(): Response
    {
        return $this->httpClient->asMultipart()->get(self::getUrl(), array_unique(array_merge($this->requestQueryParams, $this->requestParams)));
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function addParam($key, $value)
    {
        return tap($this, function () use ($key, $value) {
            $this->requestParams[$key] = $value;
        });
    }

    public function addQueryParam($key, $value)
    {
        return tap($this, function () use ($key, $value) {
            $this->requestQueryParams[$key] = $value;
        });
    }

    public function addHeader($key, $value): void
    {
        $this->httpClient->withHeaders([$key => $value]);
    }

    public static function getTokenUrl(): string
    {
        return self::ACCOUNTS_URL.'/oauth/v2/token';
    }

    public static function getRefreshTokenUrl(): string
    {
        return self::ACCOUNTS_URL.'/oauth/v2/token';
    }

    public function clearParams(): void
    {
        $this->requestParams = [];
    }
}
