<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Auth\TokenManagerInterface;
use Feroz\KeycloakAdmin\Http\HttpClientInterface;
use Feroz\KeycloakAdmin\Model\Group\GroupRepresentation;
use Feroz\KeycloakAdmin\Model\Role\RoleRepresentation;
use Feroz\KeycloakAdmin\Model\User\UserRepresentation;
use Feroz\KeycloakAdmin\Query\GroupQuery;
use Feroz\KeycloakAdmin\Query\PaginatedResult;

class GroupsResource extends AbstractResource
{
    public function __construct(
        HttpClientInterface $httpClient,
        TokenManagerInterface $tokenManager,
        private readonly string $realm
    ) {
        parent::__construct($httpClient, $tokenManager);
    }

    /**
     * @return PaginatedResult<GroupRepresentation>
     */
    public function list(?GroupQuery $query = null): PaginatedResult
    {
        $queryParams = $query !== null ? $query->toQueryParams() : [];
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/groups', rawurlencode($this->realm)),
            $queryParams
        );

        $data = $response->getData();
        if (!is_array($data)) {
            return new PaginatedResult([]);
        }

        $items = array_map(
            static fn(array $item) => GroupRepresentation::fromArray($item),
            $data
        );

        return new PaginatedResult($items);
    }

    public function get(string $groupId): GroupRepresentation
    {
        $response = $this->sendGet(sprintf('/admin/realms/%s/groups/%s', rawurlencode($this->realm), rawurlencode($groupId)));
        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return GroupRepresentation::fromArray($data);
    }

    public function create(GroupRepresentation $group): string
    {
        $response = $this->sendPost(
            sprintf('/admin/realms/%s/groups', rawurlencode($this->realm)),
            $group->toArray()
        );

        return $response->getCreatedResourceId() ?? '';
    }

    public function createChildGroup(string $parentGroupId, GroupRepresentation $group): string
    {
        $response = $this->sendPost(
            sprintf('/admin/realms/%s/groups/%s/children', rawurlencode($this->realm), rawurlencode($parentGroupId)),
            $group->toArray()
        );

        return $response->getCreatedResourceId() ?? '';
    }

    public function update(string $groupId, GroupRepresentation $group): void
    {
        $this->sendPut(
            sprintf('/admin/realms/%s/groups/%s', rawurlencode($this->realm), rawurlencode($groupId)),
            $group->toArray()
        );
    }

    public function delete(string $groupId): void
    {
        $this->sendDelete(sprintf('/admin/realms/%s/groups/%s', rawurlencode($this->realm), rawurlencode($groupId)));
    }

    /**
     * @return list<UserRepresentation>
     */
    public function getMembers(string $groupId, ?int $first = null, ?int $max = null): array
    {
        $params = [];
        if ($first !== null) {
            $params['first'] = (string) $first;
        }
        if ($max !== null) {
            $params['max'] = (string) $max;
        }

        $response = $this->sendGet(
            sprintf('/admin/realms/%s/groups/%s/members', rawurlencode($this->realm), rawurlencode($groupId)),
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
    public function getRealmRoleMappings(string $groupId): array
    {
        $response = $this->sendGet(
            sprintf('/admin/realms/%s/groups/%s/role-mappings/realm', rawurlencode($this->realm), rawurlencode($groupId))
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
    public function addRealmRoleMappings(string $groupId, array $roles): void
    {
        $body = array_map(static fn(RoleRepresentation $r) => $r->toArray(), $roles);

        $this->sendPost(
            sprintf('/admin/realms/%s/groups/%s/role-mappings/realm', rawurlencode($this->realm), rawurlencode($groupId)),
            $body
        );
    }
}
