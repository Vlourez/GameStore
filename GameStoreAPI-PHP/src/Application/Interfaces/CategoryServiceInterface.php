<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\CreateCategoryRequest;
use GameStore\Application\DTOs\Request\UpdateCategoryRequest;
use GameStore\Application\DTOs\Response\CategoryResponse;

interface CategoryServiceInterface
{
    /** @return CategoryResponse[] */
    public function getAll(): array;

    public function getById(string $id): CategoryResponse;

    public function create(CreateCategoryRequest $request): CategoryResponse;

    public function update(string $id, UpdateCategoryRequest $request): CategoryResponse;

    public function delete(string $id): void;
}
