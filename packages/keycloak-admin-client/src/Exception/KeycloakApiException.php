<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when the Keycloak REST API returns an HTTP error response.
 */
class KeycloakApiException extends RuntimeException implements KeycloakException
{
    /**
     * @param array<string, mixed>|string|null $responseBody
     */
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly array|string|null $responseBody = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|string|null
     */
    public function getResponseBody(): array|string|null
    {
        return $this->responseBody;
    }
}
