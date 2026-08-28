<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth\Credentials;

interface CredentialsInterface
{
    /**
     * @return array<string, string> Form parameters for OAuth2 token endpoint
     */
    public function toFormParams(): array;

    /**
     * Returns a cache key identifier for these credentials.
     */
    public function getCacheIdentifier(): string;
}
