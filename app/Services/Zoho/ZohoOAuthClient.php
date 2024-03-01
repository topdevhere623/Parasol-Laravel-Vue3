<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoOAuth;
use Exception;

class ZohoOAuthClient
{
    public function __construct(
        private readonly string $clientSecret,
        private readonly string $clientId,
        private readonly string $redirectUri
    ) {

    }

    protected function getZohoConnector($url): ZohoConnector
    {
        $zohoConnector = new ZohoConnector();
        $zohoConnector->setUrl($url);
        $zohoConnector->addQueryParam(ZohoConnector::CLIENT_ID, $this->getClientId());
        $zohoConnector->addQueryParam(ZohoConnector::CLIENT_SECRET, $this->getClientSecret());
        $zohoConnector->addQueryParam(ZohoConnector::REDIRECT_URL, $this->getRedirectUri());

        return $zohoConnector;
    }

    public function generateAccessToken(string $grantCode): ZohoOAuthTokens
    {
        $connector = self::getZohoConnector(ZohoConnector::getTokenURL());
        $connector->addQueryParam(ZohoConnector::GRANT_TYPE, ZohoConnector::GRANT_TYPE_AUTH_CODE);
        $connector->addQueryParam(ZohoConnector::CODE, $grantCode);

        $response = $connector->post();
        $responseJson = $response->json();

        if (array_key_exists(ZohoConnector::ACCESS_TOKEN, $responseJson)) {
            $tokens = self::getTokensFromJSON($responseJson);
            $this->createOrUpdateTokens($tokens);

            return $tokens;
        }

        throw new Exception('Exception while fetching access token from grant token - '.$response->body());
    }

    public function refreshAccessToken(string $refreshToken): ZohoOAuthTokens
    {
        $connector = self::getZohoConnector(ZohoConnector::getRefreshTokenURL());
        $connector->addQueryParam(ZohoConnector::GRANT_TYPE, ZohoConnector::GRANT_TYPE_REFRESH);
        $connector->addQueryParam(ZohoConnector::REFRESH_TOKEN, $refreshToken);

        $response = $connector->post();
        $responseJson = $response->json();

        if (array_key_exists(ZohoConnector::ACCESS_TOKEN, $responseJson)) {
            $tokens = self::getTokensFromJSON($responseJson);
            $tokens->setRefreshToken($refreshToken);

            $this->createOrUpdateTokens($tokens);

            return $tokens;
        }

        throw new Exception('Exception while fetching access token from refresh token - '.$response->body());
    }

    public function getTokens(): ZohoOAuthTokens
    {
        $tokenModel = ZohoOAuth::first();

        return self::getTokensFromModel($tokenModel);
    }

    private function createOrUpdateTokens(ZohoOAuthTokens $tokens)
    {
        $tokenModel = ZohoOAuth::first();

        if ($tokenModel === null) {
            $tokenModel = new ZohoOAuth();
        }

        $tokenModel->access_token = $tokens->getAccessToken();
        $tokenModel->refresh_token = $tokens->getRefreshToken();
        $tokenModel->expires = $tokens->getExpiryTime();
        $tokenModel->save();
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public static function getTokensFromJSON(array $response): ZohoOAuthTokens
    {
        $oAuthTokens = new ZohoOAuthTokens();

        $expiresIn = $response[ZohoConnector::EXPIRES_IN];
        if (!array_key_exists(ZohoConnector::EXPIRES_IN_SEC, $response)) {
            $expiresIn = $expiresIn * 1000;
        }

        $oAuthTokens->setExpiryTime($oAuthTokens->getCurrentTimeInMillis() + $expiresIn);
        $accessToken = $response[ZohoConnector::ACCESS_TOKEN];
        $oAuthTokens->setAccessToken($accessToken);

        if (array_key_exists(ZohoConnector::REFRESH_TOKEN, $response)) {
            $refreshToken = $response[ZohoConnector::REFRESH_TOKEN];
            $oAuthTokens->setRefreshToken($refreshToken);
        }

        return $oAuthTokens;
    }

    public static function getTokensFromModel(ZohoOAuth $tokenModel): ZohoOAuthTokens
    {
        $oAuthTokens = new ZohoOAuthTokens();
        $oAuthTokens->setExpiryTime($tokenModel->expires);
        $oAuthTokens->setAccessToken($tokenModel->access_token);
        $oAuthTokens->setRefreshToken($tokenModel->refresh_token);

        return $oAuthTokens;
    }
}
