<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class AuthResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $expiresAt,
        public readonly CustomerSummaryResponse $customer
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'accessToken' => $this->accessToken,
            'expiresAt' => $this->expiresAt,
            'customer' => $this->customer,
        ];
    }
}
