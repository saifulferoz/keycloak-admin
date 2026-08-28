<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth;

final readonly class Token
{
    public function __construct(
        private string $accessToken,
        private ?string $refreshToken,
        private int $expiresAt,
        private ?int $refreshExpiresAt = null,
        private ?string $tokenType = 'Bearer',
        private ?string $scope = null
    ) {
    }

    /**
     * @param array{
     *     access_token: string,
     *     refresh_token?: string,
     *     expires_in: int,
     *     refresh_expires_in?: int,
     *     token_type?: string,
     *     scope?: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $now = time();
        $expiresIn = (int) ($data['expires_in'] ?? 300);
        $refreshExpiresIn = isset($data['refresh_expires_in']) ? (int) $data['refresh_expires_in'] : null;

        return new self(
            accessToken: (string) $data['access_token'],
            refreshToken: isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            expiresAt: $now + $expiresIn,
            refreshExpiresAt: $refreshExpiresIn !== null ? $now + $refreshExpiresIn : null,
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
            scope: isset($data['scope']) ? (string) $data['scope'] : null
        );
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    public function getRefreshExpiresAt(): ?int
    {
        return $this->refreshExpiresAt;
    }

    public function getTokenType(): ?string
    {
        return $this->tokenType;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function isExpired(int $leewaySeconds = 30): bool
    {
        return time() + $leewaySeconds >= $this->expiresAt;
    }

    public function isRefreshExpired(int $leewaySeconds = 30): bool
    {
        if ($this->refreshExpiresAt === null) {
            return true;
        }

        return time() + $leewaySeconds >= $this->refreshExpiresAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->expiresAt,
            'refresh_expires_at' => $this->refreshExpiresAt,
            'token_type' => $this->tokenType,
            'scope' => $this->scope,
        ];
    }
}
