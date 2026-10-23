<?php

declare(strict_types=1);

namespace GameStore\API\Middleware;

use GameStore\API\Http\Request;
use GameStore\Application\Exceptions\ForbiddenException;
use GameStore\Application\Exceptions\UnauthorizedException;
use GameStore\Support\Jwt;
use RuntimeException;

final class AuthMiddleware
{
    public function __construct(private readonly string $jwtSecret)
    {
    }

    /** @param string[] $requiredRoles */
    public function handle(Request $request, array $requiredRoles = []): void
    {
        $token = $request->bearerToken();
        if ($token === null) {
            throw new UnauthorizedException('Missing authentication token.');
        }

        try {
            $payload = Jwt::decode($token, $this->jwtSecret);
        } catch (RuntimeException $e) {
            throw new UnauthorizedException($e->getMessage());
        }

        $request->authenticatedCustomerId = (string) ($payload['sub'] ?? '');
        $request->authenticatedRole = (string) ($payload['role'] ?? 'Customer');

        if ($requiredRoles !== [] && !in_array($request->authenticatedRole, $requiredRoles, true)) {
            throw new ForbiddenException('You do not have permission to perform this action.');
        }
    }
}
