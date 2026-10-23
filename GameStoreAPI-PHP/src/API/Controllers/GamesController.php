<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\ApplyDiscountRequest;
use GameStore\Application\DTOs\Request\ChangeGameStatusRequest;
use GameStore\Application\DTOs\Request\CreateGameRequest;
use GameStore\Application\DTOs\Request\GameQueryParams;
use GameStore\Application\DTOs\Request\UpdateGameRequest;
use GameStore\Application\Interfaces\GameServiceInterface;
use GameStore\Application\Validators\ApplyDiscountRequestValidator;
use GameStore\Application\Validators\ChangeGameStatusRequestValidator;
use GameStore\Application\Validators\CreateGameRequestValidator;
use GameStore\Application\Validators\UpdateGameRequestValidator;

final class GamesController
{
    public function __construct(private readonly GameServiceInterface $gameService)
    {
    }

    public function index(Request $request): void
    {
        $query = GameQueryParams::fromArray($request->query);
        Response::json($this->gameService->getPaged($query));
    }

    public function show(Request $request): void
    {
        Response::json($this->gameService->getById($request->routeParam('id')));
    }

    public function recommendations(Request $request): void
    {
        Response::json($this->gameService->getRecommendations($request->authenticatedCustomerId));
    }

    public function store(Request $request): void
    {
        $dto = CreateGameRequest::fromArray($request->json());
        (new CreateGameRequestValidator())->assert($dto);

        Response::json($this->gameService->create($dto), 201);
    }

    public function update(Request $request): void
    {
        $dto = UpdateGameRequest::fromArray($request->json());
        (new UpdateGameRequestValidator())->assert($dto);

        Response::json($this->gameService->update($request->routeParam('id'), $dto));
    }

    public function changeStatus(Request $request): void
    {
        $dto = ChangeGameStatusRequest::fromArray($request->json());
        (new ChangeGameStatusRequestValidator())->assert($dto);

        $this->gameService->changeStatus($request->routeParam('id'), $dto);
        Response::noContent();
    }

    public function applyDiscount(Request $request): void
    {
        $dto = ApplyDiscountRequest::fromArray($request->json());
        (new ApplyDiscountRequestValidator())->assert($dto);

        $this->gameService->applyDiscount($request->routeParam('id'), $dto);
        Response::noContent();
    }

    public function removeDiscount(Request $request): void
    {
        $this->gameService->removeDiscount($request->routeParam('id'));
        Response::noContent();
    }

    public function destroy(Request $request): void
    {
        $this->gameService->delete($request->routeParam('id'));
        Response::noContent();
    }
}
