<?php

declare(strict_types=1);

namespace GameStore\Domain\Common;

use GameStore\Support\Uuid;

abstract class BaseEntity
{
    protected string $id;
    protected string $createdAt;
    protected ?string $updatedAt = null;
    protected bool $isDeleted = false;

    /**
     * Called by concrete entities' factory methods when creating a brand
     * new instance (never for hydration from the database).
     */
    protected function initNew(): void
    {
        $this->id = Uuid::v4();
        $this->createdAt = gmdate('Y-m-d H:i:s');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    protected function setUpdated(): void
    {
        $this->updatedAt = gmdate('Y-m-d H:i:s');
    }

    public function softDelete(): void
    {
        $this->isDeleted = true;
        $this->setUpdated();
    }
}
