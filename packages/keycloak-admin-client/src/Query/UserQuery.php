<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Query;

final class UserQuery
{
    private ?string $search = null;
    private ?string $username = null;
    private ?string $email = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?bool $enabled = null;
    private ?bool $emailVerified = null;
    private ?bool $exact = null;
    private ?bool $briefRepresentation = null;
    private ?int $first = null;
    private ?int $max = null;
    private ?string $idpAlias = null;
    private ?string $idpUserId = null;
    private ?string $q = null;

    public static function create(): self
    {
        return new self();
    }

    public function withSearch(?string $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function withUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function withEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function withFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function withLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function withEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function withEmailVerified(?bool $emailVerified): self
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function withExact(?bool $exact): self
    {
        $this->exact = $exact;
        return $this;
    }

    public function withBriefRepresentation(?bool $briefRepresentation): self
    {
        $this->briefRepresentation = $briefRepresentation;
        return $this;
    }

    public function withFirst(?int $first): self
    {
        $this->first = $first;
        return $this;
    }

    public function withMax(?int $max): self
    {
        $this->max = $max;
        return $this;
    }

    public function withPagination(int $page, int $perPage): self
    {
        $this->first = max(0, ($page - 1) * $perPage);
        $this->max = $perPage;
        return $this;
    }

    public function withIdpAlias(?string $idpAlias): self
    {
        $this->idpAlias = $idpAlias;
        return $this;
    }

    public function withIdpUserId(?string $idpUserId): self
    {
        $this->idpUserId = $idpUserId;
        return $this;
    }

    public function withAttributeFilter(string $attributeName, string $value): self
    {
        $this->q = sprintf('%s:%s', $attributeName, $value);
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->search !== null) {
            $params['search'] = $this->search;
        }
        if ($this->username !== null) {
            $params['username'] = $this->username;
        }
        if ($this->email !== null) {
            $params['email'] = $this->email;
        }
        if ($this->firstName !== null) {
            $params['firstName'] = $this->firstName;
        }
        if ($this->lastName !== null) {
            $params['lastName'] = $this->lastName;
        }
        if ($this->enabled !== null) {
            $params['enabled'] = $this->enabled ? 'true' : 'false';
        }
        if ($this->emailVerified !== null) {
            $params['emailVerified'] = $this->emailVerified ? 'true' : 'false';
        }
        if ($this->exact !== null) {
            $params['exact'] = $this->exact ? 'true' : 'false';
        }
        if ($this->briefRepresentation !== null) {
            $params['briefRepresentation'] = $this->briefRepresentation ? 'true' : 'false';
        }
        if ($this->first !== null) {
            $params['first'] = (string) $this->first;
        }
        if ($this->max !== null) {
            $params['max'] = (string) $this->max;
        }
        if ($this->idpAlias !== null) {
            $params['idpAlias'] = $this->idpAlias;
        }
        if ($this->idpUserId !== null) {
            $params['idpUserId'] = $this->idpUserId;
        }
        if ($this->q !== null) {
            $params['q'] = $this->q;
        }

        return $params;
    }
}
