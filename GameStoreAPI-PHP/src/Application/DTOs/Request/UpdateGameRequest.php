<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class UpdateGameRequest
{
    /** @param string[]|null $tags null = manter as tags atuais; [] = remover todas */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $developer,
        public readonly string $publisher,
        public readonly float $price,
        public readonly string $releaseDate,
        public readonly string $categoryId,
        public readonly string $coverImageUrl,
        public readonly ?array $tags = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        $tags = $data['tags'] ?? null;
        return new self(
            title: (string) ($data['title'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            developer: (string) ($data['developer'] ?? ''),
            publisher: (string) ($data['publisher'] ?? ''),
            price: (float) ($data['price'] ?? 0),
            releaseDate: (string) ($data['releaseDate'] ?? ''),
            categoryId: (string) ($data['categoryId'] ?? ''),
            coverImageUrl: (string) ($data['coverImageUrl'] ?? ''),
            tags: is_array($tags) ? array_map('strval', $tags) : null
        );
    }
}
