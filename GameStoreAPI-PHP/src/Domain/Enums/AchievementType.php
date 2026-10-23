<?php

declare(strict_types=1);

namespace GameStore\Domain\Enums;

enum AchievementType: int
{
    case FirstPurchase = 1;
    case ReviewWriter = 2;
    case Collector = 3;
    case BigSpender = 4;
    case EarlyAdopter = 5;
}
