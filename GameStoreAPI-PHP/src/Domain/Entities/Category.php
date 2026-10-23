<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;

class Category extends BaseEntity
{
    private string $name;
    private string $description;
    private string $slug;

    /** @var int transient count of games in this category, set by the repository for responses */
    private int $gameCount = 0;

    private function __construct()
    {
    }

    public static function create(string $name, string $description, string $slug): self
    {
        $c = new self();
        $c->initNew();
        $c->name = $name;
        $c->description = $description;
        $c->slug = strtolower($slug);

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
        $c->name = $row['name'];
        $c->description = $row['description'];
        $c->slug = $row['slug'];
        $c->gameCount = (int) ($row['game_count'] ?? 0);

        return $c;
    }

    public function update(string $name, string $description, string $slug): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->slug = strtolower($slug);
        $this->setUpdated();
    }

    /** Ao apagar, o slug é "aposentado" para poder ser reutilizado por uma nova categoria. */
    public function softDelete(): void
    {
        $this->slug = substr($this->slug, 0, 80) . '~' . substr($this->id, 0, 8);
        parent::softDelete();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getGameCount(): int
    {
        return $this->gameCount;
    }

    public function setGameCount(int $count): void
    {
        $this->gameCount = $count;
    }
}
