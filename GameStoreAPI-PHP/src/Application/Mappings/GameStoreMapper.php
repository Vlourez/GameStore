<?php

declare(strict_types=1);

namespace GameStore\Application\Mappings;

use GameStore\Application\DTOs\Response\AchievementResponse;
use GameStore\Application\DTOs\Response\CategoryResponse;
use GameStore\Application\DTOs\Response\CustomerDetailResponse;
use GameStore\Application\DTOs\Response\CustomerSummaryResponse;
use GameStore\Application\DTOs\Response\GameDetailResponse;
use GameStore\Application\DTOs\Response\GameSummaryResponse;
use GameStore\Application\DTOs\Response\LibraryEntryResponse;
use GameStore\Application\DTOs\Response\PagedResponse;
use GameStore\Application\DTOs\Response\PurchaseDetailResponse;
use GameStore\Application\DTOs\Response\PurchaseItemResponse;
use GameStore\Application\DTOs\Response\PurchaseSummaryResponse;
use GameStore\Application\DTOs\Response\ReviewResponse;
use GameStore\Application\DTOs\Response\WishlistEntryResponse;
use GameStore\Domain\Common\PagedResult;
use GameStore\Domain\Entities\Achievement;
use GameStore\Domain\Entities\Category;
use GameStore\Domain\Entities\Customer;
use GameStore\Domain\Entities\Game;
use GameStore\Domain\Entities\LibraryEntry;
use GameStore\Domain\Entities\Purchase;
use GameStore\Domain\Entities\PurchaseItem;
use GameStore\Domain\Entities\Review;
use GameStore\Domain\Entities\WishlistEntry;

final class GameStoreMapper
{
    public static function gameToSummary(Game $g): GameSummaryResponse
    {
        return new GameSummaryResponse(
            id: $g->getId(),
            title: $g->getTitle(),
            developer: $g->getDeveloper(),
            coverImageUrl: $g->getCoverImageUrl(),
            price: $g->getPrice(),
            discountPrice: $g->getDiscountPrice(),
            currentPrice: $g->getCurrentPrice(),
            averageRating: $g->getAverageRating(),
            reviewCount: $g->getReviewCount(),
            categoryName: $g->getCategoryName(),
            status: $g->getStatus()->name,
            releaseDate: $g->getReleaseDate()
        );
    }

    public static function gameToDetail(Game $g): GameDetailResponse
    {
        return new GameDetailResponse(
            id: $g->getId(),
            title: $g->getTitle(),
            description: $g->getDescription(),
            developer: $g->getDeveloper(),
            publisher: $g->getPublisher(),
            price: $g->getPrice(),
            discountPrice: $g->getDiscountPrice(),
            currentPrice: $g->getCurrentPrice(),
            releaseDate: $g->getReleaseDate(),
            coverImageUrl: $g->getCoverImageUrl(),
            status: $g->getStatus()->name,
            tags: $g->getTagsArray(),
            categoryId: $g->getCategoryId(),
            categoryName: $g->getCategoryName(),
            averageRating: $g->getAverageRating(),
            reviewCount: $g->getReviewCount(),
            isRecommendedByMajority: $g->isRecommendedByMajority(),
            createdAt: $g->getCreatedAt()
        );
    }

    public static function categoryToResponse(Category $c): CategoryResponse
    {
        return new CategoryResponse(
            id: $c->getId(),
            name: $c->getName(),
            description: $c->getDescription(),
            slug: $c->getSlug(),
            gameCount: $c->getGameCount()
        );
    }

    public static function customerToSummary(Customer $c): CustomerSummaryResponse
    {
        return new CustomerSummaryResponse(
            id: $c->getId(),
            fullName: $c->getFullName(),
            email: $c->getEmail(),
            avatarUrl: $c->getAvatarUrl(),
            role: $c->getRole()
        );
    }

