<?php

declare(strict_types=1);

namespace GameStore\Application\Validators;

use GameStore\Application\DTOs\Request\CreateGameRequest;
use GameStore\Application\DTOs\Request\UpdateGameRequest;

final class CreateGameRequestValidator extends AbstractValidator
{
    public function rules(object $request): array
    {
        /** @var CreateGameRequest|UpdateGameRequest $request */
        $errors = [];

        $this->notEmpty($request->title, 'title', $errors, 200);
        $this->notEmpty($request->description, 'description', $errors, 5000);
        $this->notEmpty($request->developer, 'developer', $errors, 100);
        $this->notEmpty($request->publisher, 'publisher', $errors, 100);

        if ($request->price <= 0) {
            $errors[] = 'price must be greater than 0.';
        } elseif ($request->price > 99999999.99) {
            $errors[] = 'price must not exceed 99999999.99.';
        }

        if (!$this->isDate($request->releaseDate)) {
            $errors[] = 'releaseDate must be a valid date in the format YYYY-MM-DD.';
        }

        if (mb_strlen($request->coverImageUrl) > 500) {
            $errors[] = 'coverImageUrl must be 500 characters or fewer.';
        }

        $tags = $request->tags ?? [];
        if (count($tags) > 20) {
            $errors[] = 'tags must contain at most 20 items.';
        }
        foreach ($tags as $tag) {
            if (mb_strlen($tag) > 50) {
                $errors[] = 'each tag must be 50 characters or fewer.';
                break;
            }
        }

        if (trim($request->categoryId) === '') {
            $errors[] = 'categoryId must not be empty.';
        }

        return $errors;
    }
}
