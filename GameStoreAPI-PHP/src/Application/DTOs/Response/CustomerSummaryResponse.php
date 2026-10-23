<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class CustomerSummaryResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $fullName,
        public readonly string $email,
        public readonly ?string $avatarUrl,
        public readonly string $role
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'avatarUrl' => $this->avatarUrl,
            'role' => $this->role,
        ];
    }
}
