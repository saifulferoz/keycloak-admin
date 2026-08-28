<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Exception;

use Throwable;

/**
 * Marker interface for all Keycloak Admin Client exceptions.
 */
interface KeycloakException extends Throwable
{
}
