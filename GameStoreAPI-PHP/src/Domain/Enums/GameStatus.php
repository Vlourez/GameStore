<?php

declare(strict_types=1);

namespace GameStore\Domain\Enums;

enum GameStatus: int
{
    case ComingSoon = 1;
    case Available = 2;
    case Discontinued = 3;

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->name, $name) === 0) {
                return $case;
            }
        }

        throw new \InvalidArgumentException("Unknown GameStatus: {$name}");
    }
}
