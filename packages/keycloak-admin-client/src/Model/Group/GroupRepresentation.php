<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\Group;

final readonly class GroupRepresentation
{
    /**
     * @param list<GroupRepresentation>|null $subGroups
     * @param list<string>|null $realmRoles
     * @param array<string, list<string>>|null $clientRoles
     * @param array<string, list<string>>|null $attributes
     * @param array<string, mixed>|null $access
     */
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $path = null,
        public ?array $subGroups = null,
        public ?array $realmRoles = null,
        public ?array $clientRoles = null,
        public ?array $attributes = null,
        public ?array $access = null
    ) {
    }

    public static function create(string $name): self
    {
        return new self(name: $name);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $subGroups = null;
        if (isset($data['subGroups']) && is_array($data['subGroups'])) {
            $subGroups = array_map(
                static fn(array $g) => self::fromArray($g),
                $data['subGroups']
            );
        }

        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            name: isset($data['name']) ? (string) $data['name'] : null,
            path: isset($data['path']) ? (string) $data['path'] : null,
            subGroups: $subGroups,
            realmRoles: isset($data['realmRoles']) && is_array($data['realmRoles']) ? array_values($data['realmRoles']) : null,
            clientRoles: isset($data['clientRoles']) && is_array($data['clientRoles']) ? $data['clientRoles'] : null,
            attributes: isset($data['attributes']) && is_array($data['attributes']) ? $data['attributes'] : null,
            access: isset($data['access']) && is_array($data['access']) ? $data['access'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->id !== null) {
            $data['id'] = $this->id;
        }
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->path !== null) {
            $data['path'] = $this->path;
        }
        if ($this->subGroups !== null) {
            $data['subGroups'] = array_map(static fn(GroupRepresentation $g) => $g->toArray(), $this->subGroups);
        }
        if ($this->realmRoles !== null) {
            $data['realmRoles'] = $this->realmRoles;
        }
        if ($this->clientRoles !== null) {
            $data['clientRoles'] = $this->clientRoles;
        }
        if ($this->attributes !== null) {
            $data['attributes'] = $this->attributes;
        }
        if ($this->access !== null) {
            $data['access'] = $this->access;
        }

        return $data;
    }
}
