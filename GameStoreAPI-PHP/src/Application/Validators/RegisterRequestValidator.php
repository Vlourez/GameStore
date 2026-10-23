<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\RegisterRequest;

final class RegisterRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var RegisterRequest $request */
        $errors = [];

        $this->notEmpty($request->fullName, 'fullName', $errors, 100);

        if (!$this->isEmail($request->email)) {
            $errors[] = 'email must be a valid email address.';
        } elseif (mb_strlen($request->email) > 200) {
            $errors[] = 'email must be 200 characters or fewer.';
        }

        $this->checkPassword($request->password, 'password', $errors);
        $this->checkBirthDate($request->birthDate, $errors);

        return $errors;
    }
}
