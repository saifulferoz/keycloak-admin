<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth\Storage;

use Feroz\KeycloakAdmin\Auth\Token;

interface TokenStorageInterface
{
    public function get(string $key): ?Token;

    public function set(string $key, Token $token): void;

    public function delete(string $key): void;
}
