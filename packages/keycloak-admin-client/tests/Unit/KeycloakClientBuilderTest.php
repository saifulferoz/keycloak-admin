<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Tests\Unit;

use Feroz\KeycloakAdmin\KeycloakClient;
use Feroz\KeycloakAdmin\KeycloakClientInterface;
use PHPUnit\Framework\TestCase;

final class KeycloakClientBuilderTest extends TestCase
{
    public function testBuilderInstantiatesClient(): void
    {
        $client = KeycloakClient::builder()
            ->withBaseUrl('https://iam.example.com')
            ->withAuthRealm('master')
            ->withDefaultTargetRealm('myrealm')
            ->withClientCredentials('admin-cli', 'secret-key')
            ->build();

        $this->assertInstanceOf(KeycloakClientInterface::class, $client);
        $this->assertSame('https://iam.example.com', $client->getConfiguration()->getBaseUrl());
        $this->assertSame('master', $client->getConfiguration()->getAuthRealm());
        $this->assertSame('myrealm', $client->getConfiguration()->getDefaultTargetRealm());
    }
}
