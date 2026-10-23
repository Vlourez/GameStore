<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\ApplyDiscountRequest;

final class ApplyDiscountRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var ApplyDiscountRequest $request */
        $errors = [];

        if ($request->discountPrice <= 0) {
            $errors[] = 'discountPrice must be greater than 0.';
        }

        return $errors;
    }
}
