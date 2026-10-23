<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;

class PurchaseItem extends BaseEntity
{
    private string $purchaseId;
    private string $gameId;
    private float $unitPrice;

    // Transient, populated by repository joins
    private ?string $gameTitle = null;
    private ?string $gameCoverUrl = null;

    private function __construct()
    {
    }

    public static function create(string $purchaseId, string $gameId, float $unitPrice): self
    {
        $i = new self();
        $i->initNew();
        $i->purchaseId = $purchaseId;
        $i->gameId = $gameId;
        $i->unitPrice = $unitPrice;

        return $i;
    }

    /** @param array<string,mixed> $row */
    public static function hydrate(array $row): self
    {
        $i = new self();
        $i->id = $row['id'];
        $i->createdAt = $row['created_at'];
        $i->updatedAt = $row['updated_at'];
        $i->isDeleted = (bool) $row['is_deleted'];
        $i->purchaseId = $row['purchase_id'];
        $i->gameId = $row['game_id'];
        $i->unitPrice = (float) $row['unit_price'];
        $i->gameTitle = $row['game_title'] ?? null;
        $i->gameCoverUrl = $row['game_cover_url'] ?? null;

        return $i;
    }

    public function getPurchaseId(): string
    {
        return $this->purchaseId;
    }

    public function getGameId(): string
    {
        return $this->gameId;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getGameTitle(): string
    {
        return $this->gameTitle ?? '';
    }

    public function getGameCoverUrl(): string
    {
        return $this->gameCoverUrl ?? '';
    }
}
