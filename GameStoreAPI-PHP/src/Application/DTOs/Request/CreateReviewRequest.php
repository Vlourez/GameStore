<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class CreateReviewRequest
{
    public function __construct(
        public readonly string $gameId,
        public readonly int $rating,
        public readonly string $title,
        public readonly string $body,
        public readonly bool $isRecommended
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            gameId: (string) ($data['gameId'] ?? ''),
            rating: (int) ($data['rating'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            body: (string) ($data['body'] ?? ''),
            isRecommended: filter_var($data['isRecommended'] ?? false, FILTER_VALIDATE_BOOLEAN)
        );
    }
}
