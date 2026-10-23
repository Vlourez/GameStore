<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\UpdateProfileRequest;

final class UpdateProfileRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var UpdateProfileRequest $request */
        $errors = [];

        $this->notEmpty($request->fullName, 'fullName', $errors, 100);

        if ($request->avatarUrl !== null && mb_strlen($request->avatarUrl) > 500) {
            $errors[] = 'avatarUrl must be 500 characters or fewer.';
        }

        $this->checkBirthDate($request->birthDate, $errors);

        return $errors;
    }
}
