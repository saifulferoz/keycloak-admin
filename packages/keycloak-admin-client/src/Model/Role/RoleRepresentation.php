<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\Role;

final readonly class RoleRepresentation
{
    /**
     * @param array<string, list<string>>|null $attributes
     */
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?bool $composite = false,
        public ?bool $clientRole = false,
        public ?string $containerId = null,
        public ?array $attributes = null
    ) {
    }

    public static function create(string $name, ?string $description = null): self
    {
        return new self(
            name: $name,
            description: $description
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            name: isset($data['name']) ? (string) $data['name'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            composite: isset($data['composite']) ? (bool) $data['composite'] : false,
            clientRole: isset($data['clientRole']) ? (bool) $data['clientRole'] : false,
            containerId: isset($data['containerId']) ? (string) $data['containerId'] : null,
            attributes: isset($data['attributes']) && is_array($data['attributes']) ? $data['attributes'] : null
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
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        if ($this->composite !== null) {
            $data['composite'] = $this->composite;
        }
        if ($this->clientRole !== null) {
            $data['clientRole'] = $this->clientRole;
        }
        if ($this->containerId !== null) {
            $data['containerId'] = $this->containerId;
        }
        if ($this->attributes !== null) {
            $data['attributes'] = $this->attributes;
        }

        return $data;
    }
}
