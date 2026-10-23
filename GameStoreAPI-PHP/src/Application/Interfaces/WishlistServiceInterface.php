<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\AddToWishlistRequest;
use GameStore\Application\DTOs\Response\WishlistEntryResponse;

interface WishlistServiceInterface
{
    /** @return WishlistEntryResponse[] */
    public function getByCustomer(string $customerId): array;

    public function add(string $customerId, AddToWishlistRequest $request): void;

    public function remove(string $customerId, string $gameId): void;
}
