<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;
use GameStore\Domain\Common\DomainException;

class Review extends BaseEntity
{
    private string $customerId;
    private string $gameId;
    private int $rating;
    private string $title;
    private string $body;
    private bool $isRecommended;

    // Transient, populated by repository joins
    private ?string $customerName = null;
    private ?string $gameTitle = null;

    private function __construct()
    {
    }

    public static function create(
        string $customerId,
        string $gameId,
        int $rating,
        string $title,
        string $body,
        bool $isRecommended
    ): self {
        if ($rating < 1 || $rating > 5) {
            throw new DomainException('Rating must be between 1 and 5.');
        }

        $r = new self();
        $r->initNew();
        $r->customerId = $customerId;
        $r->gameId = $gameId;
        $r->rating = $rating;
        $r->title = $title;
        $r->body = $body;
        $r->isRecommended = $isRecommended;

        return $r;
    }

    /** @param array<string,mixed> $row */
    public static function hydrate(array $row): self
    {
        $r = new self();
        $r->id = $row['id'];
        $r->createdAt = $row['created_at'];
        $r->updatedAt = $row['updated_at'];
        $r->isDeleted = (bool) $row['is_deleted'];
        $r->customerId = $row['customer_id'];
        $r->gameId = $row['game_id'];
        $r->rating = (int) $row['rating'];
        $r->title = $row['title'];
        $r->body = $row['body'];
        $r->isRecommended = (bool) $row['is_recommended'];
        $r->customerName = $row['customer_name'] ?? null;
        $r->gameTitle = $row['game_title'] ?? null;

        return $r;
    }

    public function update(int $rating, string $title, string $body, bool $isRecommended): void
    {
        if ($rating < 1 || $rating > 5) {
            throw new DomainException('Rating must be between 1 and 5.');
        }

        $this->rating = $rating;
        $this->title = $title;
        $this->body = $body;
        $this->isRecommended = $isRecommended;
        $this->setUpdated();
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getGameId(): string
    {
        return $this->gameId;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isRecommended(): bool
    {
        return $this->isRecommended;
    }

    public function getCustomerName(): string
    {
        return $this->customerName ?? '';
    }

    public function getGameTitle(): string
    {
        return $this->gameTitle ?? '';
    }
}
