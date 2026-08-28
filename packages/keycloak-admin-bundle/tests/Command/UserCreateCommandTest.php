<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\Command;

use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Resources\UsersResource;
use Feroz\KeycloakAdminBundle\Command\User\UserCreateCommand;
use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UserCreateCommandTest extends TestCase
{
    public function testCreateUserCommandSuccess(): void
    {
        $usersResource = $this->createMock(UsersResource::class);
        $usersResource->expects($this->once())
            ->method('create')
            ->willReturn('created-uuid-999');

        $client = $this->createMock(KeycloakClientInterface::class);
        $client->method('users')->willReturn($usersResource);

        $registry = new KeycloakClientRegistry(['default' => $client], 'default');
        $command = new UserCreateCommand($registry);

        $tester = new CommandTester($command);
        $status = $tester->execute([
            'username' => 'new.user',
            'email' => 'new@example.com',
            '--first-name' => 'John',
            '--last-name' => 'Doe',
            '--password' => 'SecurePass123!',
        ]);

        $this->assertSame(0, $status);
        $this->assertStringContainsString('created-uuid-999', $tester->getDisplay());
    }
}
