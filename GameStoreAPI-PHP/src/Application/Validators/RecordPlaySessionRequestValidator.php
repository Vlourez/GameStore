<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\RecordPlaySessionRequest;

final class RecordPlaySessionRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var RecordPlaySessionRequest $request */
        $errors = [];

        if (trim($request->gameId) === '') {
            $errors[] = 'gameId must not be empty.';
        }

        if ($request->minutes < 1 || $request->minutes > 1440) {
            $errors[] = 'minutes must be between 1 and 1440.';
        }

        return $errors;
    }
}
