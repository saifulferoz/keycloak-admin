<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth\Credentials;

final readonly class ClientCredentials implements CredentialsInterface
{
    public function __construct(
        private string $clientId,
        private ?string $clientSecret = null
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function toFormParams(): array
    {
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
        ];

        if ($this->clientSecret !== null) {
            $params['client_secret'] = $this->clientSecret;
        }

        return $params;
    }

    public function getCacheIdentifier(): string
    {
        return 'client_' . hash('xxh128', $this->clientId . ($this->clientSecret ?? ''));
    }
}
