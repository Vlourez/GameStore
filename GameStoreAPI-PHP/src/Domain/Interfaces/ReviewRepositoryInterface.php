<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Common\PagedResult;
use GameStore\Domain\Entities\Review;

interface ReviewRepositoryInterface
{
    public function getById(string $id): ?Review;

    public function add(Review $review): void;

    public function update(Review $review): void;

    public function remove(Review $review): void;

    public function getByCustomerAndGame(string $customerId, string $gameId): ?Review;

    /** @return Review[] */
    public function getByGame(string $gameId): array;

    public function getPaged(int $page, int $pageSize, string $gameId): PagedResult;
}
