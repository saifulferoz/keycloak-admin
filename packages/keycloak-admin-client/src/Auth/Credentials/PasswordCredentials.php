<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth\Credentials;

final readonly class PasswordCredentials implements CredentialsInterface
{
    public function __construct(
        private string $clientId,
        private string $username,
        private string $password,
        private ?string $clientSecret = null
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function toFormParams(): array
    {
        $params = [
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'username' => $this->username,
            'password' => $this->password,
        ];

        if ($this->clientSecret !== null) {
            $params['client_secret'] = $this->clientSecret;
        }

        return $params;
    }

    public function getCacheIdentifier(): string
    {
        return 'user_' . hash('xxh128', $this->clientId . '_' . $this->username);
    }
}
