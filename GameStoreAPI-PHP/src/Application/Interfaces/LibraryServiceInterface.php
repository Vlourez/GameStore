<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\RecordPlaySessionRequest;
use GameStore\Application\DTOs\Response\LibraryEntryResponse;

interface LibraryServiceInterface
{
    /** @return LibraryEntryResponse[] */
    public function getByCustomer(string $customerId): array;

    public function recordPlaySession(string $customerId, RecordPlaySessionRequest $request): void;
}
