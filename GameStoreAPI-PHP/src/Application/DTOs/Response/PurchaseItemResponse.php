<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class PurchaseItemResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $gameId,
        public readonly string $gameTitle,
        public readonly string $gameCoverUrl,
        public readonly float $unitPrice
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'gameId' => $this->gameId,
            'gameTitle' => $this->gameTitle,
            'gameCoverUrl' => $this->gameCoverUrl,
            'unitPrice' => $this->unitPrice,
        ];
    }
}
