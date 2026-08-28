<?php

declare(strict_types=1);

namespace Feroz\KeycloakAdmin\Query;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T
 * @implements IteratorAggregate<int, T>
 */
final readonly class PaginatedResult implements IteratorAggregate, Countable
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        private array $items,
        private ?int $total = null,
        private ?int $first = null,
        private ?int $max = null
    ) {
    }

    /**
     * @return list<T>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function getFirst(): ?int
    {
        return $this->first;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
