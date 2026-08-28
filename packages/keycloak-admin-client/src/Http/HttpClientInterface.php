<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Http;

use Feroz\KeycloakAdmin\Exception\KeycloakApiException;

interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, string> $headers
     * @throws KeycloakApiException
     */
    public function get(string $path, array $queryParams = [], array $headers = []): ApiResponse;

    /**
     * @param array<string, mixed>|list<mixed>|null $body
     * @param array<string, string> $headers
     * @throws KeycloakApiException
     */
    public function post(string $path, array|null $body = null, array $headers = []): ApiResponse;

    /**
     * @param array<string, string> $formParams
     * @param array<string, string> $headers
     * @throws KeycloakApiException
     */
    public function postForm(string $path, array $formParams, array $headers = []): ApiResponse;

    /**
     * @param array<string, mixed>|list<mixed>|null $body
     * @param array<string, string> $headers
     * @throws KeycloakApiException
     */
    public function put(string $path, array|null $body = null, array $headers = []): ApiResponse;

    /**
     * @param array<string, string> $headers
     * @throws KeycloakApiException
     */
    public function delete(string $path, array $headers = []): ApiResponse;
}
