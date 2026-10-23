<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class CreatePurchaseRequest
{
    /** @param string[] $gameIds */
    public function __construct(
        public readonly array $gameIds,
        public readonly ?string $paymentMethod = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        $gameIds = $data['gameIds'] ?? [];
        return new self(
            gameIds: is_array($gameIds) ? array_values(array_map('strval', $gameIds)) : [],
            paymentMethod: isset($data['paymentMethod']) && $data['paymentMethod'] !== ''
                ? (string) $data['paymentMethod']
                : null
        );
    }
}
