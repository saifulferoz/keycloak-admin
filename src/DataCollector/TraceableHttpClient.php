<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\DataCollector;

use Feroz\KeycloakAdmin\Http\ApiResponse;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Throwable;

final class TraceableHttpClient implements HttpClientInterface
{
    /**
     * @var list<array{
     *     method: string,
     *     path: string,
     *     duration: float,
     *     status_code: int,
     *     request_body: mixed,
     *     response_body: mixed,
     *     error: ?string
     * }>
     */
    private array $traces = [];

    public function __construct(
        private readonly HttpClientInterface $decorated
    ) {
    }

    public function get(string $path, array $queryParams = [], array $headers = []): ApiResponse
    {
        return $this->trace('GET', $path, $queryParams, fn() => $this->decorated->get($path, $queryParams, $headers));
    }

    public function post(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        return $this->trace('POST', $path, $body, fn() => $this->decorated->post($path, $body, $headers));
    }

    public function postForm(string $path, array $formParams, array $headers = []): ApiResponse
    {
        return $this->trace('POST (Form)', $path, $formParams, fn() => $this->decorated->postForm($path, $formParams, $headers));
    }

    public function put(string $path, array|null $body = null, array $headers = []): ApiResponse
    {
        return $this->trace('PUT', $path, $body, fn() => $this->decorated->put($path, $body, $headers));
    }

    public function delete(string $path, array $headers = []): ApiResponse
    {
        return $this->trace('DELETE', $path, null, fn() => $this->decorated->delete($path, $headers));
    }

    /**
     * @return list<array{
     *     method: string,
     *     path: string,
     *     duration: float,
     *     status_code: int,
     *     request_body: mixed,
     *     response_body: mixed,
     *     error: ?string
     * }>
     */
    public function getTraces(): array
    {
        return $this->traces;
    }

    public function reset(): void
    {
        $this->traces = [];
    }

    /**
     * @param callable(): ApiResponse $caller
     */
    private function trace(string $method, string $path, mixed $requestData, callable $caller): ApiResponse
    {
        $start = microtime(true);
        $statusCode = 0;
        $responseBody = null;
        $error = null;

        try {
            /** @var ApiResponse $response */
            $response = $caller();
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getData();
            return $response;
        } catch (Throwable $e) {
            $statusCode = (int) $e->getCode();
            $error = $e->getMessage();
            throw $e;
        } finally {
            $duration = (microtime(true) - $start) * 1000;
            $this->traces[] = [
                'method' => $method,
                'path' => $path,
                'duration' => round($duration, 2),
                'status_code' => $statusCode,
                'request_body' => $requestData,
                'response_body' => $responseBody,
                'error' => $error,
            ];
        }
    }
}
