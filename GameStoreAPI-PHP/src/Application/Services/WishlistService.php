<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\AddToWishlistRequest;
use GameStore\Application\Exceptions\BusinessRuleException;
use GameStore\Application\Exceptions\ConflictException;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Interfaces\WishlistServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Entities\WishlistEntry;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class WishlistService implements WishlistServiceInterface
{
    public function __construct(private readonly UnitOfWorkInterface $uow)
    {
    }

    public function getByCustomer(string $customerId): array
    {
        $entries = $this->uow->wishlist()->getByCustomer($customerId);

        return array_map(GameStoreMapper::wishlistEntryToResponse(...), $entries);
    }

    public function add(string $customerId, AddToWishlistRequest $req): void
    {
        $this->uow->games()->getById($req->gameId)
            ?? throw new NotFoundException("Game {$req->gameId} not found.");

        if ($this->uow->games()->existsInLibrary($customerId, $req->gameId)) {
            throw new BusinessRuleException('Game is already in your library.');
        }

        if ($this->uow->wishlist()->getEntry($customerId, $req->gameId) !== null) {
            throw new ConflictException('Game already in wishlist.');
        }

        $entry = WishlistEntry::create($customerId, $req->gameId, $req->notifyOnDiscount);
        $this->uow->wishlist()->add($entry);
    }

    public function remove(string $customerId, string $gameId): void
    {
        $entry = $this->uow->wishlist()->getEntry($customerId, $gameId)
            ?? throw new NotFoundException('Wishlist entry not found.');

        $this->uow->wishlist()->remove($entry);
    }
}
