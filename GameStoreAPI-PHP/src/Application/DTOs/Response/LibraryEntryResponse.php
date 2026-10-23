<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class LibraryEntryResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $gameId,
        public readonly string $gameTitle,
        public readonly string $gameCoverUrl,
        public readonly string $acquiredAt,
        public readonly ?string $lastPlayedAt,
        public readonly int $totalPlaytimeMinutes
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'gameId' => $this->gameId,
            'gameTitle' => $this->gameTitle,
            'gameCoverUrl' => $this->gameCoverUrl,
            'acquiredAt' => $this->acquiredAt,
            'lastPlayedAt' => $this->lastPlayedAt,
            'totalPlaytimeMinutes' => $this->totalPlaytimeMinutes,
        ];
    }
}
