<?php

declare(strict_types=1);

namespace GameStore\Domain\Enums;

enum PurchaseStatus: int
{
    case Pending = 1;
    case Completed = 2;
    case Refunded = 3;
    case Cancelled = 4;
}
