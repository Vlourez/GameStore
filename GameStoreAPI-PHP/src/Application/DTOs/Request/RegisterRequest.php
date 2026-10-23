<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class RegisterRequest
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $birthDate = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fullName: (string) ($data['fullName'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            password: (string) ($data['password'] ?? ''),
            birthDate: isset($data['birthDate']) && $data['birthDate'] !== '' ? (string) $data['birthDate'] : null
        );
    }
}
