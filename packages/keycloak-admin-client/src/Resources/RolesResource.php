<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Exception\Resource\ResourceNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\RoleNotFoundException;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\Role\RoleRepresentation;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Query\RoleQuery;

class RolesResource extends AbstractResource
{
    public function __construct(
        HttpClientInterface $httpClient,
        TokenManagerInterface $tokenManager,
        private readonly string $realm
    ) {
        parent::__construct($httpClient, $tokenManager);
    }

    /**
     * @return list<RoleRepresentation>
     */
    public function allRealmRoles(?RoleQuery $query = null): array
    {
        $queryParams = $query !== null ? $query->toQueryParams() : [];
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/roles', rawurlencode($this->realm)),
            $queryParams
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

    public function getRealmRole(string $roleName): RoleRepresentation
    {
        try {
            $response = $this->sendGet(sprintf('/admin/realms/%s/roles/%s', rawurlencode($this->realm), rawurlencode($roleName)));
        } catch (ResourceNotFoundException) {
            throw new RoleNotFoundException($roleName);
        }

        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return RoleRepresentation::fromArray($data);
    }

    public function createRealmRole(RoleRepresentation $role): void
    {
        $this->sendPost(
            sprintf('/admin/realms/%s/roles', rawurlencode($this->realm)),
            $role->toArray()
        );
    }

    public function updateRealmRole(string $roleName, RoleRepresentation $role): void
    {
        $this->sendPut(
            sprintf('/admin/realms/%s/roles/%s', rawurlencode($this->realm), rawurlencode($roleName)),
            $role->toArray()
        );
    }

    public function deleteRealmRole(string $roleName): void
    {
        $this->sendDelete(sprintf('/admin/realms/%s/roles/%s', rawurlencode($this->realm), rawurlencode($roleName)));
    }

    /**
     * @return list<UserRepresentation>
     */
    public function getRoleUsers(string $roleName, ?int $first = null, ?int $max = null): array
    {
        $params = [];
        if ($first !== null) {
            $params['first'] = (string) $first;
        }
        if ($max !== null) {
            $params['max'] = (string) $max;
        }

        $response = $this->sendGet(
            sprintf('/admin/realms/%s/roles/%s/users', rawurlencode($this->realm), rawurlencode($roleName)),
            $params
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => UserRepresentation::fromArray($item),
            $data
        );
    }

    /**
     * @return list<RoleRepresentation>
     */
    public function allClientRoles(string $clientUuid, ?RoleQuery $query = null): array
    {
        $queryParams = $query !== null ? $query->toQueryParams() : [];
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/clients/%s/roles', rawurlencode($this->realm), rawurlencode($clientUuid)),
            $queryParams
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

    public function getClientRole(string $clientUuid, string $roleName): RoleRepresentation
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/clients/%s/roles/%s', rawurlencode($this->realm), rawurlencode($clientUuid), rawurlencode($roleName))
        );

        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return RoleRepresentation::fromArray($data);
    }

    public function createClientRole(string $clientUuid, RoleRepresentation $role): void
    {
        $this->sendPost(
            sprintf('/admin/realms/%s/clients/%s/roles', rawurlencode($this->realm), rawurlencode($clientUuid)),
            $role->toArray()
        );
    }

    public function updateClientRole(string $clientUuid, string $roleName, RoleRepresentation $role): void
    {
        $this->sendPut(
            sprintf('/admin/realms/%s/clients/%s/roles/%s', rawurlencode($this->realm), rawurlencode($clientUuid), rawurlencode($roleName)),
            $role->toArray()
        );
    }

    public function deleteClientRole(string $clientUuid, string $roleName): void
    {
        $this->sendDelete(
            sprintf('/admin/realms/%s/clients/%s/roles/%s', rawurlencode($this->realm), rawurlencode($clientUuid), rawurlencode($roleName))
        );
    }
}
