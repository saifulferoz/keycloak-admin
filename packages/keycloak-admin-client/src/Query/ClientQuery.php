<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Query;

final class ClientQuery
{
    private ?string $clientId = null;
    private ?string $search = null;
    private ?bool $viewableOnly = null;
    private ?int $first = null;
    private ?int $max = null;

    public static function create(): self
    {
        return new self();
    }

    public function withClientId(?string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function withSearch(?string $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function withViewableOnly(?bool $viewableOnly): self
    {
        $this->viewableOnly = $viewableOnly;
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

        if ($this->clientId !== null) {
            $params['clientId'] = $this->clientId;
        }
        if ($this->search !== null) {
            $params['search'] = $this->search;
        }
        if ($this->viewableOnly !== null) {
            $params['viewableOnly'] = $this->viewableOnly ? 'true' : 'false';
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
