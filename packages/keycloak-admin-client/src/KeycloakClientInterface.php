<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Resources\AttackDetectionResource;
use Feroz\KeycloakAdmin\Resources\ClientsResource;
use Feroz\KeycloakAdmin\Resources\GroupsResource;
use Feroz\KeycloakAdmin\Resources\IdentityProvidersResource;
use Feroz\KeycloakAdmin\Resources\RealmsResource;
use Feroz\KeycloakAdmin\Resources\RolesResource;
use Feroz\KeycloakAdmin\Resources\SessionsResource;
use Feroz\KeycloakAdmin\Resources\UsersResource;

interface KeycloakClientInterface
{
    public function getConfiguration(): Configuration;

    public function getHttpClient(): HttpClientInterface;

    public function getTokenManager(): TokenManagerInterface;

    public function realms(): RealmsResource;

    public function users(?string $realm = null): UsersResource;

    public function groups(?string $realm = null): GroupsResource;

    public function roles(?string $realm = null): RolesResource;

    public function clients(?string $realm = null): ClientsResource;

    public function identityProviders(?string $realm = null): IdentityProvidersResource;

    public function attackDetection(?string $realm = null): AttackDetectionResource;

    public function sessions(?string $realm = null): SessionsResource;

    /**
     * Checks if the Keycloak server is reachable and authentication succeeds.
     */
    public function ping(): bool;
}
