<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

interface UnitOfWorkInterface
{
    public function games(): GameRepositoryInterface;

    public function categories(): CategoryRepositoryInterface;

    public function customers(): CustomerRepositoryInterface;

    public function purchases(): PurchaseRepositoryInterface;

    public function library(): LibraryRepositoryInterface;

    public function wishlist(): WishlistRepositoryInterface;

    public function reviews(): ReviewRepositoryInterface;

    public function achievements(): AchievementRepositoryInterface;

    /**
     * Runs $callback inside a database transaction, committing on success
     * and rolling back if an exception is thrown. Mirrors EF Core's
     * SaveChanges-per-request-scope behaviour from the original project.
     */
    public function transactional(callable $callback): mixed;
}
