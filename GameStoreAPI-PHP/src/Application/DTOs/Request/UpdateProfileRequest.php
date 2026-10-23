<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class UpdateProfileRequest
{
    public function __construct(
        public readonly string $fullName,
        public readonly ?string $avatarUrl,
        public readonly ?string $birthDate
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fullName: (string) ($data['fullName'] ?? ''),
            avatarUrl: isset($data['avatarUrl']) && $data['avatarUrl'] !== '' ? (string) $data['avatarUrl'] : null,
            birthDate: isset($data['birthDate']) && $data['birthDate'] !== '' ? (string) $data['birthDate'] : null
        );
    }
}
