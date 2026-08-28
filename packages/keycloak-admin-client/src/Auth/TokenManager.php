<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth;

use Feroz\KeycloakAdmin\Auth\Storage\InMemoryTokenStorage;
use Feroz\KeycloakAdmin\Auth\Storage\TokenStorageInterface;
use Feroz\KeycloakAdmin\Configuration;
use Feroz\KeycloakAdmin\Exception\Auth\AuthenticationException;
use Feroz\KeycloakAdmin\Exception\Auth\InvalidCredentialsException;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class TokenManager implements TokenManagerInterface
{
    private TokenStorageInterface $storage;
    private LoggerInterface $logger;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly HttpClientInterface $httpClient,
        ?TokenStorageInterface $storage = null,
        ?LoggerInterface $logger = null
    ) {
        $this->storage = $storage ?? new InMemoryTokenStorage();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getAccessToken(): string
    {
        $cacheKey = $this->getCacheKey();
        $token = $this->storage->get($cacheKey);

        if ($token !== null && !$token->isExpired()) {
            return $token->getAccessToken();
        }

        // Attempt refresh token if available and not expired
        if ($token !== null && $token->getRefreshToken() !== null && !$token->isRefreshExpired()) {
            try {
                $refreshedToken = $this->refreshAccessToken($token->getRefreshToken());
                $this->storage->set($cacheKey, $refreshedToken);
                return $refreshedToken->getAccessToken();
            } catch (Throwable $e) {
                $this->logger->warning('Failed to refresh Keycloak token, falling back to full authentication: ' . $e->getMessage());
                $this->storage->delete($cacheKey);
            }
        }

        // Full authentication
        $newToken = $this->authenticate();
        $this->storage->set($cacheKey, $newToken);

        return $newToken->getAccessToken();
    }

    public function invalidateToken(): void
    {
        $this->storage->delete($this->getCacheKey());
    }

    private function authenticate(): Token
    {
        $path = sprintf('/realms/%s/protocol/openid-connect/token', rawurlencode($this->configuration->getAuthRealm()));
        $params = $this->configuration->getCredentials()->toFormParams();

        try {
            $response = $this->httpClient->postForm($path, $params);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains(strtolower($msg), 'invalid_grant') || str_contains(strtolower($msg), 'unauthorized') || str_contains(strtolower($msg), 'invalid_client')) {
                throw new InvalidCredentialsException('Authentication failed: ' . $msg, (int) $e->getCode(), null, $e);
            }
            throw new AuthenticationException('Could not authenticate with Keycloak: ' . $msg, (int) $e->getCode(), null, $e);
        }

        $data = $response->getData();
        if (!is_array($data) || !isset($data['access_token'])) {
            throw new AuthenticationException(
                'Invalid token response from Keycloak: ' . $response->getRawBody(),
                $response->getStatusCode()
            );
        }

        /** @var array{access_token: string, refresh_token?: string, expires_in: int, refresh_expires_in?: int, token_type?: string, scope?: string} $data */
        return Token::fromArray($data);
    }

    private function refreshAccessToken(string $refreshToken): Token
    {
        $path = sprintf('/realms/%s/protocol/openid-connect/token', rawurlencode($this->configuration->getAuthRealm()));
        $credentials = $this->configuration->getCredentials();
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        // Include client_id / client_secret from credentials if available
        $origParams = $credentials->toFormParams();
        if (isset($origParams['client_id'])) {
            $params['client_id'] = $origParams['client_id'];
        }
        if (isset($origParams['client_secret'])) {
            $params['client_secret'] = $origParams['client_secret'];
        }

        $response = $this->httpClient->postForm($path, $params);
        $data = $response->getData();

        if (!is_array($data) || !isset($data['access_token'])) {
            throw new AuthenticationException(
                'Invalid token refresh response from Keycloak: ' . $response->getRawBody(),
                $response->getStatusCode()
            );
        }

        /** @var array{access_token: string, refresh_token?: string, expires_in: int, refresh_expires_in?: int, token_type?: string, scope?: string} $data */
        return Token::fromArray($data);
    }

    private function getCacheKey(): string
    {
        return sprintf(
            '%s_%s_%s',
            hash('xxh128', $this->configuration->getBaseUrl()),
            $this->configuration->getAuthRealm(),
            $this->configuration->getCredentials()->getCacheIdentifier()
        );
    }
}
