<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\ChangeGameStatusRequest;
use GameStore\Domain\Enums\GameStatus;

final class ChangeGameStatusRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var ChangeGameStatusRequest $request */
        $errors = [];

        try {
            GameStatus::fromName($request->status);
        } catch (\InvalidArgumentException) {
            $errors[] = 'status must be one of: ComingSoon, Available, Discontinued.';
        }

        return $errors;
    }
}
