<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class GameQueryParams
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $categoryId = null,
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly ?string $status = null,
        public readonly ?string $orderBy = null,
        public readonly bool $descending = false,
        public readonly int $page = 1,
        public readonly int $pageSize = 20
    ) {
    }

    public static function fromArray(array $query): self
    {
        return new self(
            search: isset($query['search']) && $query['search'] !== '' ? (string) $query['search'] : null,
            categoryId: isset($query['categoryId']) && $query['categoryId'] !== '' ? (string) $query['categoryId'] : null,
            minPrice: isset($query['minPrice']) && $query['minPrice'] !== '' ? (float) $query['minPrice'] : null,
            maxPrice: isset($query['maxPrice']) && $query['maxPrice'] !== '' ? (float) $query['maxPrice'] : null,
            status: isset($query['status']) && $query['status'] !== '' ? (string) $query['status'] : null,
            orderBy: isset($query['orderBy']) && $query['orderBy'] !== '' ? (string) $query['orderBy'] : null,
            descending: filter_var($query['descending'] ?? false, FILTER_VALIDATE_BOOLEAN),
            page: max(1, (int) ($query['page'] ?? 1)),
            pageSize: min(100, max(1, (int) ($query['pageSize'] ?? 20)))
        );
    }
}
