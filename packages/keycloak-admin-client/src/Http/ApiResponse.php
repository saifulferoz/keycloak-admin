<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Http;

final readonly class ApiResponse
{
    /**
     * @param array<string, list<string>> $headers
     * @param array<string, mixed>|list<mixed>|string|null $data
     */
    public function __construct(
        private int $statusCode,
        private array|string|null $data = null,
        private array $headers = [],
        private string $rawBody = ''
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * @return array<string, mixed>|list<mixed>|string|null
     */
    public function getData(): array|string|null
    {
        return $this->data;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        $normalized = strtolower($name);
        foreach ($this->headers as $key => $values) {
            if (strtolower($key) === $normalized && !empty($values)) {
                return $values[0];
            }
        }

        return null;
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * Extracts ID from Location header if available (e.g. POST /users returns 201 with Location: .../users/{id}).
     */
    public function getCreatedResourceId(): ?string
    {
        $location = $this->getHeader('Location');
        if ($location === null) {
            return null;
        }

        $parts = explode('/', rtrim($location, '/'));
        return end($parts) ?: null;
    }
}
