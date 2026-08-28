<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Exception\Resource;

class UserNotFoundException extends ResourceNotFoundException
{
    public function __construct(string $userIdentifier, int $statusCode = 404, array|string|null $responseBody = null)
    {
        parent::__construct('User', $userIdentifier, $statusCode, $responseBody);
    }
}
