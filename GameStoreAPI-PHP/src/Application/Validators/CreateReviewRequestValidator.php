<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\CreateReviewRequest;

final class CreateReviewRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var CreateReviewRequest $request */
        $errors = [];

        if (trim($request->gameId) === '') {
            $errors[] = 'gameId must not be empty.';
        }

        if ($request->rating < 1 || $request->rating > 5) {
            $errors[] = 'rating must be between 1 and 5.';
        }

        $this->notEmpty($request->title, 'title', $errors, 100);
        $this->notEmpty($request->body, 'body', $errors, 2000);

        return $errors;
    }
}
