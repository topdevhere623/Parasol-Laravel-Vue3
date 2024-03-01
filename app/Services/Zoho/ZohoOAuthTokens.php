<?php

namespace App\Services\Zoho;

use Exception;

class ZohoOAuthTokens
{
    private string $refreshToken = '';

    private string $accessToken = '';

    private float $expiryTime = 0;

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getAccessToken(): string
    {
        if ($this->isValidAccessToken()) {
            return $this->accessToken;
        }
        throw new Exception('Access token is expired');
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getExpiryTime(): float
    {
        return $this->expiryTime;
    }

    public function setExpiryTime(float $expiryTime): float
    {
        return $this->expiryTime = $expiryTime;
    }

    public function isValidAccessToken(): bool
    {
        return ($this->getExpiryTime() - $this->getCurrentTimeInMillis()) > 1000;
    }

    public function getCurrentTimeInMillis(): float
    {
        return round(microtime(true) * 1000);
    }
}
