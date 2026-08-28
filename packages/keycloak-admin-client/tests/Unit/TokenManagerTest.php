<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Tests\Unit;

use Feroz\KeycloakAdmin\Auth\Credentials\ClientCredentials;
use Feroz\KeycloakAdmin\Auth\Storage\InMemoryTokenStorage;
use Feroz\KeycloakAdmin\Auth\Token;
use Feroz\KeycloakAdmin\Auth\TokenManager;
use Feroz\KeycloakAdmin\Configuration;
use Feroz\KeycloakAdmin\Http\ApiResponse;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use PHPUnit\Framework\TestCase;

final class TokenManagerTest extends TestCase
{
    public function testGetAccessTokenFetchesAndCachesToken(): void
    {
        $config = new Configuration(
            baseUrl: 'http://localhost:8080',
            authRealm: 'master',
            defaultTargetRealm: 'master',
            credentials: new ClientCredentials('admin-cli', 'secret')
        );

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('postForm')
            ->with(
                '/realms/master/protocol/openid-connect/token',
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => 'admin-cli',
                    'client_secret' => 'secret',
                ]
            )
            ->willReturn(new ApiResponse(200, [
                'access_token' => 'mocked-jwt-token',
                'expires_in' => 300,
                'token_type' => 'Bearer',
            ]));

        $storage = new InMemoryTokenStorage();
        $tokenManager = new TokenManager($config, $httpClient, $storage);

        // First call should hit HTTP client
        $token1 = $tokenManager->getAccessToken();
        $this->assertSame('mocked-jwt-token', $token1);

        // Second call should return cached token without HTTP call
        $token2 = $tokenManager->getAccessToken();
        $this->assertSame('mocked-jwt-token', $token2);
    }

    public function testTokenManagerRefreshesExpiredToken(): void
    {
        $config = new Configuration(
            baseUrl: 'http://localhost:8080',
            authRealm: 'master',
            defaultTargetRealm: 'master',
            credentials: new ClientCredentials('admin-cli', 'secret')
        );

        $storage = new InMemoryTokenStorage();
        // Seed storage with expired token but valid refresh token
        $cacheKey = sprintf(
            '%s_master_%s',
            hash('xxh128', 'http://localhost:8080'),
            $config->getCredentials()->getCacheIdentifier()
        );
        $storage->set($cacheKey, new Token(
            accessToken: 'expired-access',
            refreshToken: 'valid-refresh',
            expiresAt: time() - 10,
            refreshExpiresAt: time() + 3600
        ));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('postForm')
            ->with(
                '/realms/master/protocol/openid-connect/token',
                [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => 'valid-refresh',
                    'client_id' => 'admin-cli',
                    'client_secret' => 'secret',
                ]
            )
            ->willReturn(new ApiResponse(200, [
                'access_token' => 'new-refreshed-jwt',
                'refresh_token' => 'new-refresh-token',
                'expires_in' => 300,
                'refresh_expires_in' => 1800,
            ]));

        $tokenManager = new TokenManager($config, $httpClient, $storage);
        $token = $tokenManager->getAccessToken();

        $this->assertSame('new-refreshed-jwt', $token);
    }
}
