<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth;

interface TokenManagerInterface
{
    /**
     * Obtains a valid Bearer access token (from cache or via OAuth2 negotiation).
     */
    public function getAccessToken(): string;

    /**
     * Invalidates the current token in storage.
     */
    public function invalidateToken(): void;
}
