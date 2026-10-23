<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;

class WishlistEntry extends BaseEntity
{
    private string $customerId;
    private string $gameId;
    private bool $notifyOnDiscount = true;
    private string $addedAt;

    // Transient, populated by repository joins
    private ?string $gameTitle = null;
    private ?string $gameCoverUrl = null;
    private ?float $gameCurrentPrice = null;

    private function __construct()
    {
    }

    public static function create(string $customerId, string $gameId, bool $notifyOnDiscount = true): self
    {
        $e = new self();
        $e->initNew();
        $e->customerId = $customerId;
        $e->gameId = $gameId;
        $e->notifyOnDiscount = $notifyOnDiscount;
        $e->addedAt = gmdate('Y-m-d H:i:s');

        return $e;
    }

    /** @param array<string,mixed> $row */
    public static function hydrate(array $row): self
    {
        $e = new self();
        $e->id = $row['id'];
        $e->createdAt = $row['created_at'];
        $e->updatedAt = $row['updated_at'];
        $e->isDeleted = (bool) $row['is_deleted'];
        $e->customerId = $row['customer_id'];
        $e->gameId = $row['game_id'];
        $e->notifyOnDiscount = (bool) $row['notify_on_discount'];
        $e->addedAt = $row['added_at'];
        $e->gameTitle = $row['game_title'] ?? null;
        $e->gameCoverUrl = $row['game_cover_url'] ?? null;
        $e->gameCurrentPrice = isset($row['game_current_price']) ? (float) $row['game_current_price'] : null;

        return $e;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getGameId(): string
    {
        return $this->gameId;
    }

    public function isNotifyOnDiscount(): bool
    {
        return $this->notifyOnDiscount;
    }

    public function getAddedAt(): string
    {
        return $this->addedAt;
    }

    public function getGameTitle(): string
    {
        return $this->gameTitle ?? '';
    }

    public function getGameCoverUrl(): string
    {
        return $this->gameCoverUrl ?? '';
    }

    public function getGameCurrentPrice(): float
    {
        return $this->gameCurrentPrice ?? 0.0;
    }
}
