<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Exception\Resource;

use Feroz\KeycloakAdmin\Exception\KeycloakApiException;

class ResourceNotFoundException extends KeycloakApiException
{
    public function __construct(
        string $resourceType,
        string $resourceIdentifier,
        int $statusCode = 404,
        array|string|null $responseBody = null
    ) {
        parent::__construct(
            sprintf('%s with identifier "%s" was not found.', $resourceType, $resourceIdentifier),
            $statusCode,
            $responseBody
        );
    }
}
