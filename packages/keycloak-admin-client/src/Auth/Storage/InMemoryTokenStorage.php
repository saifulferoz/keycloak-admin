<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Auth\Storage;

use Feroz\KeycloakAdmin\Auth\Token;

final class InMemoryTokenStorage implements TokenStorageInterface
{
    /**
     * @var array<string, Token>
     */
    private array $tokens = [];

    public function get(string $key): ?Token
    {
        return $this->tokens[$key] ?? null;
    }

    public function set(string $key, Token $token): void
    {
        $this->tokens[$key] = $token;
    }

    public function delete(string $key): void
    {
        unset($this->tokens[$key]);
    }
}
