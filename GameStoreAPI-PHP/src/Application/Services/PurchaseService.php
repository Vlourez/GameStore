<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\CreatePurchaseRequest;
use GameStore\Application\DTOs\Response\PurchaseDetailResponse;
use GameStore\Application\Exceptions\BusinessRuleException;
use GameStore\Application\Exceptions\ForbiddenException;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Interfaces\AchievementServiceInterface;
use GameStore\Application\Interfaces\PurchaseServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Entities\LibraryEntry;
use GameStore\Domain\Entities\Purchase;
use GameStore\Domain\Entities\PurchaseItem;
use GameStore\Domain\Enums\GameStatus;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class PurchaseService implements PurchaseServiceInterface
{
    public function __construct(
        private readonly UnitOfWorkInterface $uow,
        private readonly AchievementServiceInterface $achievementService
    ) {
    }

    public function create(string $customerId, CreatePurchaseRequest $req): PurchaseDetailResponse
    {
        $gameIds = array_values(array_unique($req->gameIds));

        return $this->uow->transactional(function () use ($customerId, $gameIds, $req) {
            foreach ($gameIds as $gameId) {
                if ($this->uow->games()->existsInLibrary($customerId, $gameId)) {
                    throw new BusinessRuleException("Game {$gameId} is already in your library.");
                }
            }

            $purchase = Purchase::create($customerId, $req->paymentMethod);
            $this->uow->purchases()->add($purchase);

            foreach ($gameIds as $gameId) {
                $game = $this->uow->games()->getById($gameId)
                    ?? throw new NotFoundException("Game {$gameId} not found.");

                if ($game->getStatus() !== GameStatus::Available) {
                    throw new BusinessRuleException(
                        "Game '{$game->getTitle()}' is not available for purchase."
                    );
                }

                $item = PurchaseItem::create($purchase->getId(), $gameId, $game->getCurrentPrice());
                $purchase->addItem($item);

                $this->uow->library()->add(LibraryEntry::create($customerId, $gameId));

                // Quem comprou o jogo não precisa mais dele na wishlist.
                $wishlistEntry = $this->uow->wishlist()->getEntry($customerId, $gameId);
                if ($wishlistEntry !== null) {
                    $this->uow->wishlist()->remove($wishlistEntry);
                }
            }

            $purchase->complete();
            $this->uow->purchases()->update($purchase);

            $this->achievementService->checkAndGrant($customerId);

            $withItems = $this->uow->purchases()->getByIdWithItems($purchase->getId());

            return GameStoreMapper::purchaseToDetail($withItems);
        });
    }

    public function getById(string $id, string $customerId): PurchaseDetailResponse
    {
        $purchase = $this->uow->purchases()->getByIdWithItems($id)
            ?? throw new NotFoundException("Purchase {$id} not found.");

        if ($purchase->getCustomerId() !== $customerId) {
            throw new ForbiddenException('Access denied.');
        }

        return GameStoreMapper::purchaseToDetail($purchase);
    }

    public function getByCustomer(string $customerId): array
    {
        $purchases = $this->uow->purchases()->getByCustomer($customerId);

        return array_map(GameStoreMapper::purchaseToSummary(...), $purchases);
    }

    public function refund(string $id, string $customerId): void
    {
        $purchase = $this->uow->purchases()->getByIdWithItems($id)
            ?? throw new NotFoundException("Purchase {$id} not found.");

        if ($purchase->getCustomerId() !== $customerId) {
            throw new ForbiddenException('Access denied.');
        }

        $this->uow->transactional(function () use ($purchase, $customerId): void {
            $purchase->refund();
            $this->uow->purchases()->update($purchase);

            // Jogo reembolsado sai da biblioteca (e pode ser comprado de novo).
            foreach ($purchase->getItems() as $item) {
                $this->uow->library()->remove($customerId, $item->getGameId());
            }
        });
    }
}
