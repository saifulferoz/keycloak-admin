<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Http;

use Feroz\KeycloakAdmin\Configuration;
use Feroz\KeycloakAdmin\Exception\Auth\AuthenticationException;
use Feroz\KeycloakAdmin\Exception\KeycloakApiException;
use Feroz\KeycloakAdmin\Exception\Resource\ConflictException;
use Feroz\KeycloakAdmin\Exception\Resource\ResourceNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\ValidationException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final readonly class Psr18HttpClient implements HttpClientInterface
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private LoggerInterface $logger;

    public function __construct(
        private Configuration $configuration,
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client ?? (class_exists(Psr18ClientDiscovery::class) ? Psr18ClientDiscovery::find() : new \GuzzleHttp\Client());
        $this->requestFactory = $requestFactory ?? (class_exists(Psr17FactoryDiscovery::class) ? Psr17FactoryDiscovery::findRequestFactory() : new \GuzzleHttp\Psr7\HttpFactory());
        $this->streamFactory = $streamFactory ?? (class_exists(Psr17FactoryDiscovery::class) ? Psr17FactoryDiscovery::findStreamFactory() : new \GuzzleHttp\Psr7\HttpFactory());
        $this->logger = $logger ?? new NullLogger();
    }

    public function get(string $path, array $queryParams = [], array $headers = []): ApiResponse
    {
        $uri = $this->buildUri($path, $queryParams);
        $request = $this->requestFactory->createRequest('GET', $uri);

        return $this->send($request, $headers);
    }

    public function post(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        $uri = $this->buildUri($path);
        $request = $this->requestFactory->createRequest('POST', $uri);

        if ($body !== null) {
            $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $stream = $this->streamFactory->createStream($json);
            $request = $request->withBody($stream);
            $headers['Content-Type'] = 'application/json';
        }

        return $this->send($request, $headers);
    }

    public function postForm(string $path, array $formParams, array $headers = []): ApiResponse
    {
        $uri = $this->buildUri($path);
        $request = $this->requestFactory->createRequest('POST', $uri);

        $bodyString = http_build_query($formParams, '', '&', PHP_QUERY_RFC3986);
        $stream = $this->streamFactory->createStream($bodyString);
        $request = $request->withBody($stream);
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        return $this->send($request, $headers);
    }

    public function put(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        $uri = $this->buildUri($path);
        $request = $this->requestFactory->createRequest('PUT', $uri);

        if ($body !== null) {
            $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $stream = $this->streamFactory->createStream($json);
            $request = $request->withBody($stream);
            $headers['Content-Type'] = 'application/json';
        }

        return $this->send($request, $headers);
    }

    public function delete(string $path, array $headers = []): ApiResponse
    {
        $uri = $this->buildUri($path);
        $request = $this->requestFactory->createRequest('DELETE', $uri);

        return $this->send($request, $headers);
    }

    /**
     * @param array<string, string> $headers
     */
    private function send(RequestInterface $request, array $headers): ApiResponse
    {
        $request = $request->withHeader('Accept', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $this->logger->debug('Sending Keycloak API request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
        ]);

        try {
            $response = $this->client->sendRequest($request);
        } catch (Throwable $e) {
            $this->logger->error('Keycloak API request network failure: ' . $e->getMessage(), [
                'uri' => (string) $request->getUri(),
                'exception' => $e,
            ]);
            throw new KeycloakApiException(
                sprintf('Keycloak connection failed for "%s %s": %s', $request->getMethod(), (string) $request->getUri(), $e->getMessage()),
                0,
                null,
                $e
            );
        }

        return $this->processResponse($request, $response);
    }

    private function processResponse(RequestInterface $request, ResponseInterface $response): ApiResponse
    {
        $statusCode = $response->getStatusCode();
        $rawBody = (string) $response->getBody();
        $data = null;

        if ($rawBody !== '') {
            try {
                $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                $data = $rawBody;
            }
        }

        $headers = $response->getHeaders();
        $apiResponse = new ApiResponse($statusCode, $data, $headers, $rawBody);

        if ($statusCode >= 200 && $statusCode < 300) {
            return $apiResponse;
        }

        // Handle Errors
        $errorMessage = $this->extractErrorMessage($data, $rawBody, $statusCode);

        if ($statusCode === 401) {
            throw new AuthenticationException($errorMessage, $statusCode, $data);
        }

        if ($statusCode === 404) {
            throw new ResourceNotFoundException('Resource', (string) $request->getUri(), $statusCode, $data);
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

    /**
     * @param array<string, mixed> $queryParams
     */
    private function buildUri(string $path, array $queryParams = []): string
    {
        $url = $this->configuration->getBaseUrl() . '/' . ltrim($path, '/');

        if (!empty($queryParams)) {
            $filteredParams = array_filter($queryParams, static fn($v) => $v !== null && $v !== '');
            if (!empty($filteredParams)) {
                $url .= '?' . http_build_query($filteredParams, '', '&', PHP_QUERY_RFC3986);
            }
        }

        return $url;
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
