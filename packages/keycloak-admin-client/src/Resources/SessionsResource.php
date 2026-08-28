<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\Session\UserSessionRepresentation;

class SessionsResource extends AbstractResource
{
    public function __construct(
        HttpClientInterface $httpClient,
        TokenManagerInterface $tokenManager,
        private readonly string $realm
    ) {
        parent::__construct($httpClient, $tokenManager);
    }

    /**
     * @return list<UserSessionRepresentation>
     */
    public function getUserSessions(string $userId): array
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/users/%s/sessions', rawurlencode($this->realm), rawurlencode($userId))
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => UserSessionRepresentation::fromArray($item),
            $data
        );
    }

    /**
     * @return list<UserSessionRepresentation>
     */
    public function getClientUserSessions(string $clientUuid, ?int $first = null, ?int $max = null): array
    {
        $params = [];
        if ($first !== null) {
            $params['first'] = (string) $first;
        }
        if ($max !== null) {
            $params['max'] = (string) $max;
        }

        $response = $this->sendGet(
            sprintf('/admin/realms/%s/clients/%s/user-sessions', rawurlencode($this->realm), rawurlencode($clientUuid)),
            $params
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => UserSessionRepresentation::fromArray($item),
            $data
        );
    }

    public function logoutAll(): void
    {
        $this->sendPost(
            sprintf('/admin/realms/%s/logout-all', rawurlencode($this->realm))
        );
    }

    public function deleteUserSession(string $sessionId): void
    {
        $this->sendDelete(
            sprintf('/admin/realms/%s/sessions/%s', rawurlencode($this->realm), rawurlencode($sessionId))
        );
    }
}
