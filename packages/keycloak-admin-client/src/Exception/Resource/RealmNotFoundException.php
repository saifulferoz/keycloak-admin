<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Exception\Resource;

class RealmNotFoundException extends ResourceNotFoundException
{
    public function __construct(string $realmName, int $statusCode = 404, array|string|null $responseBody = null)
    {
        parent::__construct('Realm', $realmName, $statusCode, $responseBody);
    }
}
