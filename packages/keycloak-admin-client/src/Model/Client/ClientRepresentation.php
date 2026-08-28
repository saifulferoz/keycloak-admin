<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\Client;

final readonly class ClientRepresentation
{
    /**
     * @param list<string>|null $redirectUris
     * @param list<string>|null $webOrigins
     * @param list<string>|null $defaultClientScopes
     * @param list<string>|null $optionalClientScopes
     * @param array<string, string>|null $attributes
     */
    public function __construct(
        public ?string $id = null,
        public ?string $clientId = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $rootUrl = null,
        public ?string $baseUrl = null,
        public ?bool $enabled = true,
        public ?bool $publicClient = false,
        public ?bool $bearerOnly = false,
        public ?bool $directAccessGrantsEnabled = true,
        public ?bool $serviceAccountsEnabled = false,
        public ?bool $standardFlowEnabled = true,
        public ?bool $implicitFlowEnabled = false,
        public ?string $secret = null,
        public ?array $redirectUris = null,
        public ?array $webOrigins = null,
        public ?array $defaultClientScopes = null,
        public ?array $optionalClientScopes = null,
        public ?array $attributes = null,
        public ?string $protocol = 'openid-connect'
    ) {
    }

    public static function create(string $clientId): self
    {
        return new self(
            clientId: $clientId,
            enabled: true
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            clientId: isset($data['clientId']) ? (string) $data['clientId'] : null,
            name: isset($data['name']) ? (string) $data['name'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            rootUrl: isset($data['rootUrl']) ? (string) $data['rootUrl'] : null,
            baseUrl: isset($data['baseUrl']) ? (string) $data['baseUrl'] : null,
            enabled: isset($data['enabled']) ? (bool) $data['enabled'] : true,
            publicClient: isset($data['publicClient']) ? (bool) $data['publicClient'] : false,
            bearerOnly: isset($data['bearerOnly']) ? (bool) $data['bearerOnly'] : false,
            directAccessGrantsEnabled: isset($data['directAccessGrantsEnabled']) ? (bool) $data['directAccessGrantsEnabled'] : true,
            serviceAccountsEnabled: isset($data['serviceAccountsEnabled']) ? (bool) $data['serviceAccountsEnabled'] : false,
            standardFlowEnabled: isset($data['standardFlowEnabled']) ? (bool) $data['standardFlowEnabled'] : true,
            implicitFlowEnabled: isset($data['implicitFlowEnabled']) ? (bool) $data['implicitFlowEnabled'] : false,
            secret: isset($data['secret']) ? (string) $data['secret'] : null,
            redirectUris: isset($data['redirectUris']) && is_array($data['redirectUris']) ? array_values($data['redirectUris']) : null,
            webOrigins: isset($data['webOrigins']) && is_array($data['webOrigins']) ? array_values($data['webOrigins']) : null,
            defaultClientScopes: isset($data['defaultClientScopes']) && is_array($data['defaultClientScopes']) ? array_values($data['defaultClientScopes']) : null,
            optionalClientScopes: isset($data['optionalClientScopes']) && is_array($data['optionalClientScopes']) ? array_values($data['optionalClientScopes']) : null,
            attributes: isset($data['attributes']) && is_array($data['attributes']) ? $data['attributes'] : null,
            protocol: isset($data['protocol']) ? (string) $data['protocol'] : 'openid-connect'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->id !== null) {
            $data['id'] = $this->id;
        }
        if ($this->clientId !== null) {
            $data['clientId'] = $this->clientId;
        }
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        if ($this->rootUrl !== null) {
            $data['rootUrl'] = $this->rootUrl;
        }
        if ($this->baseUrl !== null) {
            $data['baseUrl'] = $this->baseUrl;
        }
        if ($this->enabled !== null) {
            $data['enabled'] = $this->enabled;
        }
        if ($this->publicClient !== null) {
            $data['publicClient'] = $this->publicClient;
        }
        if ($this->bearerOnly !== null) {
            $data['bearerOnly'] = $this->bearerOnly;
        }
        if ($this->directAccessGrantsEnabled !== null) {
            $data['directAccessGrantsEnabled'] = $this->directAccessGrantsEnabled;
        }
        if ($this->serviceAccountsEnabled !== null) {
            $data['serviceAccountsEnabled'] = $this->serviceAccountsEnabled;
        }
        if ($this->standardFlowEnabled !== null) {
            $data['standardFlowEnabled'] = $this->standardFlowEnabled;
        }
        if ($this->implicitFlowEnabled !== null) {
            $data['implicitFlowEnabled'] = $this->implicitFlowEnabled;
        }
        if ($this->secret !== null) {
            $data['secret'] = $this->secret;
        }
        if ($this->redirectUris !== null) {
            $data['redirectUris'] = $this->redirectUris;
        }
        if ($this->webOrigins !== null) {
            $data['webOrigins'] = $this->webOrigins;
        }
        if ($this->defaultClientScopes !== null) {
            $data['defaultClientScopes'] = $this->defaultClientScopes;
        }
        if ($this->optionalClientScopes !== null) {
            $data['optionalClientScopes'] = $this->optionalClientScopes;
        }
        if ($this->attributes !== null) {
            $data['attributes'] = $this->attributes;
        }
        if ($this->protocol !== null) {
            $data['protocol'] = $this->protocol;
        }

        return $data;
    }
}
