<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Entities\Purchase;

interface PurchaseRepositoryInterface
{
    public function getById(string $id): ?Purchase;

    public function add(Purchase $purchase): void;

    public function update(Purchase $purchase): void;

    public function getByIdWithItems(string $id): ?Purchase;

    /** @return Purchase[] */
    public function getByCustomer(string $customerId): array;
}
