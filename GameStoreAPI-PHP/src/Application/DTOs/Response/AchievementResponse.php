<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class AchievementResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $name,
        public readonly string $description,
        public readonly string $earnedAt
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'earnedAt' => $this->earnedAt,
        ];
    }
}
