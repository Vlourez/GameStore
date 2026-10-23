<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class CategoryResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $slug,
        public readonly int $gameCount
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'gameCount' => $this->gameCount,
        ];
    }
}
