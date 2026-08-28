<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Http;

use Feroz\KeycloakAdmin\Configuration;
use Feroz\KeycloakAdmin\Exception\Auth\AuthenticationException;
use Feroz\KeycloakAdmin\Exception\KeycloakApiException;
use Feroz\KeycloakAdmin\Exception\Resource\ConflictException;
use Feroz\KeycloakAdmin\Exception\Resource\ResourceNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\ValidationException;
use Feroz\KeycloakAdmin\Http\ApiResponse;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyContractsHttpClient;
use Throwable;

final readonly class SymfonyHttpClient implements HttpClientInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private Configuration $configuration,
        private SymfonyContractsHttpClient $client,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function get(string $path, array $queryParams = [], array $headers = []): ApiResponse
    {
        return $this->request('GET', $path, [
            'query' => array_filter($queryParams, static fn($v) => $v !== null && $v !== ''),
            'headers' => $headers,
        ]);
    }

    public function post(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        $options = ['headers' => $headers];
        if ($body !== null) {
            $options['json'] = $body;
        }

        return $this->request('POST', $path, $options);
    }

    public function postForm(string $path, array $formParams, array $headers = []): ApiResponse
    {
        return $this->request('POST', $path, [
            'body' => $formParams,
            'headers' => $headers,
        ]);
    }

    public function put(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        $options = ['headers' => $headers];
        if ($body !== null) {
            $options['json'] = $body;
        }

        return $this->request('PUT', $path, $options);
    }

    public function delete(string $path, array $headers = []): ApiResponse
    {
        return $this->request('DELETE', $path, [
            'headers' => $headers,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $path, array $options): ApiResponse
    {
        $url = $this->configuration->getBaseUrl() . '/' . ltrim($path, '/');

        $headers = $options['headers'] ?? [];
        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;
        $options['timeout'] = $this->configuration->getTimeout();
        $options['verify_peer'] = $this->configuration->isVerifySsl();
        $options['verify_host'] = $this->configuration->isVerifySsl();

        $this->logger->debug('Sending Keycloak API request via Symfony HttpClient', [
            'method' => $method,
            'url' => $url,
        ]);

        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $rawBody = $response->getContent(false);
            $responseHeaders = $response->getHeaders(false);
        } catch (Throwable $e) {
            $this->logger->error('Keycloak API request network failure: ' . $e->getMessage(), [
                'url' => $url,
                'exception' => $e,
            ]);
            throw new KeycloakApiException(
                sprintf('Keycloak connection failed for "%s %s": %s', $method, $url, $e->getMessage()),
                0,
                null,
                $e
            );
        }

        $data = null;
        if ($rawBody !== '') {
            try {
                $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                $data = $rawBody;
            }
        }

        $apiResponse = new ApiResponse($statusCode, $data, $responseHeaders, $rawBody);

        if ($statusCode >= 200 && $statusCode < 300) {
            return $apiResponse;
        }

        $errorMessage = $this->extractErrorMessage($data, $rawBody, $statusCode);

        if ($statusCode === 401) {
            throw new AuthenticationException($errorMessage, $statusCode, $data);
        }

        if ($statusCode === 404) {
            throw new ResourceNotFoundException('Resource', $url, $statusCode, $data);
        }

        if ($statusCode === 409) {
            throw new ConflictException($errorMessage, $statusCode, $data);
        }

        if ($statusCode === 400) {
            throw new ValidationException($errorMessage, $statusCode, $data);
        }

        throw new KeycloakApiException(
            sprintf('Keycloak API returned HTTP %d: %s', $statusCode, $errorMessage),
            $statusCode,
            $data
        );
    }

    private function extractErrorMessage(mixed $data, string $rawBody, int $statusCode): string
    {
        if (is_array($data)) {
            if (isset($data['errorMessage'])) {
                return (string) $data['errorMessage'];
            }
            if (isset($data['error_description'])) {
                return (string) $data['error_description'];
            }
            if (isset($data['error'])) {
                return (string) $data['error'];
            }
        }

        if ($rawBody !== '') {
            return substr($rawBody, 0, 300);
        }

        return sprintf('HTTP Error %d', $statusCode);
    }
}
