<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin;

use Feroz\KeycloakAdmin\Auth\Credentials\CredentialsInterface;

/**
 * Keycloak Admin Client configuration.
 */
final class Configuration
{
    public function __construct(
        private string $baseUrl,
        private string $authRealm,
        private ?string $defaultTargetRealm,
        private CredentialsInterface $credentials,
        private float $timeout = 15.0,
        private bool $verifySsl = true
    ) {
        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getAuthRealm(): string
    {
        return $this->authRealm;
    }

    public function getDefaultTargetRealm(): string
    {
        return $this->defaultTargetRealm ?? $this->authRealm;
    }

    public function getCredentials(): CredentialsInterface
    {
        return $this->credentials;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function isVerifySsl(): bool
    {
        return $this->verifySsl;
    }
}
