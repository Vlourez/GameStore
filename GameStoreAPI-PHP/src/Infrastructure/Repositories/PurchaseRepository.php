<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Entities\Purchase;
use GameStore\Domain\Entities\PurchaseItem;
use GameStore\Domain\Interfaces\PurchaseRepositoryInterface;
use PDO;

final class PurchaseRepository implements PurchaseRepositoryInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function getById(string $id): ?Purchase
    {
        $stmt = $this->db->prepare('SELECT * FROM purchases WHERE id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Purchase::hydrate($row) : null;
    }

    public function add(Purchase $purchase): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO purchases (id, customer_id, status, total_amount, payment_method, purchased_at, created_at, updated_at, is_deleted)
             VALUES (:id, :customer_id, :status, :total_amount, :payment_method, :purchased_at, :created_at, :updated_at, 0)'
        );
        $stmt->execute($this->toParams($purchase));

        foreach ($purchase->getItems() as $item) {
            $this->insertItem($item);
        }
    }

    public function update(Purchase $purchase): void
    {
        $stmt = $this->db->prepare(
            'UPDATE purchases SET status = :status, total_amount = :total_amount, payment_method = :payment_method,
             updated_at = :updated_at, is_deleted = :is_deleted WHERE id = :id'
        );
        $params = $this->toParams($purchase);
        $params['is_deleted'] = (int) $purchase->isDeleted();
        unset($params['customer_id'], $params['purchased_at'], $params['created_at']);
        $stmt->execute($params);

        // Persist any items that were added on this instance but not yet stored (e.g. right after add()).
        $existingStmt = $this->db->prepare('SELECT id FROM purchase_items WHERE purchase_id = :id');
        $existingStmt->execute(['id' => $purchase->getId()]);
        $existingIds = $existingStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($purchase->getItems() as $item) {
            if (!in_array($item->getId(), $existingIds, true)) {
                $this->insertItem($item);
            }
        }
    }

    private function insertItem(PurchaseItem $item): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO purchase_items (id, purchase_id, game_id, unit_price, created_at, updated_at, is_deleted)
             VALUES (:id, :purchase_id, :game_id, :unit_price, :created_at, :updated_at, 0)'
        );
        $stmt->execute([
            'id' => $item->getId(),
            'purchase_id' => $item->getPurchaseId(),
            'game_id' => $item->getGameId(),
            'unit_price' => $item->getUnitPrice(),
            'created_at' => $item->getCreatedAt(),
            'updated_at' => $item->getUpdatedAt(),
        ]);
    }

    public function getByIdWithItems(string $id): ?Purchase
    {
        $purchase = $this->getById($id);
        if ($purchase === null) {
            return null;
        }

        $purchase->setItems($this->loadItems($id));

        return $purchase;
    }

    public function getByCustomer(string $customerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM purchases WHERE customer_id = :customerId AND is_deleted = 0 ORDER BY purchased_at DESC'
        );
        $stmt->execute(['customerId' => $customerId]);

        $purchases = array_map(Purchase::hydrate(...), $stmt->fetchAll());

        foreach ($purchases as $purchase) {
            $purchase->setItems($this->loadItems($purchase->getId()));
        }

        return $purchases;
    }

    /** @return PurchaseItem[] */
    private function loadItems(string $purchaseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pi.*, g.title AS game_title, g.cover_image_url AS game_cover_url
             FROM purchase_items pi
             JOIN games g ON g.id = pi.game_id
             WHERE pi.purchase_id = :purchaseId AND pi.is_deleted = 0'
        );
        $stmt->execute(['purchaseId' => $purchaseId]);

        return array_map(PurchaseItem::hydrate(...), $stmt->fetchAll());
    }

    /** @return array<string,mixed> */
    private function toParams(Purchase $purchase): array
    {
        return [
            'id' => $purchase->getId(),
            'customer_id' => $purchase->getCustomerId(),
            'status' => $purchase->getStatus()->value,
            'total_amount' => $purchase->getTotalAmount(),
            'payment_method' => $purchase->getPaymentMethod(),
            'purchased_at' => $purchase->getPurchasedAt(),
            'created_at' => $purchase->getCreatedAt(),
            'updated_at' => $purchase->getUpdatedAt(),
        ];
    }
}
