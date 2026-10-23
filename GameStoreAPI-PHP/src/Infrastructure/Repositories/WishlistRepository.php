<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Entities\WishlistEntry;
use GameStore\Domain\Interfaces\WishlistRepositoryInterface;
use PDO;

final class WishlistRepository implements WishlistRepositoryInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function add(WishlistEntry $entry): void
    {
        // Libera a chave única (customer_id, game_id) caso exista uma linha antiga marcada como apagada.
        $purge = $this->db->prepare(
            'DELETE FROM wishlist_entries WHERE customer_id = :customer_id AND game_id = :game_id AND is_deleted = 1'
        );
        $purge->execute(['customer_id' => $entry->getCustomerId(), 'game_id' => $entry->getGameId()]);

        $stmt = $this->db->prepare(
            'INSERT INTO wishlist_entries
                (id, customer_id, game_id, notify_on_discount, added_at, created_at, updated_at, is_deleted)
             VALUES
                (:id, :customer_id, :game_id, :notify_on_discount, :added_at, :created_at, :updated_at, 0)'
        );
        $stmt->execute([
            'id' => $entry->getId(),
            'customer_id' => $entry->getCustomerId(),
            'game_id' => $entry->getGameId(),
            'notify_on_discount' => (int) $entry->isNotifyOnDiscount(),
            'added_at' => $entry->getAddedAt(),
            'created_at' => $entry->getCreatedAt(),
            'updated_at' => $entry->getUpdatedAt(),
        ]);
    }

    public function remove(WishlistEntry $entry): void
    {
        // Item de wishlist não tem histórico a preservar: remoção definitiva
        // (evita conflito com a chave única ao adicionar o mesmo jogo de novo).
        $stmt = $this->db->prepare('DELETE FROM wishlist_entries WHERE id = :id');
        $stmt->execute(['id' => $entry->getId()]);
    }

    public function getEntry(string $customerId, string $gameId): ?WishlistEntry
    {
        $stmt = $this->db->prepare(
            'SELECT w.*, g.title AS game_title, g.cover_image_url AS game_cover_url,
                    COALESCE(g.discount_price, g.price) AS game_current_price
             FROM wishlist_entries w
             JOIN games g ON g.id = w.game_id
             WHERE w.customer_id = :customerId AND w.game_id = :gameId AND w.is_deleted = 0'
        );
        $stmt->execute(['customerId' => $customerId, 'gameId' => $gameId]);
        $row = $stmt->fetch();

        return $row ? WishlistEntry::hydrate($row) : null;
    }

    public function getByCustomer(string $customerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT w.*, g.title AS game_title, g.cover_image_url AS game_cover_url,
                    COALESCE(g.discount_price, g.price) AS game_current_price
             FROM wishlist_entries w
             JOIN games g ON g.id = w.game_id
             WHERE w.customer_id = :customerId AND w.is_deleted = 0
             ORDER BY w.added_at DESC'
        );
        $stmt->execute(['customerId' => $customerId]);

        return array_map(WishlistEntry::hydrate(...), $stmt->fetchAll());
    }
}
