<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\CreatePurchaseRequest;
use GameStore\Application\DTOs\Response\PurchaseDetailResponse;
use GameStore\Application\DTOs\Response\PurchaseSummaryResponse;

interface PurchaseServiceInterface
{
    public function create(string $customerId, CreatePurchaseRequest $request): PurchaseDetailResponse;

    public function getById(string $id, string $customerId): PurchaseDetailResponse;

    /** @return PurchaseSummaryResponse[] */
    public function getByCustomer(string $customerId): array;

    public function refund(string $id, string $customerId): void;
}
