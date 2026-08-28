<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth\Storage;

use Feroz\KeycloakAdmin\Auth\Token;
use Psr\SimpleCache\CacheInterface;

final readonly class PsrSimpleCacheTokenStorage implements TokenStorageInterface
{
    public function __construct(
        private CacheInterface $cache,
        private string $prefix = 'keycloak_token_'
    ) {
    }

    public function get(string $key): ?Token
    {
        /** @var array<string, mixed>|null $data */
        $data = $this->cache->get($this->prefix . $key);

        if ($data === null || !is_array($data)) {
            return null;
        }

        return new Token(
            accessToken: (string) $data['access_token'],
            refreshToken: isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            expiresAt: (int) $data['expires_at'],
            refreshExpiresAt: isset($data['refresh_expires_at']) ? (int) $data['refresh_expires_at'] : null,
            tokenType: isset($data['token_type']) ? (string) $data['token_type'] : 'Bearer',
            scope: isset($data['scope']) ? (string) $data['scope'] : null
        );
    }

    public function set(string $key, Token $token): void
    {
        $ttl = max(0, $token->getExpiresAt() - time());
        $this->cache->set($this->prefix . $key, $token->toArray(), $ttl);
    }

    public function delete(string $key): void
    {
        $this->cache->delete($this->prefix . $key);
    }
}
