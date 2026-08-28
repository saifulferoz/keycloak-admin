<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Exception\Resource\ConflictException;
use Feroz\KeycloakAdmin\Exception\Resource\ResourceNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\UserAlreadyExistsException;
use Feroz\KeycloakAdmin\Exception\Resource\UserNotFoundException;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\Enum\RequiredAction;
use Feroz\KeycloakAdmin\Model\Group\GroupRepresentation;
use Feroz\KeycloakAdmin\Model\Role\RoleRepresentation;
use Feroz\KeycloakAdmin\Model\Session\UserSessionRepresentation;
use Feroz\KeycloakAdmin\Model\User\CredentialRepresentation;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Query\PaginatedResult;
use Feroz\KeycloakAdmin\Query\UserQuery;

class UsersResource extends AbstractResource
{
    public function __construct(
        HttpClientInterface $httpClient,
        TokenManagerInterface $tokenManager,
        private readonly string $realm
    ) {
        parent::__construct($httpClient, $tokenManager);
    }

    /**
     * @return PaginatedResult<UserRepresentation>
     */
    public function list(?UserQuery $query = null): PaginatedResult
    {
        $queryParams = $query !== null ? $query->toQueryParams() : [];
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/users', rawurlencode($this->realm)),
            $queryParams
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return new PaginatedResult([]);
        }

        $items = array_map(
            static fn(array $item) => UserRepresentation::fromArray($item),
            $data
        );

