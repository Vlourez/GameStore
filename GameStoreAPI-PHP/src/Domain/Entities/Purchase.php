<?php

declare(strict_types=1);

namespace GameStore\Domain\Entities;

use GameStore\Domain\Common\BaseEntity;
use GameStore\Domain\Common\DomainException;
use GameStore\Domain\Enums\PurchaseStatus;

class Purchase extends BaseEntity
{
    private string $customerId;
    private PurchaseStatus $status = PurchaseStatus::Pending;
    private float $totalAmount = 0.0;
    private ?string $paymentMethod = null;
    private string $purchasedAt;

    /** @var PurchaseItem[] */
    private array $items = [];

    private function __construct()
    {
    }

    public static function create(string $customerId, ?string $paymentMethod = null): self
    {
        $p = new self();
        $p->initNew();
        $p->customerId = $customerId;
        $p->paymentMethod = $paymentMethod;
        $p->purchasedAt = gmdate('Y-m-d H:i:s');

        return $p;
    }

    /** @param array<string,mixed> $row */
    public static function hydrate(array $row): self
    {
        $p = new self();
        $p->id = $row['id'];
        $p->createdAt = $row['created_at'];
        $p->updatedAt = $row['updated_at'];
        $p->isDeleted = (bool) $row['is_deleted'];
        $p->customerId = $row['customer_id'];
        $p->status = PurchaseStatus::from((int) $row['status']);
        $p->totalAmount = (float) $row['total_amount'];
        $p->paymentMethod = $row['payment_method'];
        $p->purchasedAt = $row['purchased_at'];

        return $p;
    }

    public function addItem(PurchaseItem $item): void
    {
        $this->items[] = $item;
        $this->recalculateTotal();
    }

    private function recalculateTotal(): void
    {
        $this->totalAmount = array_reduce(
            $this->items,
            static fn (float $carry, PurchaseItem $item) => $carry + $item->getUnitPrice(),
            0.0
        );
    }

    public function complete(): void
    {
        $this->status = PurchaseStatus::Completed;
        $this->setUpdated();
    }

    public function refund(): void
    {
        if ($this->status !== PurchaseStatus::Completed) {
            throw new DomainException('Only completed purchases can be refunded.');
        }

        $this->status = PurchaseStatus::Refunded;
        $this->setUpdated();
    }

    public function cancel(): void
    {
        if ($this->status === PurchaseStatus::Completed) {
            throw new DomainException('Completed purchases cannot be cancelled.');
        }

        $this->status = PurchaseStatus::Cancelled;
        $this->setUpdated();
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getStatus(): PurchaseStatus
    {
        return $this->status;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function getPurchasedAt(): string
    {
        return $this->purchasedAt;
    }

    /** @return PurchaseItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
