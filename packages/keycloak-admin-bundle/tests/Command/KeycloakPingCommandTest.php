<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\Command;

use Feroz\KeycloakAdmin\Auth\Credentials\ClientCredentials;
use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Configuration;
use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdminBundle\Command\KeycloakPingCommand;
use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class KeycloakPingCommandTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->method('getAccessToken')->willReturn('valid-access-token');

        $config = new Configuration('http://localhost:8080', 'master', 'master', new ClientCredentials('admin-cli'));

        $client = $this->createMock(KeycloakClientInterface::class);
        $client->method('getTokenManager')->willReturn($tokenManager);
        $client->method('getConfiguration')->willReturn($config);

        $registry = new KeycloakClientRegistry(['default' => $client], 'default');
        $command = new KeycloakPingCommand($registry);

        $tester = new CommandTester($command);
        $status = $tester->execute([]);

        $this->assertSame(0, $status);
        $this->assertStringContainsString('Successfully authenticated with Keycloak', $tester->getDisplay());
    }
}
