<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function getById(string $id): ?Category;

    /** @return Category[] */
    public function getAll(): array;

    public function add(Category $category): void;

    public function update(Category $category): void;

    public function remove(Category $category): void;

    public function getBySlug(string $slug): ?Category;

    public function slugExists(string $slug, ?string $excludeId = null): bool;
}
