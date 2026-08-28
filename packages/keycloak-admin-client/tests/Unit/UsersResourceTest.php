<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Tests\Unit;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Exception\Resource\ConflictException;
use Feroz\KeycloakAdmin\Exception\Resource\ResourceNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\UserAlreadyExistsException;
use Feroz\KeycloakAdmin\Exception\Resource\UserNotFoundException;
use Feroz\KeycloakAdmin\Http\ApiResponse;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Resources\UsersResource;
use PHPUnit\Framework\TestCase;

final class UsersResourceTest extends TestCase
{
    public function testCreateUserParsesLocationHeader(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $tokenManager = $this->createMock(TokenManagerInterface::class);

        $tokenManager->method('getAccessToken')->willReturn('valid-token');

        $user = UserRepresentation::create('test@example.com');

        $httpClient->expects($this->once())
            ->method('post')
            ->with(
                '/admin/realms/master/users',
                $user->toArray(),
                ['Authorization' => 'Bearer valid-token']
            )
            ->willReturn(new ApiResponse(201, null, ['Location' => ['http://localhost:8080/admin/realms/master/users/uuid-12345']]));

        $resource = new UsersResource($httpClient, $tokenManager, 'master');
        $id = $resource->create($user);

        $this->assertSame('uuid-12345', $id);
    }

    public function testCreateUserThrowsUserAlreadyExistsExceptionOnConflict(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $tokenManager = $this->createMock(TokenManagerInterface::class);

        $tokenManager->method('getAccessToken')->willReturn('valid-token');

        $user = UserRepresentation::create('duplicate@example.com');

        $httpClient->expects($this->once())
            ->method('post')
            ->willThrowException(new ConflictException('User exists with same username', 409));

        $this->expectException(UserAlreadyExistsException::class);

        $resource = new UsersResource($httpClient, $tokenManager, 'master');
        $resource->create($user);
    }

    public function testGetUserThrowsUserNotFoundException(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $tokenManager = $this->createMock(TokenManagerInterface::class);

        $tokenManager->method('getAccessToken')->willReturn('valid-token');

        $httpClient->expects($this->once())
            ->method('get')
            ->willThrowException(new ResourceNotFoundException('User', '/users/unknown-id'));

        $this->expectException(UserNotFoundException::class);

        $resource = new UsersResource($httpClient, $tokenManager, 'master');
        $resource->get('unknown-id');
    }
}
