<?php

declare(strict_types=1);

namespace GameStore\Application\Interfaces;

use GameStore\Application\DTOs\Request\ChangePasswordRequest;
use GameStore\Application\DTOs\Request\LoginRequest;
use GameStore\Application\DTOs\Request\RegisterRequest;
use GameStore\Application\DTOs\Response\AuthResponse;

interface AuthServiceInterface
{
    public function register(RegisterRequest $request): AuthResponse;

    public function login(LoginRequest $request): AuthResponse;

    public function changePassword(string $customerId, ChangePasswordRequest $request): void;
}
