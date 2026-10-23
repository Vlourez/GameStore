<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Request;

final class ChangeGameStatusRequest
{
    public function __construct(public readonly string $status)
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(status: (string) ($data['status'] ?? ''));
    }
}
