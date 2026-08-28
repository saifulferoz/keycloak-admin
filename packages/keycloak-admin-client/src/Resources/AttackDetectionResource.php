<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\AttackDetection\UserStatusRepresentation;

class AttackDetectionResource extends AbstractResource
{
    public function __construct(
        HttpClientInterface $httpClient,
        TokenManagerInterface $tokenManager,
        private readonly string $realm
    ) {
        parent::__construct($httpClient, $tokenManager);
    }

    public function getUserStatus(string $userId): UserStatusRepresentation
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/attack-detection/brute-force/users/%s', rawurlencode($this->realm), rawurlencode($userId))
        );

        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return UserStatusRepresentation::fromArray(is_array($data) ? $data : []);
    }

    public function clearUserStatus(string $userId): void
    {
        $this->sendDelete(
            sprintf('/admin/realms/%s/attack-detection/brute-force/users/%s', rawurlencode($this->realm), rawurlencode($userId))
        );
    }

    public function clearAllUsersStatus(): void
    {
        $this->sendDelete(
            sprintf('/admin/realms/%s/attack-detection/brute-force/users', rawurlencode($this->realm))
        );
    }
}
