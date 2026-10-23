<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class AddToWishlistRequest
{
    public function __construct(
        public readonly string $gameId,
        public readonly bool $notifyOnDiscount = true
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            gameId: (string) ($data['gameId'] ?? ''),
            notifyOnDiscount: filter_var($data['notifyOnDiscount'] ?? true, FILTER_VALIDATE_BOOLEAN)
        );
    }
}
