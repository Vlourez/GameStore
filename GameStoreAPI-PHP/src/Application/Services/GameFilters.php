<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\DTOs\Request\GameQueryParams;
use GameStore\Application\Exceptions\ValidationException;
use GameStore\Domain\Enums\GameStatus;

final class GameFilters
{
    /** @return array<string,mixed> */
    public static function build(GameQueryParams $q): array
    {
        $filters = [];

        if ($q->search !== null) {
            $filters['search'] = $q->search;
        }
        if ($q->categoryId !== null) {
            $filters['categoryId'] = $q->categoryId;
        }
        if ($q->minPrice !== null) {
            $filters['minPrice'] = $q->minPrice;
        }
        if ($q->maxPrice !== null) {
            $filters['maxPrice'] = $q->maxPrice;
        }
        if ($q->status !== null) {
            try {
                $filters['status'] = GameStatus::fromName($q->status);
            } catch (\InvalidArgumentException) {
                throw new ValidationException(['status must be one of: ComingSoon, Available, Discontinued.']);
            }
        }

        return $filters;
    }

    public static function buildOrderBy(?string $orderBy): string
    {
        return match (strtolower((string) $orderBy)) {
            'price' => 'price',
            'releasedate' => 'release_date',
            'title' => 'title',
            'rating' => 'average_rating',
            default => 'created_at',
        };
    }
}
