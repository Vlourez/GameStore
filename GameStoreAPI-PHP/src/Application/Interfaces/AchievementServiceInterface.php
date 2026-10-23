<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

interface AchievementServiceInterface
{
    public function checkAndGrant(string $customerId): void;
}
