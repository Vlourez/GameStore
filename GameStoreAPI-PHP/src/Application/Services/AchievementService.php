<?php

declare(strict_types=1);

namespace GameStore\Application\Services;

use GameStore\Application\Interfaces\AchievementServiceInterface;
use GameStore\Domain\Entities\Achievement;
use GameStore\Domain\Enums\AchievementType;
use GameStore\Domain\Enums\PurchaseStatus;
use GameStore\Domain\Interfaces\UnitOfWorkInterface;

final class AchievementService implements AchievementServiceInterface
{
    public function __construct(private readonly UnitOfWorkInterface $uow)
    {
    }

    public function checkAndGrant(string $customerId): void
    {
        $customer = $this->uow->customers()->getWithLibrary($customerId);
        if ($customer === null) {
            return;
        }

        $purchases = $this->uow->purchases()->getByCustomer($customerId);
        $hasCompletedPurchase = false;
        foreach ($purchases as $purchase) {
            if ($purchase->getStatus() === PurchaseStatus::Completed) {
                $hasCompletedPurchase = true;
                break;
            }
        }

        $this->tryGrant(
            $customerId,
            AchievementType::FirstPurchase,
            'First Purchase',
            'Made your first purchase!',
            $hasCompletedPurchase
        );

        $this->tryGrant(
            $customerId,
            AchievementType::Collector,
            'Collector',
            'Owns 10 or more games.',
            $customer->getLibraryCount() >= 10
        );

        $this->tryGrant(
            $customerId,
            AchievementType::ReviewWriter,
            'Critic',
            'Wrote your first review.',
            $customer->getReviewCount() > 0
        );
    }

    private function tryGrant(
        string $customerId,
        AchievementType $type,
        string $name,
        string $description,
        bool $conditionMet
    ): void {
        if (!$conditionMet) {
            return;
        }

        if ($this->uow->achievements()->hasAchievement($customerId, $type)) {
            return;
        }

        $this->uow->achievements()->add(Achievement::create($customerId, $type, $name, $description));
    }
}
