<?php

declare(strict_types=1);

namespace GameStore\API\Controllers;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\Application\DTOs\Request\ChangePasswordRequest;
use GameStore\Application\DTOs\Request\LoginRequest;
use GameStore\Application\DTOs\Request\RegisterRequest;
use GameStore\Application\Interfaces\AuthServiceInterface;
use GameStore\Application\Validators\ChangePasswordRequestValidator;
use GameStore\Application\Validators\LoginRequestValidator;
use GameStore\Application\Validators\RegisterRequestValidator;

final class AuthController
{
    public function __construct(private readonly AuthServiceInterface $authService)
    {
    }

    public function register(Request $request): void
    {
        $dto = RegisterRequest::fromArray($request->json());
        (new RegisterRequestValidator())->assert($dto);

        $result = $this->authService->register($dto);
        Response::json($result, 201);
    }

    public function login(Request $request): void
    {
        $dto = LoginRequest::fromArray($request->json());
        (new LoginRequestValidator())->assert($dto);

        $result = $this->authService->login($dto);
        Response::json($result, 200);
    }

    public function changePassword(Request $request): void
    {
        $dto = ChangePasswordRequest::fromArray($request->json());
        (new ChangePasswordRequestValidator())->assert($dto);

        $this->authService->changePassword($request->authenticatedCustomerId, $dto);
        Response::noContent();
    }
}
