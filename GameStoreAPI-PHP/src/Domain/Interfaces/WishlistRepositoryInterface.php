<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Entities\WishlistEntry;

interface WishlistRepositoryInterface
{
    public function add(WishlistEntry $entry): void;

    public function remove(WishlistEntry $entry): void;

    public function getEntry(string $customerId, string $gameId): ?WishlistEntry;

    /** @return WishlistEntry[] */
    public function getByCustomer(string $customerId): array;
}
