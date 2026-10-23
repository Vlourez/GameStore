<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class CustomerDetailResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $fullName,
        public readonly string $email,
        public readonly ?string $avatarUrl,
        public readonly ?string $birthDate,
        public readonly string $role,
        public readonly bool $isActive,
        public readonly int $libraryCount,
        public readonly int $wishlistCount,
        public readonly int $achievementCount,
        public readonly string $createdAt
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'avatarUrl' => $this->avatarUrl,
            'birthDate' => $this->birthDate,
            'role' => $this->role,
            'isActive' => $this->isActive,
            'libraryCount' => $this->libraryCount,
            'wishlistCount' => $this->wishlistCount,
            'achievementCount' => $this->achievementCount,
            'createdAt' => $this->createdAt,
        ];
    }
}
