<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Query;

final class RoleQuery
{
    private ?string $search = null;
    private ?bool $briefRepresentation = null;
    private ?int $first = null;
    private ?int $max = null;

    public static function create(): self
    {
        return new self();
    }

    public function withSearch(?string $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function withBriefRepresentation(?bool $briefRepresentation): self
    {
        $this->briefRepresentation = $briefRepresentation;
        return $this;
    }

    public function withPagination(int $page, int $perPage): self
    {
        $this->first = max(0, ($page - 1) * $perPage);
        $this->max = $perPage;
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
        if ($this->briefRepresentation !== null) {
            $params['briefRepresentation'] = $this->briefRepresentation ? 'true' : 'false';
        }
        if ($this->first !== null) {
            $params['first'] = (string) $this->first;
        }
        if ($this->max !== null) {
            $params['max'] = (string) $this->max;
        }

        return $params;
    }
}
