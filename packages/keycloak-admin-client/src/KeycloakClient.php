<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin;

use Feroz\KeycloakAdmin\Auth\Storage\InMemoryTokenStorage;
use Feroz\KeycloakAdmin\Auth\Storage\TokenStorageInterface;
use Feroz\KeycloakAdmin\Auth\TokenManager;
use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Http\Psr18HttpClient;
use Feroz\KeycloakAdmin\Resources\AttackDetectionResource;
use Feroz\KeycloakAdmin\Resources\ClientsResource;
use Feroz\KeycloakAdmin\Resources\GroupsResource;
use Feroz\KeycloakAdmin\Resources\IdentityProvidersResource;
use Feroz\KeycloakAdmin\Resources\RealmsResource;
use Feroz\KeycloakAdmin\Resources\RolesResource;
use Feroz\KeycloakAdmin\Resources\SessionsResource;
use Feroz\KeycloakAdmin\Resources\UsersResource;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class KeycloakClient implements KeycloakClientInterface
{
    private HttpClientInterface $httpClient;
    private TokenManagerInterface $tokenManager;

    public function __construct(
        private readonly Configuration $configuration,
        ?HttpClientInterface $httpClient = null,
        ?TokenManagerInterface $tokenManager = null,
        ?TokenStorageInterface $tokenStorage = null,
        ?LoggerInterface $logger = null
    ) {
        $log = $logger ?? new NullLogger();
        $this->httpClient = $httpClient ?? new Psr18HttpClient($this->configuration, logger: $log);
        $this->tokenManager = $tokenManager ?? new TokenManager(
            $this->configuration,
            $this->httpClient,
            $tokenStorage ?? new InMemoryTokenStorage(),
            $log
        );
    }

    public static function builder(): KeycloakClientBuilder
    {
        return new KeycloakClientBuilder();
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    public function getTokenManager(): TokenManagerInterface
    {
        return $this->tokenManager;
    }

    public function realms(): RealmsResource
    {
        return new RealmsResource($this->httpClient, $this->tokenManager);
    }

    public function users(?string $realm = null): UsersResource
    {
        return new UsersResource($this->httpClient, $this->tokenManager, $this->resolveRealm($realm));
    }

    public function groups(?string $realm = null): GroupsResource
    {
        return new GroupsResource($this->httpClient, $this->tokenManager, $this->resolveRealm($realm));
    }

    public function roles(?string $realm = null): RolesResource
    {
        return new RolesResource($this->httpClient, $this->tokenManager, $this->resolveRealm($realm));
    }

    public function clients(?string $realm = null): ClientsResource
    {
        return new ClientsResource($this->httpClient, $this->tokenManager, $this->resolveRealm($realm));
    }

    public function identityProviders(?string $realm = null): IdentityProvidersResource
    {
        return new IdentityProvidersResource($this->httpClient, $this->tokenManager, $this->resolveRealm($realm));
    }

    public function attackDetection(?string $realm = null): AttackDetectionResource
    {
        return new AttackDetectionResource($this->httpClient, $this->tokenManager, $this->resolveRealm($realm));
    }

    public function sessions(?string $realm = null): SessionsResource
    {
        return new SessionsResource($this->httpClient, $this->tokenManager, $this->resolveRealm($realm));
    }

    public function ping(): bool
    {
        try {
            $token = $this->tokenManager->getAccessToken();
            return !empty($token);
        } catch (Throwable) {
            return false;
        }
    }

    private function resolveRealm(?string $realm): string
    {
        return $realm ?? $this->configuration->getDefaultTargetRealm();
    }
}
