<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\Command;

use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdmin\Resources\UsersResource;
use Feroz\KeycloakAdminBundle\Command\User\UserDeleteCommand;
use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UserDeleteCommandTest extends TestCase
{
    public function testDeleteUserWithForceOption(): void
    {
        $usersResource = $this->createMock(UsersResource::class);
        $usersResource->expects($this->once())
            ->method('delete')
            ->with('user-uuid-123');

        $client = $this->createMock(KeycloakClientInterface::class);
        $client->method('users')->willReturn($usersResource);

        $registry = new KeycloakClientRegistry(['default' => $client], 'default');
        $command = new UserDeleteCommand($registry);

        $tester = new CommandTester($command);
        $status = $tester->execute([
            'user-id' => 'user-uuid-123',
            '--force' => true,
        ]);

        $this->assertSame(0, $status);
        $this->assertStringContainsString('user-uuid-123', $tester->getDisplay());
        $this->assertStringContainsString('successfully deleted', $tester->getDisplay());
    }
}
