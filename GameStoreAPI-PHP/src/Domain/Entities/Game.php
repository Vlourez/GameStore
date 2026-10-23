<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;
use GameStore\Domain\Common\DomainException;
use GameStore\Domain\Enums\GameStatus;

class Game extends BaseEntity
{
    private string $title;
    private string $description;
    private string $developer;
    private string $publisher;
    private float $price;
    private ?float $discountPrice = null;
    private string $releaseDate;
    private string $coverImageUrl = '';
    private GameStatus $status = GameStatus::Available;
    /** @var string[] */
    private array $tags = [];
    private string $categoryId;

    // Transient fields populated by repositories for read models
    private ?string $categoryName = null;
    private float $averageRating = 0.0;
    private int $reviewCount = 0;
    private int $recommendedReviewCount = 0;

    private function __construct()
    {
    }

    public static function create(
        string $title,
        string $description,
        string $developer,
        string $publisher,
        float $price,
        string $releaseDate,
        string $categoryId,
        string $coverImageUrl = ''
    ): self {
        $g = new self();
        $g->initNew();
        $g->title = $title;
        $g->description = $description;
        $g->developer = $developer;
        $g->publisher = $publisher;
        $g->price = $price;
        $g->releaseDate = $releaseDate;
        $g->categoryId = $categoryId;
        $g->coverImageUrl = $coverImageUrl;

        return $g;
    }

    /** @param array<string,mixed> $row */
    public static function hydrate(array $row): self
    {
        $g = new self();
        $g->id = $row['id'];
        $g->createdAt = $row['created_at'];
        $g->updatedAt = $row['updated_at'];
        $g->isDeleted = (bool) $row['is_deleted'];
        $g->title = $row['title'];
        $g->description = $row['description'];
        $g->developer = $row['developer'];
        $g->publisher = $row['publisher'];
        $g->price = (float) $row['price'];
        $g->discountPrice = $row['discount_price'] !== null ? (float) $row['discount_price'] : null;
        $g->releaseDate = $row['release_date'];
        $g->coverImageUrl = $row['cover_image_url'] ?? '';
        $g->status = GameStatus::from((int) $row['status']);
        $g->tags = $row['tags'] ? (json_decode($row['tags'], true) ?: []) : [];
        $g->categoryId = $row['category_id'];
        $g->categoryName = $row['category_name'] ?? null;
        $g->averageRating = isset($row['average_rating']) ? round((float) $row['average_rating'], 2) : 0.0;
        $g->reviewCount = (int) ($row['review_count'] ?? 0);
        $g->recommendedReviewCount = (int) ($row['recommended_review_count'] ?? 0);

        return $g;
    }

    public function update(
        string $title,
        string $description,
        string $developer,
        string $publisher,
        float $price,
        string $releaseDate,
        string $categoryId,
        string $coverImageUrl
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->developer = $developer;
        $this->publisher = $publisher;
        $this->price = $price;
        $this->releaseDate = $releaseDate;
        $this->categoryId = $categoryId;
        $this->coverImageUrl = $coverImageUrl;

        // Se o novo preço ficou menor ou igual ao desconto vigente, o desconto deixa de fazer sentido.
        if ($this->discountPrice !== null && $this->discountPrice >= $price) {
            $this->discountPrice = null;
        }

        $this->setUpdated();
    }

    public function applyDiscount(float $discountPrice): void
    {
        if ($discountPrice >= $this->price) {
            throw new DomainException('Discount price must be lower than original price.');
        }

        $this->discountPrice = $discountPrice;
        $this->setUpdated();
    }

    public function removeDiscount(): void
    {
        $this->discountPrice = null;
        $this->setUpdated();
    }

    public function setStatus(GameStatus $status): void
    {
        $this->status = $status;
        $this->setUpdated();
    }

    /** @param string[] $tags */
    public function setTags(array $tags): void
    {
        $normalized = array_values(array_unique(array_map(
            static fn (string $t) => strtolower(trim($t)),
            $tags
        )));

        $this->tags = $normalized;
        $this->setUpdated();
    }

    public function getCurrentPrice(): float
    {
        return $this->discountPrice ?? $this->price;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDeveloper(): string
    {
        return $this->developer;
    }

    public function getPublisher(): string
    {
        return $this->publisher;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDiscountPrice(): ?float
    {
        return $this->discountPrice;
    }

    public function getReleaseDate(): string
    {
        return $this->releaseDate;
    }

    public function getCoverImageUrl(): string
    {
        return $this->coverImageUrl;
    }

    public function getStatus(): GameStatus
    {
        return $this->status;
    }

    /** @return string[] */
    public function getTagsArray(): array
    {
        return $this->tags;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName ?? '';
    }

    public function getAverageRating(): float
    {
        return $this->averageRating;
    }

    public function getReviewCount(): int
    {
        return $this->reviewCount;
    }

    public function isRecommendedByMajority(): bool
    {
        return $this->reviewCount > 0 && $this->recommendedReviewCount > (int) floor($this->reviewCount / 2);
    }
}
