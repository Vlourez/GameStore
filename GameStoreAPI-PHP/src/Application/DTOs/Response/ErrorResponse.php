<?php

declare(strict_types=1);

namespace GameStore\Application\DTOs\Response;

final class ErrorResponse implements \JsonSerializable
{
    /** @param string[]|null $errors */
    public function __construct(
        public readonly string $message,
        public readonly ?array $errors = null
    ) {
    }

    public function jsonSerialize(): array
    {
        $data = ['message' => $this->message];
        if ($this->errors !== null) {
            $data['errors'] = $this->errors;
        }

        return $data;
    }
}
