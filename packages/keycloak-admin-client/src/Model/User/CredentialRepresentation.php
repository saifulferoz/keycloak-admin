<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\User;

final readonly class CredentialRepresentation
{
    public function __construct(
        public ?string $id = null,
        public ?string $type = 'password',
        public ?string $value = null,
        public ?bool $temporary = false,
        public ?string $userLabel = null,
        public ?int $createdDate = null
    ) {
    }

    public static function password(string $value, bool $temporary = false): self
    {
        return new self(
            type: 'password',
            value: $value,
            temporary: $temporary
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            type: isset($data['type']) ? (string) $data['type'] : 'password',
            value: isset($data['value']) ? (string) $data['value'] : null,
            temporary: isset($data['temporary']) ? (bool) $data['temporary'] : false,
            userLabel: isset($data['userLabel']) ? (string) $data['userLabel'] : null,
            createdDate: isset($data['createdDate']) ? (int) $data['createdDate'] : null
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
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->value !== null) {
            $data['value'] = $this->value;
        }
        if ($this->temporary !== null) {
            $data['temporary'] = $this->temporary;
        }
        if ($this->userLabel !== null) {
            $data['userLabel'] = $this->userLabel;
        }
        if ($this->createdDate !== null) {
            $data['createdDate'] = $this->createdDate;
        }

        return $data;
    }
}
