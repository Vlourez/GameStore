<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\CreateReviewRequest;
use GameStore\Application\DTOs\Request\UpdateReviewRequest;
use GameStore\Application\DTOs\Response\PagedResponse;
use GameStore\Application\DTOs\Response\ReviewResponse;

interface ReviewServiceInterface
{
    public function getByGame(string $gameId, int $page, int $pageSize): PagedResponse;

    public function create(string $customerId, CreateReviewRequest $request): ReviewResponse;

    public function update(string $customerId, string $reviewId, UpdateReviewRequest $request): ReviewResponse;

    public function delete(string $customerId, string $reviewId): void;
}