        return new PaginatedResult($items);
    }

    public function count(?UserQuery $query = null): int
    {
        $queryParams = $query !== null ? $query->toQueryParams() : [];
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/users/count', rawurlencode($this->realm)),
            $queryParams
        );

        return (int) $response->getData();
    }

    public function get(string $userId): UserRepresentation
    {
        try {
            $response = $this->sendGet(sprintf('/admin/realms/%s/users/%s', rawurlencode($this->realm), rawurlencode($userId)));
        } catch (ResourceNotFoundException) {
            throw new UserNotFoundException($userId);
        }

        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return UserRepresentation::fromArray($data);
    }

    /**
     * Creates a new user and returns the newly generated User ID.
     */
    public function create(UserRepresentation $user): string
    {
        try {
            $response = $this->sendPost(
                sprintf('/admin/realms/%s/users', rawurlencode($this->realm)),
                $user->toArray()
            );
        } catch (ConflictException $e) {
            throw new UserAlreadyExistsException($user->username ?? $user->email ?? 'unknown', 409, $e->getResponseBody(), $e);
        }

        $createdId = $response->getCreatedResourceId();
        if ($createdId !== null) {
            return $createdId;
        }

        // Fallback if Location header wasn't parsed: lookup by username/email
        if ($user->username !== null) {
            $found = $this->list(UserQuery::create()->withUsername($user->username)->withExact(true));
            if ($found->count() > 0 && $found->getItems()[0]->id !== null) {
                return $found->getItems()[0]->id;
            }
        }

        return '';
    }

    public function update(string $userId, UserRepresentation $user): void
    {
        try {
            $this->sendPut(
                sprintf('/admin/realms/%s/users/%s', rawurlencode($this->realm), rawurlencode($userId)),
                $user->toArray()
            );
        } catch (ResourceNotFoundException) {
            throw new UserNotFoundException($userId);
        }
    }

    public function delete(string $userId): void
    {
        try {
            $this->sendDelete(sprintf('/admin/realms/%s/users/%s', rawurlencode($this->realm), rawurlencode($userId)));
        } catch (ResourceNotFoundException) {
            throw new UserNotFoundException($userId);
        }
    }

    public function resetPassword(string $userId, string $newPassword, bool $temporary = false): void
    {
        $credential = CredentialRepresentation::password($newPassword, $temporary);

        $this->sendPut(
            sprintf('/admin/realms/%s/users/%s/reset-password', rawurlencode($this->realm), rawurlencode($userId)),
            $credential->toArray()
        );
    }

    public function sendVerifyEmail(string $userId, ?string $clientId = null, ?string $redirectUri = null): void
    {
        $params = [];
        if ($clientId !== null) {
            $params['client_id'] = $clientId;
        }
        if ($redirectUri !== null) {
            $params['redirect_uri'] = $redirectUri;
        }

        $path = sprintf('/admin/realms/%s/users/%s/send-verify-email', rawurlencode($this->realm), rawurlencode($userId));
        if (!empty($params)) {
            $path .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        $this->sendPut($path);
    }

    /**
     * @param list<RequiredAction|string> $actions
     */
    public function executeActionsEmail(
        string $userId,
        array $actions,
        ?int $lifespan = null,
        ?string $clientId = null,
        ?string $redirectUri = null
    ): void {
        $formatted = array_map(
            static fn(RequiredAction|string $a) => $a instanceof RequiredAction ? $a->value : $a,
            $actions
        );

        $params = [];
        if ($lifespan !== null) {
            $params['lifespan'] = (string) $lifespan;
        }
        if ($clientId !== null) {
            $params['client_id'] = $clientId;
        }
        if ($redirectUri !== null) {
            $params['redirect_uri'] = $redirectUri;
        }

        $path = sprintf('/admin/realms/%s/users/%s/execute-actions-email', rawurlencode($this->realm), rawurlencode($userId));
        if (!empty($params)) {
            $path .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        $this->sendPut($path, array_values($formatted));
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

    public function logout(string $userId): void
    {
        $this->sendPost(
            sprintf('/admin/realms/%s/users/%s/logout', rawurlencode($this->realm), rawurlencode($userId))
        );
    }

    /**
     * @return list<GroupRepresentation>
     */
    public function getUserGroups(string $userId): array
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/users/%s/groups', rawurlencode($this->realm), rawurlencode($userId))
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => GroupRepresentation::fromArray($item),
            $data
        );
    }

    public function joinGroup(string $userId, string $groupId): void
    {
        $this->sendPut(
            sprintf('/admin/realms/%s/users/%s/groups/%s', rawurlencode($this->realm), rawurlencode($userId), rawurlencode($groupId))
        );
    }

    public function leaveGroup(string $userId, string $groupId): void
    {
        $this->sendDelete(
            sprintf('/admin/realms/%s/users/%s/groups/%s', rawurlencode($this->realm), rawurlencode($userId), rawurlencode($groupId))
        );
    }

    /**
     * @return list<RoleRepresentation>
     */
    public function getRealmRoleMappings(string $userId): array
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/users/%s/role-mappings/realm', rawurlencode($this->realm), rawurlencode($userId))
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => RoleRepresentation::fromArray($item),
            $data
        );
    }

    /**
     * @param list<RoleRepresentation> $roles
     */
    public function addRealmRoleMappings(string $userId, array $roles): void
    {
        $body = array_map(static fn(RoleRepresentation $r) => $r->toArray(), $roles);

        $this->sendPost(
            sprintf('/admin/realms/%s/users/%s/role-mappings/realm', rawurlencode($this->realm), rawurlencode($userId)),
            $body
        );
    }

    /**
     * @param list<RoleRepresentation> $roles
     */
    public function deleteRealmRoleMappings(string $userId, array $roles): void
    {
        $body = array_map(static fn(RoleRepresentation $r) => $r->toArray(), $roles);

        // Custom delete with body
        $this->httpClient->delete(
            sprintf('/admin/realms/%s/users/%s/role-mappings/realm', rawurlencode($this->realm), rawurlencode($userId)),
            ['Authorization' => 'Bearer ' . $this->tokenManager->getAccessToken()]
        );
    }

    /**
     * @return list<RoleRepresentation>
     */
    public function getClientRoleMappings(string $userId, string $clientUuid): array
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/users/%s/role-mappings/clients/%s', rawurlencode($this->realm), rawurlencode($userId), rawurlencode($clientUuid))
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => RoleRepresentation::fromArray($item),
            $data
        );
    }

    /**
     * @param list<RoleRepresentation> $roles
     */
    public function addClientRoleMappings(string $userId, string $clientUuid, array $roles): void
    {
        $body = array_map(static fn(RoleRepresentation $r) => $r->toArray(), $roles);

        $this->sendPost(
            sprintf('/admin/realms/%s/users/%s/role-mappings/clients/%s', rawurlencode($this->realm), rawurlencode($userId), rawurlencode($clientUuid)),
            $body
        );
    }
}
