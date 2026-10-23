<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Entities\Achievement;
use GameStore\Domain\Enums\AchievementType;
use GameStore\Domain\Interfaces\AchievementRepositoryInterface;
use PDO;

final class AchievementRepository implements AchievementRepositoryInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function add(Achievement $achievement): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO achievements (id, customer_id, type, name, description, earned_at, created_at, updated_at, is_deleted)
             VALUES (:id, :customer_id, :type, :name, :description, :earned_at, :created_at, :updated_at, 0)'
        );
        $stmt->execute([
            'id' => $achievement->getId(),
            'customer_id' => $achievement->getCustomerId(),
            'type' => $achievement->getType()->value,
            'name' => $achievement->getName(),
            'description' => $achievement->getDescription(),
            'earned_at' => $achievement->getEarnedAt(),
            'created_at' => $achievement->getCreatedAt(),
            'updated_at' => $achievement->getUpdatedAt(),
        ]);
    }

    public function getByCustomer(string $customerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM achievements WHERE customer_id = :customerId AND is_deleted = 0 ORDER BY earned_at DESC'
        );
        $stmt->execute(['customerId' => $customerId]);

        return array_map(Achievement::hydrate(...), $stmt->fetchAll());
    }

    public function hasAchievement(string $customerId, AchievementType $type): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM achievements WHERE customer_id = :customerId AND type = :type AND is_deleted = 0'
        );
        $stmt->execute(['customerId' => $customerId, 'type' => $type->value]);

        return ((int) $stmt->fetchColumn()) > 0;
    }
}
