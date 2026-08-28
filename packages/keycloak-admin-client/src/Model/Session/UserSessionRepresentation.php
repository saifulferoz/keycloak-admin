<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\Session;

final readonly class UserSessionRepresentation
{
    /**
     * @param array<string, string>|null $clients
     */
    public function __construct(
        public ?string $id = null,
        public ?string $username = null,
        public ?string $userId = null,
        public ?string $ipAddress = null,
        public ?int $start = null,
        public ?int $lastAccess = null,
        public ?array $clients = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            username: isset($data['username']) ? (string) $data['username'] : null,
            userId: isset($data['userId']) ? (string) $data['userId'] : null,
            ipAddress: isset($data['ipAddress']) ? (string) $data['ipAddress'] : null,
            start: isset($data['start']) ? (int) $data['start'] : null,
            lastAccess: isset($data['lastAccess']) ? (int) $data['lastAccess'] : null,
            clients: isset($data['clients']) && is_array($data['clients']) ? $data['clients'] : null
        );
    }
}
