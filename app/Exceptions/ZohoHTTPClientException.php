<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Throwable;

class ZohoHTTPClientException extends Exception
{
    public const RESPONSECODE_NO_CONTENT = 204;

    public const RESPONSECODE_MOVED_PERMANENTLY = 301;

    public const RESPONSECODE_MOVED_TEMPORARILY = 302;

    public const RESPONSECODE_NOT_MODIFIED = 304;

    public const RESPONSECODE_BAD_REQUEST = 400;

    public const RESPONSECODE_AUTHORIZATION_ERROR = 401;

    public const RESPONSECODE_FORBIDDEN = 403;

    public const RESPONSECODE_NOT_FOUND = 404;

    public const RESPONSECODE_METHOD_NOT_ALLOWED = 405;

    public const RESPONSECODE_REQUEST_ENTITY_TOO_LARGE = 413;

    public const RESPONSECODE_UNSUPPORTED_MEDIA_TYPE = 415;

    public const RESPONSECODE_TOO_MANY_REQUEST = 429;

    public const RESPONSECODE_INTERNAL_SERVER_ERROR = 500;

    private array $body;
    private array $headers;
    private string $method;
    private array $requestData;

    public function __construct(
        Response $response,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $requestData = []
    ) {
        parent::__construct($message, $code, $previous);

        $this->setHeaders($response->headers());
        $this->setBody($response->json());
        $this->setMethod((string) $response->effectiveUri());
        $this->setRequestData($requestData);
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(
        string $method
    ): void {
        $this->method = $method;
    }

    public function context(): array
    {
        return [
            'http_code' => $this->getCode(),
            'method' => $this->getMethod(),
            'body' => $this->getBody(),
            'request_data' => $this->getRequestData(),
            'headers' => $this->getHeaders(),
        ];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function setRequestData(array $requestData): void
    {
        $this->requestData = $requestData;
    }

    public static function getFaultyResponseCodes(): array
    {
        return [
            self::RESPONSECODE_NO_CONTENT,
            self::RESPONSECODE_NOT_MODIFIED,
            self::RESPONSECODE_NOT_FOUND,
            self::RESPONSECODE_AUTHORIZATION_ERROR,
            self::RESPONSECODE_BAD_REQUEST,
            self::RESPONSECODE_FORBIDDEN,
            self::RESPONSECODE_INTERNAL_SERVER_ERROR,
            self::RESPONSECODE_METHOD_NOT_ALLOWED,
            self::RESPONSECODE_MOVED_PERMANENTLY,
            self::RESPONSECODE_MOVED_TEMPORARILY,
            self::RESPONSECODE_REQUEST_ENTITY_TOO_LARGE,
            self::RESPONSECODE_TOO_MANY_REQUEST,
            self::RESPONSECODE_UNSUPPORTED_MEDIA_TYPE,
        ];
    }
}
