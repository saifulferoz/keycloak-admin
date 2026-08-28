<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Exception\Resource;

class UserAlreadyExistsException extends ConflictException
{
    public function __construct(string $usernameOrEmail, int $statusCode = 409, array|string|null $responseBody = null)
    {
        parent::__construct(
            sprintf('A user with username or email "%s" already exists.', $usernameOrEmail),
            $statusCode,
            $responseBody
        );
    }
}
