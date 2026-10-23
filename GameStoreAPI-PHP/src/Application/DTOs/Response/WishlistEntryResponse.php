<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class WishlistEntryResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $gameId,
        public readonly string $gameTitle,
        public readonly string $gameCoverUrl,
        public readonly float $currentPrice,
        public readonly bool $notifyOnDiscount,
        public readonly string $addedAt
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'gameId' => $this->gameId,
            'gameTitle' => $this->gameTitle,
            'gameCoverUrl' => $this->gameCoverUrl,
            'currentPrice' => $this->currentPrice,
            'notifyOnDiscount' => $this->notifyOnDiscount,
            'addedAt' => $this->addedAt,
        ];
    }
}
