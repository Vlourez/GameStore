<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Entities\LibraryEntry;

interface LibraryRepositoryInterface
{
    public function add(LibraryEntry $entry): void;

    public function update(LibraryEntry $entry): void;

    /** Remove definitivamente o jogo da biblioteca (usado no reembolso). */
    public function remove(string $customerId, string $gameId): void;

    public function getEntry(string $customerId, string $gameId): ?LibraryEntry;

    /** @return LibraryEntry[] */
    public function getByCustomer(string $customerId): array;
}
