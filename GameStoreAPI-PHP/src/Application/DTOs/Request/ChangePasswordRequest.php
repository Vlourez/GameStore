<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class ChangePasswordRequest
{
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: (string) ($data['currentPassword'] ?? ''),
            newPassword: (string) ($data['newPassword'] ?? '')
        );
    }
}
