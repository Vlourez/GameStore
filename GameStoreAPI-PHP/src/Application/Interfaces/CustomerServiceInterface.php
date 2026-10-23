<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\UpdateProfileRequest;
use GameStore\Application\DTOs\Response\AchievementResponse;
use GameStore\Application\DTOs\Response\CustomerDetailResponse;

interface CustomerServiceInterface
{
    public function getById(string $id): CustomerDetailResponse;

    public function updateProfile(string $id, UpdateProfileRequest $request): CustomerDetailResponse;

    /** @return AchievementResponse[] */
    public function getAchievements(string $customerId): array;
}
