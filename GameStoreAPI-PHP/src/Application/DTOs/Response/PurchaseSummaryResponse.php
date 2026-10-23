<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class PurchaseSummaryResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly float $totalAmount,
        public readonly string $status,
        public readonly ?string $paymentMethod,
        public readonly int $itemCount,
        public readonly string $purchasedAt
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'totalAmount' => $this->totalAmount,
            'status' => $this->status,
            'paymentMethod' => $this->paymentMethod,
            'itemCount' => $this->itemCount,
            'purchasedAt' => $this->purchasedAt,
        ];
    }
}
