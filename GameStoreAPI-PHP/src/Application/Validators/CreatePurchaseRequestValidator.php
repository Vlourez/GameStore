<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\CreatePurchaseRequest;

final class CreatePurchaseRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var CreatePurchaseRequest $request */
        $errors = [];

        if ($request->gameIds === []) {
            $errors[] = 'At least one game is required.';
        }

        if (count($request->gameIds) > 50) {
            $errors[] = 'A purchase can contain at most 50 games.';
        }

        if ($request->paymentMethod !== null && mb_strlen($request->paymentMethod) > 50) {
            $errors[] = 'paymentMethod must be 50 characters or fewer.';
        }

        foreach ($request->gameIds as $gameId) {
            if (trim((string) $gameId) === '') {
                $errors[] = 'gameIds must not contain empty values.';
                break;
            }
        }

        return $errors;
    }
}
