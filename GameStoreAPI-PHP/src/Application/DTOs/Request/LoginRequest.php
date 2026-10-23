<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class LoginRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) ($data['email'] ?? ''),
            password: (string) ($data['password'] ?? '')
        );
    }
}
