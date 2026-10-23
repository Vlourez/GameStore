<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Entities\Customer;
use GameStore\Domain\Interfaces\CustomerRepositoryInterface;
use PDO;

final class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function getById(string $id): ?Customer
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Customer::hydrate($row) : null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM customers WHERE is_deleted = 0 ORDER BY full_name ASC');

        return array_map(Customer::hydrate(...), $stmt->fetchAll());
    }

    public function add(Customer $customer): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO customers
                (id, full_name, email, password_hash, avatar_url, birth_date, is_active, role, created_at, updated_at, is_deleted)
             VALUES
                (:id, :full_name, :email, :password_hash, :avatar_url, :birth_date, :is_active, :role, :created_at, :updated_at, 0)'
        );
        $stmt->execute([
            'id' => $customer->getId(),
            'full_name' => $customer->getFullName(),
            'email' => $customer->getEmail(),
            'password_hash' => $customer->getPasswordHash(),
            'avatar_url' => $customer->getAvatarUrl(),
            'birth_date' => $customer->getBirthDate(),
            'is_active' => (int) $customer->isActive(),
            'role' => $customer->getRole(),
            'created_at' => $customer->getCreatedAt(),
            'updated_at' => $customer->getUpdatedAt(),
        ]);
    }

    public function update(Customer $customer): void
    {
        $stmt = $this->db->prepare(
            'UPDATE customers SET full_name = :full_name, email = :email, password_hash = :password_hash,
             avatar_url = :avatar_url, birth_date = :birth_date, is_active = :is_active, role = :role,
             updated_at = :updated_at, is_deleted = :is_deleted WHERE id = :id'
        );
        $stmt->execute([
            'id' => $customer->getId(),
            'full_name' => $customer->getFullName(),
            'email' => $customer->getEmail(),
            'password_hash' => $customer->getPasswordHash(),
            'avatar_url' => $customer->getAvatarUrl(),
            'birth_date' => $customer->getBirthDate(),
            'is_active' => (int) $customer->isActive(),
            'role' => $customer->getRole(),
            'updated_at' => $customer->getUpdatedAt(),
            'is_deleted' => (int) $customer->isDeleted(),
        ]);
    }

    public function remove(Customer $customer): void
    {
        $this->update($customer);
    }

    public function getByEmail(string $email): ?Customer
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE email = :email AND is_deleted = 0');
        $stmt->execute(['email' => strtolower($email)]);
        $row = $stmt->fetch();

        return $row ? Customer::hydrate($row) : null;
    }

    public function emailExists(string $email, ?string $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM customers WHERE email = :email AND is_deleted = 0';
        $params = ['email' => strtolower($email)];

        if ($excludeId !== null) {
            $sql .= ' AND id != :excludeId';
            $params['excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function getWithLibrary(string $id): ?Customer
    {
        $customer = $this->getById($id);
        if ($customer === null) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM library_entries WHERE customer_id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $customer->setLibraryCount((int) $stmt->fetchColumn());

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM wishlist_entries WHERE customer_id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $customer->setWishlistCount((int) $stmt->fetchColumn());

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM achievements WHERE customer_id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $customer->setAchievementCount((int) $stmt->fetchColumn());

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM purchases WHERE customer_id = :id AND status = 2 AND is_deleted = 0"
        );
        $stmt->execute(['id' => $id]);
        $customer->setCompletedPurchaseCount((int) $stmt->fetchColumn());

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reviews WHERE customer_id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $customer->setReviewCount((int) $stmt->fetchColumn());

        return $customer;
    }
}
