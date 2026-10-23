<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\ApplyDiscountRequest;
use GameStore\Application\DTOs\Request\ChangeGameStatusRequest;
use GameStore\Application\DTOs\Request\CreateGameRequest;
use GameStore\Application\DTOs\Request\GameQueryParams;
use GameStore\Application\DTOs\Request\UpdateGameRequest;
use GameStore\Application\DTOs\Response\GameDetailResponse;
use GameStore\Application\DTOs\Response\PagedResponse;

interface GameServiceInterface
{
    public function getPaged(GameQueryParams $query): PagedResponse;

    public function getById(string $id): GameDetailResponse;

    /** @return \GameStore\Application\DTOs\Response\GameSummaryResponse[] */
    public function getRecommendations(string $customerId): array;

    public function create(CreateGameRequest $request): GameDetailResponse;

    public function update(string $id, UpdateGameRequest $request): GameDetailResponse;

    public function changeStatus(string $id, ChangeGameStatusRequest $request): void;

    public function applyDiscount(string $id, ApplyDiscountRequest $request): void;

    public function removeDiscount(string $id): void;

    public function delete(string $id): void;
}
