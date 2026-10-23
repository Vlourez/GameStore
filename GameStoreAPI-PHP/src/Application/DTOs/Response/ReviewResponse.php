<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class ReviewResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $customerId,
        public readonly string $customerName,
        public readonly string $gameId,
        public readonly string $gameTitle,
        public readonly int $rating,
        public readonly string $title,
        public readonly string $body,
        public readonly bool $isRecommended,
        public readonly string $createdAt
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'customerName' => $this->customerName,
            'gameId' => $this->gameId,
            'gameTitle' => $this->gameTitle,
            'rating' => $this->rating,
            'title' => $this->title,
            'body' => $this->body,
            'isRecommended' => $this->isRecommended,
            'createdAt' => $this->createdAt,
        ];
    }
}
