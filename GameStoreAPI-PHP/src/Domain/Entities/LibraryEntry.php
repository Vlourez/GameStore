<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;

class LibraryEntry extends BaseEntity
{
    private string $customerId;
    private string $gameId;
    private string $acquiredAt;
    private ?string $lastPlayedAt = null;
    private int $totalPlaytimeMinutes = 0;

    // Transient, populated by repository joins
    private ?string $gameTitle = null;
    private ?string $gameCoverUrl = null;

    private function __construct()
    {
    }

    public static function create(string $customerId, string $gameId): self
    {
        $e = new self();
        $e->initNew();
        $e->customerId = $customerId;
        $e->gameId = $gameId;
        $e->acquiredAt = gmdate('Y-m-d H:i:s');

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
        $e->acquiredAt = $row['acquired_at'];
        $e->lastPlayedAt = $row['last_played_at'];
        $e->totalPlaytimeMinutes = (int) $row['total_playtime_minutes'];
        $e->gameTitle = $row['game_title'] ?? null;
        $e->gameCoverUrl = $row['game_cover_url'] ?? null;

        return $e;
    }

    public function recordPlaySession(int $minutes): void
    {
        if ($minutes <= 0) {
            return;
        }

        $this->totalPlaytimeMinutes += $minutes;
        $this->lastPlayedAt = gmdate('Y-m-d H:i:s');
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

    public function getAcquiredAt(): string
    {
        return $this->acquiredAt;
    }

    public function getLastPlayedAt(): ?string
    {
        return $this->lastPlayedAt;
    }

    public function getTotalPlaytimeMinutes(): int
    {
        return $this->totalPlaytimeMinutes;
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
