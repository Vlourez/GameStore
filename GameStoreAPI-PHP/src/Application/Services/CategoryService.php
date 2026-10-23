<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\CreateCategoryRequest;
use GameStore\Application\DTOs\Request\UpdateCategoryRequest;
use GameStore\Application\DTOs\Response\CategoryResponse;
use GameStore\Application\Exceptions\BusinessRuleException;
use GameStore\Application\Exceptions\ConflictException;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Interfaces\CategoryServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Entities\Category;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class CategoryService implements CategoryServiceInterface
{
    public function __construct(private readonly UnitOfWorkInterface $uow)
    {
    }

    public function getAll(): array
    {
        $categories = $this->uow->categories()->getAll();

        return array_map(GameStoreMapper::categoryToResponse(...), $categories);
    }

    public function getById(string $id): CategoryResponse
    {
        $category = $this->uow->categories()->getById($id)
            ?? throw new NotFoundException("Category {$id} not found.");

        return GameStoreMapper::categoryToResponse($category);
    }

    public function create(CreateCategoryRequest $req): CategoryResponse
    {
        if ($this->uow->categories()->slugExists($req->slug)) {
            throw new ConflictException("Slug '{$req->slug}' already exists.");
        }

        $category = Category::create($req->name, $req->description, $req->slug);
        $this->uow->categories()->add($category);

        return GameStoreMapper::categoryToResponse($category);
    }

    public function update(string $id, UpdateCategoryRequest $req): CategoryResponse
    {
        $category = $this->uow->categories()->getById($id)
            ?? throw new NotFoundException("Category {$id} not found.");

        if ($this->uow->categories()->slugExists($req->slug, $id)) {
            throw new ConflictException("Slug '{$req->slug}' already exists.");
        }

        $category->update($req->name, $req->description, $req->slug);
        $this->uow->categories()->update($category);

        return GameStoreMapper::categoryToResponse($category);
    }

    public function delete(string $id): void
    {
        $category = $this->uow->categories()->getById($id)
            ?? throw new NotFoundException("Category {$id} not found.");

        if ($category->getGameCount() > 0) {
            throw new BusinessRuleException(
                "Category has {$category->getGameCount()} game(s). Move or delete them before deleting the category."
            );
        }

        $category->softDelete();
        $this->uow->categories()->update($category);
    }
}
