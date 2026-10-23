<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\CreateReviewRequest;
use GameStore\Application\DTOs\Request\UpdateReviewRequest;
use GameStore\Application\Interfaces\ReviewServiceInterface;
use GameStore\Application\Validators\CreateReviewRequestValidator;
use GameStore\Application\Validators\UpdateReviewRequestValidator;

final class ReviewsController
{
    public function __construct(private readonly ReviewServiceInterface $reviewService)
    {
    }

    public function indexForGame(Request $request): void
    {
        $page = max(1, (int) $request->queryParam('page', 1));
        $pageSize = min(100, max(1, (int) $request->queryParam('pageSize', 20)));

        Response::json($this->reviewService->getByGame($request->routeParam('gameId'), $page, $pageSize));
    }

    public function store(Request $request): void
    {
        $dto = CreateReviewRequest::fromArray($request->json());
        (new CreateReviewRequestValidator())->assert($dto);

        Response::json($this->reviewService->create($request->authenticatedCustomerId, $dto), 201);
    }

    public function update(Request $request): void
    {
        $dto = UpdateReviewRequest::fromArray($request->json());
        (new UpdateReviewRequestValidator())->assert($dto);


        $result = $this->reviewService->update(
            $request->authenticatedCustomerId,
            $request->routeParam('id'),
            $dto
        );

        Response::json($result);
    }

    public function destroy(Request $request): void
    {
        $this->reviewService->delete($request->authenticatedCustomerId, $request->routeParam('id'));
        Response::noContent();
    }
}
