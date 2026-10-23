<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\CreateReviewRequest;
use GameStore\Application\DTOs\Request\UpdateReviewRequest;
use GameStore\Application\DTOs\Response\PagedResponse;
use GameStore\Application\DTOs\Response\ReviewResponse;
use GameStore\Application\Exceptions\BusinessRuleException;
use GameStore\Application\Exceptions\ConflictException;
use GameStore\Application\Exceptions\ForbiddenException;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Interfaces\AchievementServiceInterface;
use GameStore\Application\Interfaces\ReviewServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Entities\Review;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class ReviewService implements ReviewServiceInterface
{
    public function __construct(
        private readonly UnitOfWorkInterface $uow,
        private readonly AchievementServiceInterface $achievementService
    ) {
    }

    public function getByGame(string $gameId, int $page, int $pageSize): PagedResponse
    {
        $paged = $this->uow->reviews()->getPaged($page, $pageSize, $gameId);

        return GameStoreMapper::toPagedResponse($paged, GameStoreMapper::reviewToResponse(...));
    }

    public function create(string $customerId, CreateReviewRequest $req): ReviewResponse
    {
        if (!$this->uow->games()->existsInLibrary($customerId, $req->gameId)) {
            throw new BusinessRuleException('You must own the game to review it.');
        }

        if ($this->uow->reviews()->getByCustomerAndGame($customerId, $req->gameId) !== null) {
            throw new ConflictException('You already reviewed this game.');
        }

        $review = Review::create($customerId, $req->gameId, $req->rating, $req->title, $req->body, $req->isRecommended);
        $this->uow->reviews()->add($review);

        $this->achievementService->checkAndGrant($customerId);

        $withDetails = $this->uow->reviews()->getById($review->getId());

        return GameStoreMapper::reviewToResponse($withDetails);
    }

    public function update(string $customerId, string $reviewId, UpdateReviewRequest $req): ReviewResponse
    {
        $review = $this->uow->reviews()->getById($reviewId)
            ?? throw new NotFoundException("Review {$reviewId} not found.");

        if ($review->getCustomerId() !== $customerId) {
            throw new ForbiddenException('Access denied.');
        }

        $review->update($req->rating, $req->title, $req->body, $req->isRecommended);
        $this->uow->reviews()->update($review);

        return GameStoreMapper::reviewToResponse($review);
    }

    public function delete(string $customerId, string $reviewId): void
    {
        $review = $this->uow->reviews()->getById($reviewId)
            ?? throw new NotFoundException("Review {$reviewId} not found.");

        if ($review->getCustomerId() !== $customerId) {
            throw new ForbiddenException('Access denied.');
        }

        $review->softDelete();
        $this->uow->reviews()->update($review);
    }
}
