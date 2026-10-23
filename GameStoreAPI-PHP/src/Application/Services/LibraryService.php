<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\RecordPlaySessionRequest;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Interfaces\LibraryServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class LibraryService implements LibraryServiceInterface
{
    public function __construct(private readonly UnitOfWorkInterface $uow)
    {
    }

    public function getByCustomer(string $customerId): array
    {
        $entries = $this->uow->library()->getByCustomer($customerId);

        return array_map(GameStoreMapper::libraryEntryToResponse(...), $entries);
    }

    public function recordPlaySession(string $customerId, RecordPlaySessionRequest $req): void
    {
        $entry = $this->uow->library()->getEntry($customerId, $req->gameId)
            ?? throw new NotFoundException('Game not in library.');

        $entry->recordPlaySession($req->minutes);
        $this->uow->library()->update($entry);
    }
}
