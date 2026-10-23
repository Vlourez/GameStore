<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

/**
 * Update rules are identical to create rules, so we simply reuse the logic.
 */
final class UpdateGameRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        return (new CreateGameRequestValidator())->rules($request);
    }
}
