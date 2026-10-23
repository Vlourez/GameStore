<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\ChangePasswordRequest;
use GameStore\Application\DTOs\Request\LoginRequest;
use GameStore\Application\DTOs\Request\RegisterRequest;
use GameStore\Application\DTOs\Response\AuthResponse;
use GameStore\Application\Exceptions\ConflictException;
use GameStore\Application\Exceptions\NotFoundException;
use GameStore\Application\Exceptions\UnauthorizedException;
use GameStore\Application\Interfaces\AuthServiceInterface;
use GameStore\Application\Mappings\GameStoreMapper;
use GameStore\Domain\Entities\Customer;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;
use GameStore\Support\Jwt;

final class AuthService implements AuthServiceInterface
{
    private const TOKEN_LIFETIME_HOURS = 8;

    public function __construct(
        private readonly UnitOfWorkInterface $uow,
        private readonly string $jwtSecret,
        private readonly string $jwtIssuer,
        private readonly string $jwtAudience
    ) {
    }

    public function register(RegisterRequest $req): AuthResponse
    {
        if ($this->uow->customers()->emailExists($req->email)) {
            throw new ConflictException('Email already registered.');
        }

        $hash = password_hash($req->password, PASSWORD_BCRYPT);
        $customer = Customer::create($req->fullName, $req->email, $hash, $req->birthDate);

        $this->uow->customers()->add($customer);

        return $this->buildAuthResponse($customer);
    }

    public function login(LoginRequest $req): AuthResponse
    {
        $customer = $this->uow->customers()->getByEmail($req->email)
            ?? throw new UnauthorizedException('Invalid credentials.');

        if (!$customer->isActive()) {
            throw new UnauthorizedException('Account is deactivated.');
        }

        if (!password_verify($req->password, $customer->getPasswordHash())) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        return $this->buildAuthResponse($customer);
    }

    public function changePassword(string $customerId, ChangePasswordRequest $req): void
    {
        $customer = $this->uow->customers()->getById($customerId)
            ?? throw new NotFoundException('Customer not found.');

        if (!password_verify($req->currentPassword, $customer->getPasswordHash())) {
            throw new UnauthorizedException('Current password is incorrect.');
        }

        $customer->updatePassword(password_hash($req->newPassword, PASSWORD_BCRYPT));
        $this->uow->customers()->update($customer);
    }

    private function buildAuthResponse(Customer $customer): AuthResponse
    {
        $expiresAt = time() + self::TOKEN_LIFETIME_HOURS * 3600;

        $token = Jwt::encode([
            'sub' => $customer->getId(),
            'email' => $customer->getEmail(),
            'role' => $customer->getRole(),
            'jti' => bin2hex(random_bytes(16)),
            'iss' => $this->jwtIssuer,
            'aud' => $this->jwtAudience,
            'iat' => time(),
            'exp' => $expiresAt,
        ], $this->jwtSecret);

        return new AuthResponse(
            accessToken: $token,
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', $expiresAt),
            customer: GameStoreMapper::customerToSummary($customer)
        );
    }
}
