<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\CreatePurchaseRequest;
use GameStore\Application\Interfaces\PurchaseServiceInterface;
use GameStore\Application\Validators\CreatePurchaseRequestValidator;

final class PurchasesController
{
    public function __construct(private readonly PurchaseServiceInterface $purchaseService)
    {
    }

    public function index(Request $request): void
    {
        Response::json($this->purchaseService->getByCustomer($request->authenticatedCustomerId));
    }

    public function show(Request $request): void
    {
        $result = $this->purchaseService->getById($request->routeParam('id'), $request->authenticatedCustomerId);
        Response::json($result);
    }

    public function store(Request $request): void
    {
        $dto = CreatePurchaseRequest::fromArray($request->json());
        (new CreatePurchaseRequestValidator())->assert($dto);

        $result = $this->purchaseService->create($request->authenticatedCustomerId, $dto);
        Response::json($result, 201);
    }

    public function refund(Request $request): void
    {
        $this->purchaseService->refund($request->routeParam('id'), $request->authenticatedCustomerId);
        Response::noContent();
    }
}
