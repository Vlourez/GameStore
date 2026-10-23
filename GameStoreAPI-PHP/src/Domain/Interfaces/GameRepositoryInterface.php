<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Common\PagedResult;
use GameStore\Domain\Entities\Game;

interface GameRepositoryInterface
{
    public function getById(string $id): ?Game;

    /** @return Game[] */
    public function getAll(): array;

    public function add(Game $game): void;

    public function update(Game $game): void;

    public function remove(Game $game): void;

    public function getByIdWithDetails(string $id): ?Game;

    /** @return Game[] */
    public function getByCategory(string $categoryId): array;

    /** @return Game[] */
    public function getRecommendedForCustomer(string $customerId, int $top = 10): array;

    /** @return Game[] */
    public function search(string $query): array;

    public function existsInLibrary(string $customerId, string $gameId): bool;

    /**
     * @param array<string,mixed> $filters Supported keys: categoryId, minPrice, maxPrice, status, search
     */
    public function getPaged(
        int $page,
        int $pageSize,
        array $filters = [],
        ?string $orderBy = null,
        bool $descending = false
    ): PagedResult;
}
