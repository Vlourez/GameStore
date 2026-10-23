<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class RecordPlaySessionRequest
{
    public function __construct(
        public readonly string $gameId,
        public readonly int $minutes
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            gameId: (string) ($data['gameId'] ?? ''),
            minutes: (int) ($data['minutes'] ?? 0)
        );
    }
}
