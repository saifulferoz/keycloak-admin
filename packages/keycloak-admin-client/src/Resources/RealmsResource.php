<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Resources;

use Feroz\KeycloakAdmin\Exception\Resource\RealmNotFoundException;
use Feroz\KeycloakAdmin\Exception\Resource\ResourceNotFoundException;
use Feroz\KeycloakAdmin\Model\Realm\RealmRepresentation;

class RealmsResource extends AbstractResource
{
    /**
     * @return list<RealmRepresentation>
     */
    public function all(): array
    {
        $response = $this->sendGet('/admin/realms');
        $data = $response->getData();

        if (!is_array($data)) {
            return [];
        }

        return array_map(
            static fn(array $item) => RealmRepresentation::fromArray($item),
            $data
        );
    }

    public function get(string $realmName): RealmRepresentation
    {
        try {
            $response = $this->sendGet(sprintf('/admin/realms/%s', rawurlencode($realmName)));
        } catch (ResourceNotFoundException) {
            throw new RealmNotFoundException($realmName);
        }

        /** @var array<string, mixed> $data */
        $data = $response->getData();
        return RealmRepresentation::fromArray($data);
    }

    public function create(RealmRepresentation $realm): void
    {
        $this->sendPost('/admin/realms', $realm->toArray());
    }

    public function update(string $realmName, RealmRepresentation $realm): void
    {
        $this->sendPut(sprintf('/admin/realms/%s', rawurlencode($realmName)), $realm->toArray());
    }

    public function delete(string $realmName): void
    {
        $this->sendDelete(sprintf('/admin/realms/%s', rawurlencode($realmName)));
    }

    public function clearUserCache(string $realmName): void
    {
        $this->sendPost(sprintf('/admin/realms/%s/clear-user-cache', rawurlencode($realmName)));
    }

    public function clearRealmCache(string $realmName): void
    {
        $this->sendPost(sprintf('/admin/realms/%s/clear-realm-cache', rawurlencode($realmName)));
    }

    public function clearKeysCache(string $realmName): void
    {
        $this->sendPost(sprintf('/admin/realms/%s/clear-keys-cache', rawurlencode($realmName)));
    }
}
