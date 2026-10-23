<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class ApplyDiscountRequest
{
    public function __construct(
        public readonly float $discountPrice
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(discountPrice: (float) ($data['discountPrice'] ?? 0));
    }
}
