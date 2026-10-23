<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;

class Customer extends BaseEntity
{
    private string $fullName;
    private string $email;
    private string $passwordHash;
    private ?string $avatarUrl = null;
    private ?string $birthDate = null;
    private bool $isActive = true;
    private string $role = 'Customer';

    /** Transient aggregate counters, populated by the repository when needed. */
    private int $libraryCount = 0;
    private int $wishlistCount = 0;
    private int $achievementCount = 0;
    private int $purchaseCount = 0;
    private int $completedPurchaseCount = 0;
    private int $reviewCount = 0;

    private function __construct()
    {
    }

    public static function create(
        string $fullName,
        string $email,
        string $passwordHash,
        ?string $birthDate = null
    ): self {
        $c = new self();
        $c->initNew();
        $c->fullName = $fullName;
        $c->email = strtolower($email);
        $c->passwordHash = $passwordHash;
        $c->birthDate = $birthDate;

        return $c;
    }

    /** @param array<string,mixed> $row */
    public static function hydrate(array $row): self
    {
        $c = new self();
        $c->id = $row['id'];
        $c->createdAt = $row['created_at'];
        $c->updatedAt = $row['updated_at'];
        $c->isDeleted = (bool) $row['is_deleted'];
        $c->fullName = $row['full_name'];
        $c->email = $row['email'];
        $c->passwordHash = $row['password_hash'];
        $c->avatarUrl = $row['avatar_url'];
        $c->birthDate = $row['birth_date'];
        $c->isActive = (bool) $row['is_active'];
        $c->role = $row['role'];

        return $c;
    }

    public function updateProfile(string $fullName, ?string $avatarUrl, ?string $birthDate): void
    {
        $this->fullName = $fullName;
        $this->avatarUrl = $avatarUrl;
        $this->birthDate = $birthDate;
        $this->setUpdated();
    }

    public function updatePassword(string $newPasswordHash): void
    {
        $this->passwordHash = $newPasswordHash;
        $this->setUpdated();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->setUpdated();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->setUpdated();
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
        $this->setUpdated();
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getLibraryCount(): int
    {
        return $this->libraryCount;
    }

    public function setLibraryCount(int $count): void
    {
        $this->libraryCount = $count;
    }

    public function getWishlistCount(): int
    {
        return $this->wishlistCount;
    }

    public function setWishlistCount(int $count): void
    {
        $this->wishlistCount = $count;
    }

    public function getAchievementCount(): int
    {
        return $this->achievementCount;
    }

    public function setAchievementCount(int $count): void
    {
        $this->achievementCount = $count;
    }

    public function getCompletedPurchaseCount(): int
    {
        return $this->completedPurchaseCount;
    }

    public function setCompletedPurchaseCount(int $count): void
    {
        $this->completedPurchaseCount = $count;
    }

    public function getReviewCount(): int
    {
        return $this->reviewCount;
    }

    public function setReviewCount(int $count): void
    {
        $this->reviewCount = $count;
    }
}
