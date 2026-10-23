<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\AddToWishlistRequest;

final class AddToWishlistRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var AddToWishlistRequest $request */
        $errors = [];

        if (trim($request->gameId) === '') {
            $errors[] = 'gameId must not be empty.';
        }

        return $errors;
    }
}
