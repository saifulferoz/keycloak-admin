<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\AttackDetection;

final readonly class UserStatusRepresentation
{
    public function __construct(
        public int $numFailures = 0,
        public bool $disabled = false,
        public ?int $lastFailure = null,
        public ?string $lastIPFailure = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            numFailures: isset($data['numFailures']) ? (int) $data['numFailures'] : 0,
            disabled: isset($data['disabled']) ? (bool) $data['disabled'] : false,
            lastFailure: isset($data['lastFailure']) ? (int) $data['lastFailure'] : null,
            lastIPFailure: isset($data['lastIPFailure']) ? (string) $data['lastIPFailure'] : null
        );
    }
}
