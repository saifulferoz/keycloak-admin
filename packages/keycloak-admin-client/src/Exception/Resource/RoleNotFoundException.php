<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Exception\Resource;

class RoleNotFoundException extends ResourceNotFoundException
{
    public function __construct(string $roleName, int $statusCode = 404, array|string|null $responseBody = null)
    {
        parent::__construct('Role', $roleName, $statusCode, $responseBody);
    }
}
