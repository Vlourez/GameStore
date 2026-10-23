<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\AddToWishlistRequest;
use GameStore\Application\Interfaces\WishlistServiceInterface;
use GameStore\Application\Validators\AddToWishlistRequestValidator;

final class WishlistController
{
    public function __construct(private readonly WishlistServiceInterface $wishlistService)
    {
    }

    public function index(Request $request): void
    {
        Response::json($this->wishlistService->getByCustomer($request->authenticatedCustomerId));
    }

    public function store(Request $request): void
    {
        $dto = AddToWishlistRequest::fromArray($request->json());
        (new AddToWishlistRequestValidator())->assert($dto);

        $this->wishlistService->add($request->authenticatedCustomerId, $dto);
        Response::json(['message' => 'Added to wishlist.'], 201);
    }

    public function destroy(Request $request): void
    {
        $this->wishlistService->remove($request->authenticatedCustomerId, $request->routeParam('gameId'));
        Response::noContent();
    }
}
