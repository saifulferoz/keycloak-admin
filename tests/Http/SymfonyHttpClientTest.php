<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Tests\Http;

use Feroz\KeycloakAdmin\Auth\Credentials\ClientCredentials;
use Feroz\KeycloakAdmin\Configuration;
use Feroz\KeycloakAdmin\Exception\Auth\AuthenticationException;
use Feroz\KeycloakAdmin\Exception\Resource\ConflictException;
use Feroz\KeycloakAdmin\Exception\Resource\ResourceNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\ValidationException;
use Feroz\KeycloakAdminBundle\Http\SymfonyHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SymfonyHttpClientTest extends TestCase
{
    private Configuration $config;

    protected function setUp(): void
    {
        $this->config = new Configuration(
            'https://iam.example.com',
            'master',
            'myrealm',
            new ClientCredentials('admin-cli', 'secret')
        );
    }

    public function testGetRequest(): void
    {
        $mockResponse = new MockResponse(json_encode(['status' => 'success']), [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $mockClient = new MockHttpClient($mockResponse);
        $client = new SymfonyHttpClient($this->config, $mockClient);

        $response = $client->get('/admin/realms/myrealm/users', ['search' => 'feroz']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['status' => 'success'], $response->getData());
        $this->assertSame('GET', $mockResponse->getRequestMethod());
        $this->assertStringContainsString('search=feroz', $mockResponse->getRequestUrl());
    }

    public function testPostJsonRequest(): void
    {
        $mockResponse = new MockResponse(json_encode(['id' => 'user-123']), [
            'http_code' => 201,
            'response_headers' => ['Location' => 'https://iam.example.com/admin/realms/myrealm/users/user-123'],
        ]);

        $mockClient = new MockHttpClient($mockResponse);
        $client = new SymfonyHttpClient($this->config, $mockClient);

        $response = $client->post('/admin/realms/myrealm/users', ['username' => 'newuser']);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('user-123', $response->getCreatedResourceId());
        $this->assertSame('POST', $mockResponse->getRequestMethod());
    }

    public function testPostFormRequest(): void
    {
        $mockResponse = new MockResponse(json_encode(['access_token' => 'mock-jwt-token', 'expires_in' => 300]), [
            'http_code' => 200,
        ]);

        $mockClient = new MockHttpClient($mockResponse);
        $client = new SymfonyHttpClient($this->config, $mockClient);

        $response = $client->postForm('/realms/master/protocol/openid-connect/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'admin-cli',
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('mock-jwt-token', $response->getData()['access_token']);
    }

    public function testHandles404NotFoundException(): void
    {
        $mockResponse = new MockResponse(json_encode(['error' => 'User not found']), [
            'http_code' => 404,
        ]);

        $mockClient = new MockHttpClient($mockResponse);
        $client = new SymfonyHttpClient($this->config, $mockClient);

        $this->expectException(ResourceNotFoundException::class);
        $client->get('/admin/realms/myrealm/users/invalid-id');
    }

    public function testHandles409ConflictException(): void
    {
        $mockResponse = new MockResponse(json_encode(['errorMessage' => 'User exists with same username']), [
            'http_code' => 409,
        ]);

        $mockClient = new MockHttpClient($mockResponse);
        $client = new SymfonyHttpClient($this->config, $mockClient);

        $this->expectException(ConflictException::class);
        $client->post('/admin/realms/myrealm/users', ['username' => 'duplicate']);
    }

    public function testHandles400ValidationException(): void
    {
        $mockResponse = new MockResponse(json_encode(['errorMessage' => 'Password too short']), [
            'http_code' => 400,
        ]);

        $mockClient = new MockHttpClient($mockResponse);
        $client = new SymfonyHttpClient($this->config, $mockClient);

        $this->expectException(ValidationException::class);
        $client->post('/admin/realms/myrealm/users', ['password' => '123']);
    }

    public function testHandles401UnauthorizedException(): void
    {
        $mockResponse = new MockResponse(json_encode(['error' => 'invalid_client']), [
            'http_code' => 401,
        ]);

        $mockClient = new MockHttpClient($mockResponse);
        $client = new SymfonyHttpClient($this->config, $mockClient);

        $this->expectException(AuthenticationException::class);
        $client->get('/admin/realms/myrealm/users');
    }
}
