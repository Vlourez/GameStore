<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Interfaces\AchievementRepositoryInterface;
use GameStore\Domain\Interfaces\CategoryRepositoryInterface;
use GameStore\Domain\Interfaces\CustomerRepositoryInterface;
use GameStore\Domain\Interfaces\GameRepositoryInterface;
use GameStore\Domain\Interfaces\LibraryRepositoryInterface;
use GameStore\Domain\Interfaces\PurchaseRepositoryInterface;
use GameStore\Domain\Interfaces\ReviewRepositoryInterface;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;
use GameStore\Domain\Interfaces\WishlistRepositoryInterface;
use PDO;
use Throwable;

final class UnitOfWork implements UnitOfWorkInterface
{
    private ?GameRepositoryInterface $games = null;
    private ?CategoryRepositoryInterface $categories = null;
    private ?CustomerRepositoryInterface $customers = null;
    private ?PurchaseRepositoryInterface $purchases = null;
    private ?LibraryRepositoryInterface $library = null;
    private ?WishlistRepositoryInterface $wishlist = null;
    private ?ReviewRepositoryInterface $reviews = null;
    private ?AchievementRepositoryInterface $achievements = null;

    public function __construct(private readonly PDO $db)
    {
    }

    public function games(): GameRepositoryInterface
    {
        return $this->games ??= new GameRepository($this->db);
    }

    public function categories(): CategoryRepositoryInterface
    {
        return $this->categories ??= new CategoryRepository($this->db);
    }

    public function customers(): CustomerRepositoryInterface
    {
        return $this->customers ??= new CustomerRepository($this->db);
    }

    public function purchases(): PurchaseRepositoryInterface
    {
        return $this->purchases ??= new PurchaseRepository($this->db);
    }

    public function library(): LibraryRepositoryInterface
    {
        return $this->library ??= new LibraryRepository($this->db);
    }

    public function wishlist(): WishlistRepositoryInterface
    {
        return $this->wishlist ??= new WishlistRepository($this->db);
    }

    public function reviews(): ReviewRepositoryInterface
    {
        return $this->reviews ??= new ReviewRepository($this->db);
    }

    public function achievements(): AchievementRepositoryInterface
    {
        return $this->achievements ??= new AchievementRepository($this->db);
    }

    public function transactional(callable $callback): mixed
    {
        $alreadyInTransaction = $this->db->inTransaction();

        if (!$alreadyInTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $result = $callback();

            if (!$alreadyInTransaction) {
                $this->db->commit();
            }

            return $result;
        } catch (Throwable $e) {
            if (!$alreadyInTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
