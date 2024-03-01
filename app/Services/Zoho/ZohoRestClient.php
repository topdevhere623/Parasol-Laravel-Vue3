<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoOAuth;
use Illuminate\Support\Facades\Cache;

class ZohoRestClient
{
    private bool $isAvailable;

    public function __construct(
        private readonly ZohoConnector $zohoConnector,
        private readonly string $organizationId,
        private readonly ZohoOAuthClient $zohoOAuthClient,
        private readonly string $baseUrl = 'https://books.zoho.com/api/v3'
    ) {
        $tokens = $this->getTokens();
        if ($tokens === null) {
            return;
        }

        $this->zohoConnector->addHeader('Authorization', ZohoConnector::OAUTH_HEADER_PREFIX.$tokens->getAccessToken());
    }

    private function getTokens(): ?ZohoOAuthTokens
    {
        /** @var ZohoOAuth|null $tokenModel */
        $tokenModel = ZohoOAuth::query()->first();

        if (!$this->isAvailable = $tokenModel != null) {
            return null;
        }

        $tokens = ZohoOAuthClient::getTokensFromModel($tokenModel);
        if (!$tokens->isValidAccessToken()) {
            $lock = Cache::lock('tokens_refresh', 16);

            if ($lock->get()) {
                $tokens = $this->refreshToken($tokens->getRefreshToken());
                $lock->release();
            } else {
                // Если блокировка не получена, то подождём 2 секунды, и попробуем снова получить токены
                sleep(2);
                return $this->getTokens();
            }
        }

        return $tokens;
    }

    private function refreshToken(string $refreshToken): ZohoOAuthTokens
    {
        return $this->zohoOAuthClient->refreshAccessToken($refreshToken);
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    private function getUrl($path)
    {
        if (!str_starts_with($path, '/')) {
            $path = '/'.$path;
        }
        return $this->baseUrl.$path;
    }

    private function setUrlAndParams(string $resourceName, array $params = []): void
    {
        $this->zohoConnector->setUrl($this->getUrl($resourceName));

        $this->zohoConnector->clearParams();
        foreach ($params as $key => $param) {
            $this->zohoConnector->addParam($key, $param);
        }
    }

    public function createRecord(string $resourceName, array $params, ?string $resourceId = null, ?string $subResourceName = null): array
    {
        $uri = $resourceName;

        if (!empty($resourceId)) {
            $uri .= "/{$resourceId}";
        }

        if (!empty($subResourceName)) {
            $uri .= "/{$subResourceName}";
        }

        $this->setUrlAndParams($uri, $params);

        $this->zohoConnector->addParam('organization_id', $this->organizationId);

        return $this->zohoConnector->post()->json();
    }

    public function updateRecord(string $resourceName, array $params, ?string $resourceId = null, ?string $subResourceName = null): array
    {
        $uri = $resourceName;

        if (!empty($resourceId)) {
            $uri .= "/{$resourceId}";
        }

        if (!empty($subResourceName)) {
            $uri .= "/{$subResourceName}";
        }

        $this->setUrlAndParams($uri, $params);

        $this->zohoConnector->addParam('organization_id', $this->organizationId);

        return $this->zohoConnector->put()->json();
    }

    public function getList(string $resourceName, array $params): array
    {
        $this->setUrlAndParams($resourceName, $params);

        $this->zohoConnector->addParam('organization_id', $this->organizationId);

        return $this->zohoConnector->get()->json();
    }

    public function getRecord(string $resourceName, string $id, ?string $subResource = null): array
    {
        $uri = "{$resourceName}/{$id}";

        if (!empty($subResource)) {
            $uri .= "/{$subResource}";
        }

        $this->setUrlAndParams($uri);

        $this->zohoConnector->addParam('organization_id', $this->organizationId);

        return $this->zohoConnector->get()->json();
    }
}
