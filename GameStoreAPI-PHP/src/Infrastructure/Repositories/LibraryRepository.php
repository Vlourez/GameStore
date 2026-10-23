<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Entities\LibraryEntry;
use GameStore\Domain\Interfaces\LibraryRepositoryInterface;
use PDO;

final class LibraryRepository implements LibraryRepositoryInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function add(LibraryEntry $entry): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO library_entries
                (id, customer_id, game_id, acquired_at, last_played_at, total_playtime_minutes, created_at, updated_at, is_deleted)
             VALUES
                (:id, :customer_id, :game_id, :acquired_at, :last_played_at, :total_playtime_minutes, :created_at, :updated_at, 0)'
        );
        $stmt->execute($this->toParams($entry));
    }

    public function update(LibraryEntry $entry): void
    {
        $stmt = $this->db->prepare(
            'UPDATE library_entries SET last_played_at = :last_played_at,
             total_playtime_minutes = :total_playtime_minutes, updated_at = :updated_at WHERE id = :id'
        );
        $stmt->execute([
            'id' => $entry->getId(),
            'last_played_at' => $entry->getLastPlayedAt(),
            'total_playtime_minutes' => $entry->getTotalPlaytimeMinutes(),
            'updated_at' => $entry->getUpdatedAt(),
        ]);
    }

    public function remove(string $customerId, string $gameId): void
    {
        $stmt = $this->db->prepare('DELETE FROM library_entries WHERE customer_id = :customerId AND game_id = :gameId');
        $stmt->execute(['customerId' => $customerId, 'gameId' => $gameId]);
    }

    public function getEntry(string $customerId, string $gameId): ?LibraryEntry
    {
        $stmt = $this->db->prepare(
            'SELECT le.*, g.title AS game_title, g.cover_image_url AS game_cover_url
             FROM library_entries le
             JOIN games g ON g.id = le.game_id
             WHERE le.customer_id = :customerId AND le.game_id = :gameId AND le.is_deleted = 0'
        );
        $stmt->execute(['customerId' => $customerId, 'gameId' => $gameId]);
        $row = $stmt->fetch();

        return $row ? LibraryEntry::hydrate($row) : null;
    }

    public function getByCustomer(string $customerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT le.*, g.title AS game_title, g.cover_image_url AS game_cover_url
             FROM library_entries le
             JOIN games g ON g.id = le.game_id
             WHERE le.customer_id = :customerId AND le.is_deleted = 0
             ORDER BY le.acquired_at DESC'
        );
        $stmt->execute(['customerId' => $customerId]);

        return array_map(LibraryEntry::hydrate(...), $stmt->fetchAll());
    }

    /** @return array<string,mixed> */
    private function toParams(LibraryEntry $entry): array
    {
        return [
            'id' => $entry->getId(),
            'customer_id' => $entry->getCustomerId(),
            'game_id' => $entry->getGameId(),
            'acquired_at' => $entry->getAcquiredAt(),
            'last_played_at' => $entry->getLastPlayedAt(),
            'total_playtime_minutes' => $entry->getTotalPlaytimeMinutes(),
            'created_at' => $entry->getCreatedAt(),
            'updated_at' => $entry->getUpdatedAt(),
        ];
    }
}
