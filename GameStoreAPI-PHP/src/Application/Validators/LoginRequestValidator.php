<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\LoginRequest;

final class LoginRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var LoginRequest $request */
        $errors = [];

        if (!$this->isEmail($request->email)) {
            $errors[] = 'email must be a valid email address.';
        }

        if (trim($request->password) === '') {
            $errors[] = 'password must not be empty.';
        }

        return $errors;
    }
}