    public static function customerToDetail(Customer $c): CustomerDetailResponse
    {
        return new CustomerDetailResponse(
            id: $c->getId(),
            fullName: $c->getFullName(),
            email: $c->getEmail(),
            avatarUrl: $c->getAvatarUrl(),
            birthDate: $c->getBirthDate(),
            role: $c->getRole(),
            isActive: $c->isActive(),
            libraryCount: $c->getLibraryCount(),
            wishlistCount: $c->getWishlistCount(),
            achievementCount: $c->getAchievementCount(),
            createdAt: $c->getCreatedAt()
        );
    }

    public static function purchaseToSummary(Purchase $p): PurchaseSummaryResponse
    {
        return new PurchaseSummaryResponse(
            id: $p->getId(),
            totalAmount: $p->getTotalAmount(),
            status: $p->getStatus()->name,
            paymentMethod: $p->getPaymentMethod(),
            itemCount: count($p->getItems()),
            purchasedAt: $p->getPurchasedAt()
        );
    }

    public static function purchaseToDetail(Purchase $p): PurchaseDetailResponse
    {
        return new PurchaseDetailResponse(
            id: $p->getId(),
            totalAmount: $p->getTotalAmount(),
            status: $p->getStatus()->name,
            paymentMethod: $p->getPaymentMethod(),
            purchasedAt: $p->getPurchasedAt(),
            items: array_map(self::purchaseItemToResponse(...), $p->getItems())
        );
    }

    public static function purchaseItemToResponse(PurchaseItem $i): PurchaseItemResponse
    {
        return new PurchaseItemResponse(
            gameId: $i->getGameId(),
            gameTitle: $i->getGameTitle(),
            gameCoverUrl: $i->getGameCoverUrl(),
            unitPrice: $i->getUnitPrice()
        );
    }

    public static function libraryEntryToResponse(LibraryEntry $e): LibraryEntryResponse
    {
        return new LibraryEntryResponse(
            id: $e->getId(),
            gameId: $e->getGameId(),
            gameTitle: $e->getGameTitle(),
            gameCoverUrl: $e->getGameCoverUrl(),
            acquiredAt: $e->getAcquiredAt(),
            lastPlayedAt: $e->getLastPlayedAt(),
            totalPlaytimeMinutes: $e->getTotalPlaytimeMinutes()
        );
    }

    public static function wishlistEntryToResponse(WishlistEntry $w): WishlistEntryResponse
    {
        return new WishlistEntryResponse(
            id: $w->getId(),
            gameId: $w->getGameId(),
            gameTitle: $w->getGameTitle(),
            gameCoverUrl: $w->getGameCoverUrl(),
            currentPrice: $w->getGameCurrentPrice(),
            notifyOnDiscount: $w->isNotifyOnDiscount(),
            addedAt: $w->getAddedAt()
        );
    }

    public static function reviewToResponse(Review $r): ReviewResponse
    {
        return new ReviewResponse(
            id: $r->getId(),
            customerId: $r->getCustomerId(),
            customerName: $r->getCustomerName(),
            gameId: $r->getGameId(),
            gameTitle: $r->getGameTitle(),
            rating: $r->getRating(),
            title: $r->getTitle(),
            body: $r->getBody(),
            isRecommended: $r->isRecommended(),
            createdAt: $r->getCreatedAt()
        );
    }

    public static function achievementToResponse(Achievement $a): AchievementResponse
    {
        return new AchievementResponse(
            id: $a->getId(),
            type: $a->getType()->name,
            name: $a->getName(),
            description: $a->getDescription(),
            earnedAt: $a->getEarnedAt()
        );
    }

    /**
     * @param callable(mixed): mixed $itemMapper
     */
    public static function toPagedResponse(PagedResult $result, callable $itemMapper): PagedResponse
    {
        return new PagedResponse(
            items: array_map($itemMapper, $result->items),
            totalCount: $result->totalCount,
            page: $result->page,
            pageSize: $result->pageSize,
            totalPages: $result->totalPages(),
            hasNextPage: $result->hasNextPage(),
            hasPreviousPage: $result->hasPreviousPage()
        );
    }
}
