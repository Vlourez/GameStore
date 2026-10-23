<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class CreateCategoryRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $slug
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            slug: (string) ($data['slug'] ?? '')
        );
    }
}
