<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class PagedResponse implements \JsonSerializable
{
    /** @param array<int,mixed> $items */
    public function __construct(
        public readonly array $items,
        public readonly int $totalCount,
        public readonly int $page,
        public readonly int $pageSize,
        public readonly int $totalPages,
        public readonly bool $hasNextPage,
        public readonly bool $hasPreviousPage
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'totalCount' => $this->totalCount,
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'totalPages' => $this->totalPages,
            'hasNextPage' => $this->hasNextPage,
            'hasPreviousPage' => $this->hasPreviousPage,
        ];
    }
}
