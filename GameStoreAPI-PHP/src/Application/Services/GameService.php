<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\ApplyDiscountRequest;
use GameStore\Application\DTOs\Request\ChangeGameStatusRequest;
use GameStore\Application\DTOs\Request\CreateGameRequest;
use GameStore\Application\DTOs\Request\GameQueryParams;
use GameStore\Application\DTOs\Request\UpdateGameRequest;
use GameStore\Application\DTOs\Response\GameDetailResponse;
use GameStore\Application\DTOs\Response\PagedResponse;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Interfaces\GameServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Entities\Game;
use GameStore\Domain\Enums\GameStatus;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class GameService implements GameServiceInterface
{
    public function __construct(private readonly UnitOfWorkInterface $uow)
    {
    }

    public function getPaged(GameQueryParams $q): PagedResponse
    {
        $filters = GameFilters::build($q);
        $orderBy = GameFilters::buildOrderBy($q->orderBy);

        $paged = $this->uow->games()->getPaged($q->page, $q->pageSize, $filters, $orderBy, $q->descending);

        return GameStoreMapper::toPagedResponse($paged, GameStoreMapper::gameToSummary(...));
    }

    public function getById(string $id): GameDetailResponse
    {
        $game = $this->uow->games()->getByIdWithDetails($id)
            ?? throw new NotFoundException("Game {$id} not found.");

        return GameStoreMapper::gameToDetail($game);
    }

    public function getRecommendations(string $customerId): array
    {
        $games = $this->uow->games()->getRecommendedForCustomer($customerId, 10);

        return array_map(GameStoreMapper::gameToSummary(...), $games);
    }

    public function create(CreateGameRequest $req): GameDetailResponse
    {
        $this->uow->categories()->getById($req->categoryId)
            ?? throw new NotFoundException("Category {$req->categoryId} not found.");

        $game = Game::create(
            $req->title,
            $req->description,
            $req->developer,
            $req->publisher,
            $req->price,
            $req->releaseDate,
            $req->categoryId,
            $req->coverImageUrl
        );

        if ($req->tags !== []) {
            $game->setTags($req->tags);
        }

        $this->uow->games()->add($game);

        $created = $this->uow->games()->getByIdWithDetails($game->getId());

        return GameStoreMapper::gameToDetail($created);
    }

    public function update(string $id, UpdateGameRequest $req): GameDetailResponse
    {
        $game = $this->uow->games()->getById($id)
            ?? throw new NotFoundException("Game {$id} not found.");

        $this->uow->categories()->getById($req->categoryId)
            ?? throw new NotFoundException("Category {$req->categoryId} not found.");

        $game->update(
            $req->title,
            $req->description,
            $req->developer,
            $req->publisher,
            $req->price,
            $req->releaseDate,
            $req->categoryId,
            $req->coverImageUrl
        );

        // tags ausente (null) = mantém as atuais; [] = limpa todas.
        if ($req->tags !== null) {
            $game->setTags($req->tags);
        }

        $this->uow->games()->update($game);

        $updated = $this->uow->games()->getByIdWithDetails($id);

        return GameStoreMapper::gameToDetail($updated);
    }

    public function changeStatus(string $id, ChangeGameStatusRequest $req): void
    {
        $game = $this->uow->games()->getById($id)
            ?? throw new NotFoundException("Game {$id} not found.");

        $game->setStatus(GameStatus::fromName($req->status));
        $this->uow->games()->update($game);
    }

    public function applyDiscount(string $id, ApplyDiscountRequest $req): void
    {
        $game = $this->uow->games()->getById($id)
            ?? throw new NotFoundException("Game {$id} not found.");

        $game->applyDiscount($req->discountPrice);
        $this->uow->games()->update($game);
    }

    public function removeDiscount(string $id): void
    {
        $game = $this->uow->games()->getById($id)
            ?? throw new NotFoundException("Game {$id} not found.");

        $game->removeDiscount();
        $this->uow->games()->update($game);
    }

    public function delete(string $id): void
    {
        $game = $this->uow->games()->getById($id)
            ?? throw new NotFoundException("Game {$id} not found.");

        $game->softDelete();
        $this->uow->games()->update($game);
    }
}
