<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\UpdateProfileRequest;
use GameStore\Application\Interfaces\CustomerServiceInterface;
use GameStore\Application\Validators\UpdateProfileRequestValidator;

final class CustomersController
{
    public function __construct(private readonly CustomerServiceInterface $customerService)
    {
    }

    public function me(Request $request): void
    {
        Response::json($this->customerService->getById($request->authenticatedCustomerId));
    }

    public function updateMe(Request $request): void
    {
        $dto = UpdateProfileRequest::fromArray($request->json());
        (new UpdateProfileRequestValidator())->assert($dto);

        $result = $this->customerService->updateProfile($request->authenticatedCustomerId, $dto);
        Response::json($result);
    }

    public function achievements(Request $request): void
    {
        Response::json($this->customerService->getAchievements($request->authenticatedCustomerId));
    }
}
