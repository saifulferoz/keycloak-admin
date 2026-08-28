<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Tests\Unit;

use Feroz\KeycloakAdmin\Auth\Token;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    public function testTokenInstantiationAndExpiration(): void
    {
        $token = Token::fromArray([
            'access_token' => 'access-token-123',
            'refresh_token' => 'refresh-token-456',
            'expires_in' => 300,
            'refresh_expires_in' => 1800,
            'token_type' => 'Bearer',
        ]);

        $this->assertSame('access-token-123', $token->getAccessToken());
        $this->assertSame('refresh-token-456', $token->getRefreshToken());
        $this->assertSame('Bearer', $token->getTokenType());
        $this->assertFalse($token->isExpired());
        $this->assertFalse($token->isRefreshExpired());
    }

    public function testTokenExpiredCheck(): void
    {
        $token = new Token(
            accessToken: 'token',
            refreshToken: null,
            expiresAt: time() - 10
        );

        $this->assertTrue($token->isExpired());
    }
}
