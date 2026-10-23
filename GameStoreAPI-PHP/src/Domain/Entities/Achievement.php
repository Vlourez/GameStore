<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;
use GameStore\Domain\Enums\AchievementType;

class Achievement extends BaseEntity
{
    private string $customerId;
    private AchievementType $type;
    private string $name;
    private string $description;
    private string $earnedAt;

    private function __construct()
    {
    }

    public static function create(
        string $customerId,
        AchievementType $type,
        string $name,
        string $description
    ): self {
        $a = new self();
        $a->initNew();
        $a->customerId = $customerId;
        $a->type = $type;
        $a->name = $name;
        $a->description = $description;
        $a->earnedAt = gmdate('Y-m-d H:i:s');

        return $a;
    }

    /** @param array<string,mixed> $row */
    public static function hydrate(array $row): self
    {
        $a = new self();
        $a->id = $row['id'];
        $a->createdAt = $row['created_at'];
        $a->updatedAt = $row['updated_at'];
        $a->isDeleted = (bool) $row['is_deleted'];
        $a->customerId = $row['customer_id'];
        $a->type = AchievementType::from((int) $row['type']);
        $a->name = $row['name'];
        $a->description = $row['description'];
        $a->earnedAt = $row['earned_at'];

        return $a;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getType(): AchievementType
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getEarnedAt(): string
    {
        return $this->earnedAt;
    }
}
