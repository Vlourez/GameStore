<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\CreateCategoryRequest;
use GameStore\Application\DTOs\Request\UpdateCategoryRequest;
use GameStore\Application\Interfaces\CategoryServiceInterface;
use GameStore\Application\Validators\CreateCategoryRequestValidator;

final class CategoriesController
{
    public function __construct(private readonly CategoryServiceInterface $categoryService)
    {
    }

    public function index(Request $request): void
    {
        Response::json($this->categoryService->getAll());
    }

    public function show(Request $request): void
    {
        Response::json($this->categoryService->getById($request->routeParam('id')));
    }

    public function store(Request $request): void
    {
        $dto = CreateCategoryRequest::fromArray($request->json());
        (new CreateCategoryRequestValidator())->assert($dto);

        Response::json($this->categoryService->create($dto), 201);
    }

    public function update(Request $request): void
    {
        $dto = UpdateCategoryRequest::fromArray($request->json());
        (new CreateCategoryRequestValidator())->assert($dto);

        Response::json($this->categoryService->update($request->routeParam('id'), $dto));
    }

    public function destroy(Request $request): void
    {
        $this->categoryService->delete($request->routeParam('id'));
        Response::noContent();
    }
}
