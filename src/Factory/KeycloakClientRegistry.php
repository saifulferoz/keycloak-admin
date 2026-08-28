<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdminBundle\Factory;

use Feroz\KeycloakAdmin\KeycloakClientInterface;
use InvalidArgumentException;

final class KeycloakClientRegistry
{
    /**
     * @param array<string, KeycloakClientInterface> $clients
     */
    public function __construct(
        private readonly array $clients,
        private readonly string $defaultClientName = 'default'
    ) {
    }

    public function getDefaultClient(): KeycloakClientInterface
    {
        return $this->getClient($this->defaultClientName);
    }

    public function getClient(?string $name = null): KeycloakClientInterface
    {
        $clientName = $name ?? $this->defaultClientName;

        if (!isset($this->clients[$clientName])) {
            throw new InvalidArgumentException(sprintf(
                'Keycloak client "%s" is not configured. Available clients: %s',
                $clientName,
                implode(', ', array_keys($this->clients))
            ));
        }

        return $this->clients[$clientName];
    }

    public function hasClient(string $name): bool
    {
        return isset($this->clients[$name]);
    }

    /**
     * @return array<string, KeycloakClientInterface>
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    public function getDefaultClientName(): string
    {
        return $this->defaultClientName;
    }
}
