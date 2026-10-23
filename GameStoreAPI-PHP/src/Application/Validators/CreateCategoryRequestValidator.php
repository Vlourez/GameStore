<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\CreateCategoryRequest;
use GameStore\Application\DTOs\Request\UpdateCategoryRequest;

final class CreateCategoryRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var CreateCategoryRequest|UpdateCategoryRequest $request */
        $errors = [];

        $this->notEmpty($request->name, 'name', $errors, 100);
        $this->notEmpty($request->description, 'description', $errors, 500);

        if (trim($request->slug) === '') {
            $errors[] = 'slug must not be empty.';
        } elseif (mb_strlen($request->slug) > 100) {
            $errors[] = 'slug must be 100 characters or fewer.';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $request->slug)) {
            $errors[] = 'slug must contain only lowercase letters, numbers and hyphens.';
        }

        return $errors;
    }
}
