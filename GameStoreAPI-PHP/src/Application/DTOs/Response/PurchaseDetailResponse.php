<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class PurchaseDetailResponse implements \JsonSerializable
{
    /** @param PurchaseItemResponse[] $items */
    public function __construct(
        public readonly string $id,
        public readonly float $totalAmount,
        public readonly string $status,
        public readonly ?string $paymentMethod,
        public readonly string $purchasedAt,
        public readonly array $items
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'totalAmount' => $this->totalAmount,
            'status' => $this->status,
            'paymentMethod' => $this->paymentMethod,
            'purchasedAt' => $this->purchasedAt,
            'items' => $this->items,
        ];
    }
}
