<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class GameDetailResponse implements \JsonSerializable
{
    /** @param string[] $tags */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $developer,
        public readonly string $publisher,
        public readonly float $price,
        public readonly ?float $discountPrice,
        public readonly float $currentPrice,
        public readonly string $releaseDate,
        public readonly string $coverImageUrl,
        public readonly string $status,
        public readonly array $tags,
        public readonly string $categoryId,
        public readonly string $categoryName,
        public readonly float $averageRating,
        public readonly int $reviewCount,
        public readonly bool $isRecommendedByMajority,
        public readonly string $createdAt
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'developer' => $this->developer,
            'publisher' => $this->publisher,
            'price' => $this->price,
            'discountPrice' => $this->discountPrice,
            'currentPrice' => $this->currentPrice,
            'releaseDate' => $this->releaseDate,
            'coverImageUrl' => $this->coverImageUrl,
            'status' => $this->status,
            'tags' => $this->tags,
            'categoryId' => $this->categoryId,
            'categoryName' => $this->categoryName,
            'averageRating' => $this->averageRating,
            'reviewCount' => $this->reviewCount,
            'isRecommendedByMajority' => $this->isRecommendedByMajority,
            'createdAt' => $this->createdAt,
        ];
    }
}
