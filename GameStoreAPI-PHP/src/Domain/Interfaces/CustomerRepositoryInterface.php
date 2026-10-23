<?php

declare(strict_types=1);

namespace GameStore\Domain\Interfaces;

use GameStore\Domain\Entities\Customer;

interface CustomerRepositoryInterface
{
    public function getById(string $id): ?Customer;

    /** @return Customer[] */
    public function getAll(): array;

    public function add(Customer $customer): void;

    public function update(Customer $customer): void;

    public function remove(Customer $customer): void;

    public function getByEmail(string $email): ?Customer;

    public function emailExists(string $email, ?string $excludeId = null): bool;

    public function getWithLibrary(string $id): ?Customer;
}
