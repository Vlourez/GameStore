<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\ChangePasswordRequest;

final class ChangePasswordRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var ChangePasswordRequest $request */
        $errors = [];

        if ($request->currentPassword === '') {
            $errors[] = 'currentPassword must not be empty.';
        }

        $this->checkPassword($request->newPassword, 'newPassword', $errors);

        if ($request->newPassword !== '' && $request->newPassword === $request->currentPassword) {
            $errors[] = 'newPassword must be different from the current password.';
        }

        return $errors;
    }
}
