<?php

declare(strict_types=1);

namespace GameStore\Domain\Common;

final class PagedResult
{
    /** @param array<int,mixed> $items */
    public function __construct(
        public readonly array $items,
        public readonly int $totalCount,
        public readonly int $page,
        public readonly int $pageSize
    ) {
    }

    public function totalPages(): int
    {
        return $this->pageSize > 0 ? (int) ceil($this->totalCount / $this->pageSize) : 0;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }
}
