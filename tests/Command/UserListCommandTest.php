<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\Command;

use Feroz\KeycloakAdmin\KeycloakClientInterface;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Query\PaginatedResult;
use Feroz\KeycloakAdmin\Resources\UsersResource;
use Feroz\KeycloakAdminBundle\Command\User\UserListCommand;
use Feroz\KeycloakAdminBundle\Factory\KeycloakClientRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UserListCommandTest extends TestCase
{
    public function testListUsersOutputsTable(): void
    {
        $usersResource = $this->createMock(UsersResource::class);
        $user1 = UserRepresentation::create('feroz@example.com', 'saiful.feroz')->withName('Saiful', 'Feroz');

        $usersResource->method('list')->willReturn(new PaginatedResult([$user1]));
        $usersResource->method('count')->willReturn(1);

        $client = $this->createMock(KeycloakClientInterface::class);
        $client->method('users')->willReturn($usersResource);

        $registry = new KeycloakClientRegistry(['default' => $client], 'default');
        $command = new UserListCommand($registry);

        $tester = new CommandTester($command);
        $status = $tester->execute([]);

        $this->assertSame(0, $status);
        $this->assertStringContainsString('saiful.feroz', $tester->getDisplay());
        $this->assertStringContainsString('feroz@example.com', $tester->getDisplay());
        $this->assertStringContainsString('Saiful Feroz', $tester->getDisplay());
    }
}
