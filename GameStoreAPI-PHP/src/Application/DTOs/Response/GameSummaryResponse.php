<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class GameSummaryResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $developer,
        public readonly string $coverImageUrl,
        public readonly float $price,
        public readonly ?float $discountPrice,
        public readonly float $currentPrice,
        public readonly float $averageRating,
        public readonly int $reviewCount,
        public readonly string $categoryName,
        public readonly string $status,
        public readonly string $releaseDate
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'developer' => $this->developer,
            'coverImageUrl' => $this->coverImageUrl,
            'price' => $this->price,
            'discountPrice' => $this->discountPrice,
            'currentPrice' => $this->currentPrice,
            'averageRating' => $this->averageRating,
            'reviewCount' => $this->reviewCount,
            'categoryName' => $this->categoryName,
            'status' => $this->status,
            'releaseDate' => $this->releaseDate,
        ];
    }
}
