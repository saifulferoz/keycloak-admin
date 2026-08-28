<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Query;

final class GroupQuery
{
    private ?string $search = null;
    private ?bool $exact = null;
    private ?bool $briefRepresentation = null;
    private ?int $first = null;
    private ?int $max = null;
    private ?bool $populateHierarchy = null;

    public static function create(): self
    {
        return new self();
    }

    public function withSearch(?string $search): self
    {
        $this->search = $search;
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

    public function withPopulateHierarchy(?bool $populateHierarchy): self
    {
        $this->populateHierarchy = $populateHierarchy;
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
        if ($this->populateHierarchy !== null) {
            $params['populateHierarchy'] = $this->populateHierarchy ? 'true' : 'false';
        }

        return $params;
    }
}
