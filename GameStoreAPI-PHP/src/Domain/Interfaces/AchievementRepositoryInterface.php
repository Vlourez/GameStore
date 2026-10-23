<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Entities\Achievement;
use GameStore\Domain\Enums\AchievementType;

interface AchievementRepositoryInterface
{
    public function add(Achievement $achievement): void;

    /** @return Achievement[] */
    public function getByCustomer(string $customerId): array;

    public function hasAchievement(string $customerId, AchievementType $type): bool;
}
