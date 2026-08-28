<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Model\User;

use Feroz\KeycloakAdmin\Model\Enum\RequiredAction;

final readonly class UserRepresentation
{
    /**
     * @param list<string>|null $realmRoles
     * @param array<string, list<string>>|null $clientRoles
     * @param list<string>|null $groups
     * @param list<CredentialRepresentation>|null $credentials
     * @param list<string>|null $requiredActions
     * @param array<string, list<string>>|null $attributes
     * @param array<string, mixed>|null $access
     */
    public function __construct(
        public ?string $id = null,
        public ?string $username = null,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?bool $enabled = true,
        public ?bool $emailVerified = false,
        public ?int $createdTimestamp = null,
        public ?array $realmRoles = null,
        public ?array $clientRoles = null,
        public ?array $groups = null,
        public ?array $credentials = null,
        public ?array $requiredActions = null,
        public ?array $attributes = null,
        public ?string $federationLink = null,
        public ?array $access = null
    ) {
    }

    public static function create(?string $email = null, ?string $username = null): self
    {
        return new self(
            username: $username ?? $email,
            email: $email,
            enabled: true
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $credentials = null;
        if (isset($data['credentials']) && is_array($data['credentials'])) {
            $credentials = array_map(
                static fn(array $c) => CredentialRepresentation::fromArray($c),
                $data['credentials']
            );
        }

        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            username: isset($data['username']) ? (string) $data['username'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            firstName: isset($data['firstName']) ? (string) $data['firstName'] : null,
            lastName: isset($data['lastName']) ? (string) $data['lastName'] : null,
            enabled: isset($data['enabled']) ? (bool) $data['enabled'] : true,
            emailVerified: isset($data['emailVerified']) ? (bool) $data['emailVerified'] : false,
            createdTimestamp: isset($data['createdTimestamp']) ? (int) $data['createdTimestamp'] : null,
            realmRoles: isset($data['realmRoles']) && is_array($data['realmRoles']) ? array_values($data['realmRoles']) : null,
            clientRoles: isset($data['clientRoles']) && is_array($data['clientRoles']) ? $data['clientRoles'] : null,
            groups: isset($data['groups']) && is_array($data['groups']) ? array_values($data['groups']) : null,
            credentials: $credentials,
            requiredActions: isset($data['requiredActions']) && is_array($data['requiredActions']) ? array_values($data['requiredActions']) : null,
            attributes: isset($data['attributes']) && is_array($data['attributes']) ? $data['attributes'] : null,
            federationLink: isset($data['federationLink']) ? (string) $data['federationLink'] : null,
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
        if ($this->username !== null) {
            $data['username'] = $this->username;
        }
        if ($this->email !== null) {
            $data['email'] = $this->email;
        }
        if ($this->firstName !== null) {
            $data['firstName'] = $this->firstName;
        }
        if ($this->lastName !== null) {
            $data['lastName'] = $this->lastName;
        }
        if ($this->enabled !== null) {
            $data['enabled'] = $this->enabled;
        }
        if ($this->emailVerified !== null) {
            $data['emailVerified'] = $this->emailVerified;
        }
        if ($this->createdTimestamp !== null) {
            $data['createdTimestamp'] = $this->createdTimestamp;
        }
        if ($this->realmRoles !== null) {
            $data['realmRoles'] = $this->realmRoles;
        }
        if ($this->clientRoles !== null) {
            $data['clientRoles'] = $this->clientRoles;
        }
        if ($this->groups !== null) {
            $data['groups'] = $this->groups;
        }
        if ($this->credentials !== null) {
            $data['credentials'] = array_map(static fn(CredentialRepresentation $c) => $c->toArray(), $this->credentials);
        }
        if ($this->requiredActions !== null) {
            $data['requiredActions'] = $this->requiredActions;
        }
        if ($this->attributes !== null) {
            $data['attributes'] = $this->attributes;
        }
        if ($this->federationLink !== null) {
            $data['federationLink'] = $this->federationLink;
        }
        if ($this->access !== null) {
            $data['access'] = $this->access;
        }

        return $data;
    }

    public function withUsername(string $username): self
    {
        return new self(
            id: $this->id,
            username: $username,
            email: $this->email,
            firstName: $this->firstName,
            lastName: $this->lastName,
            enabled: $this->enabled,
            emailVerified: $this->emailVerified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $this->credentials,
            requiredActions: $this->requiredActions,
            attributes: $this->attributes,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }

    public function withEmail(string $email, bool $verified = false): self
    {
        return new self(
            id: $this->id,
            username: $this->username,
            email: $email,
            firstName: $this->firstName,
            lastName: $this->lastName,
            enabled: $this->enabled,
            emailVerified: $verified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $this->credentials,
            requiredActions: $this->requiredActions,
            attributes: $this->attributes,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }

    public function withEmailVerified(bool $verified = true): self
    {
        return new self(
            id: $this->id,
            username: $this->username,
            email: $this->email,
            firstName: $this->firstName,
            lastName: $this->lastName,
            enabled: $this->enabled,
            emailVerified: $verified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $this->credentials,
            requiredActions: $this->requiredActions,
            attributes: $this->attributes,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }

    public function withEnabled(bool $enabled = true): self
    {
        return new self(
            id: $this->id,
            username: $this->username,
            email: $this->email,
            firstName: $this->firstName,
            lastName: $this->lastName,
            enabled: $enabled,
            emailVerified: $this->emailVerified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $this->credentials,
            requiredActions: $this->requiredActions,
            attributes: $this->attributes,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }

    public function withName(string $firstName, string $lastName): self
    {
        return new self(
            id: $this->id,
            username: $this->username,
            email: $this->email,
            firstName: $firstName,
            lastName: $lastName,
            enabled: $this->enabled,
            emailVerified: $this->emailVerified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $this->credentials,
            requiredActions: $this->requiredActions,
            attributes: $this->attributes,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }

    /**
     * @param list<string>|string $value
     */
    public function withAttribute(string $name, array|string $value): self
    {
        $attrs = $this->attributes ?? [];
        $attrs[$name] = is_array($value) ? array_values($value) : [$value];

        return new self(
            id: $this->id,
            username: $this->username,
            email: $this->email,
            firstName: $this->firstName,
            lastName: $this->lastName,
            enabled: $this->enabled,
            emailVerified: $this->emailVerified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $this->credentials,
            requiredActions: $this->requiredActions,
            attributes: $attrs,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }

    /**
     * @param list<RequiredAction|string> $actions
     */
    public function withRequiredActions(array $actions): self
    {
        $formatted = array_map(
            static fn(RequiredAction|string $a) => $a instanceof RequiredAction ? $a->value : $a,
            $actions
        );

        return new self(
            id: $this->id,
            username: $this->username,
            email: $this->email,
            firstName: $this->firstName,
            lastName: $this->lastName,
            enabled: $this->enabled,
            emailVerified: $this->emailVerified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $this->credentials,
            requiredActions: array_values($formatted),
            attributes: $this->attributes,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }

    public function withPassword(string $password, bool $temporary = false): self
    {
        $creds = $this->credentials ?? [];
        $creds[] = CredentialRepresentation::password($password, $temporary);

        return new self(
            id: $this->id,
            username: $this->username,
            email: $this->email,
            firstName: $this->firstName,
            lastName: $this->lastName,
            enabled: $this->enabled,
            emailVerified: $this->emailVerified,
            createdTimestamp: $this->createdTimestamp,
            realmRoles: $this->realmRoles,
            clientRoles: $this->clientRoles,
            groups: $this->groups,
            credentials: $creds,
            requiredActions: $this->requiredActions,
            attributes: $this->attributes,
            federationLink: $this->federationLink,
            access: $this->access
        );
    }
}
