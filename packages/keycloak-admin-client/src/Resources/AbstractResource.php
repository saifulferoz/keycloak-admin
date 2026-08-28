<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Exception\Auth\AuthenticationException;
use Feroz\KeycloakAdmin\Http\ApiResponse;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;

abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClientInterface $httpClient,
        protected readonly TokenManagerInterface $tokenManager
    ) {
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, string> $headers
     */
    protected function sendGet(string $path, array $queryParams = [], array $headers = []): ApiResponse
    {
        return $this->executeWithAuth(
            fn(array $authHeaders) => $this->httpClient->get($path, $queryParams, array_merge($authHeaders, $headers))
        );
    }

    /**
     * @param array<string, mixed>|list<mixed>|null $body
     * @param array<string, string> $headers
     */
    protected function sendPost(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        return $this->executeWithAuth(
            fn(array $authHeaders) => $this->httpClient->post($path, $body, array_merge($authHeaders, $headers))
        );
    }

    /**
     * @param array<string, mixed>|list<mixed>|null $body
     * @param array<string, string> $headers
     */
    protected function sendPut(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        return $this->executeWithAuth(
            fn(array $authHeaders) => $this->httpClient->put($path, $body, array_merge($authHeaders, $headers))
        );
    }

    /**
     * @param array<string, string> $headers
     */
    protected function sendDelete(string $path, array $headers = []): ApiResponse
    {
        return $this->executeWithAuth(
            fn(array $authHeaders) => $this->httpClient->delete($path, array_merge($authHeaders, $headers))
        );
    }

    /**
     * @param callable(array<string, string>): ApiResponse $callback
     */
    private function executeWithAuth(callable $callback): ApiResponse
    {
        $token = $this->tokenManager->getAccessToken();
        $authHeaders = ['Authorization' => 'Bearer ' . $token];

        try {
            return $callback($authHeaders);
        } catch (AuthenticationException $e) {
            // Token might have been revoked on the server; invalidate and retry once
            $this->tokenManager->invalidateToken();
            $newToken = $this->tokenManager->getAccessToken();
            $newAuthHeaders = ['Authorization' => 'Bearer ' . $newToken];

            return $callback($newAuthHeaders);
        }
    }
}
