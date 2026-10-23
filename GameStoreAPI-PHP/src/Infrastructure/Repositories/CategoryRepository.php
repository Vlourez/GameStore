<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Entities\Category;
use GameStore\Domain\Interfaces\CategoryRepositoryInterface;
use PDO;

final class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function getById(string $id): ?Category
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, (SELECT COUNT(*) FROM games g WHERE g.category_id = c.id AND g.is_deleted = 0) AS game_count
             FROM categories c WHERE c.id = :id AND c.is_deleted = 0'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Category::hydrate($row) : null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, (SELECT COUNT(*) FROM games g WHERE g.category_id = c.id AND g.is_deleted = 0) AS game_count
             FROM categories c WHERE c.is_deleted = 0 ORDER BY c.name ASC'
        );

        return array_map(Category::hydrate(...), $stmt->fetchAll());
    }

    public function add(Category $category): void
    {
        $this->releaseDeletedSlug($category->getSlug());

        $stmt = $this->db->prepare(
            'INSERT INTO categories (id, name, description, slug, created_at, updated_at, is_deleted)
             VALUES (:id, :name, :description, :slug, :created_at, :updated_at, 0)'
        );
        $stmt->execute([
            'id' => $category->getId(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
            'slug' => $category->getSlug(),
            'created_at' => $category->getCreatedAt(),
            'updated_at' => $category->getUpdatedAt(),
        ]);
    }

    public function update(Category $category): void
    {
        if (!$category->isDeleted()) {
            $this->releaseDeletedSlug($category->getSlug());
        }

        $stmt = $this->db->prepare(
            'UPDATE categories SET name = :name, description = :description, slug = :slug,
             updated_at = :updated_at, is_deleted = :is_deleted WHERE id = :id'
        );
        $stmt->execute([
            'id' => $category->getId(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
            'slug' => $category->getSlug(),
            'updated_at' => $category->getUpdatedAt(),
            'is_deleted' => (int) $category->isDeleted(),
        ]);
    }

    public function remove(Category $category): void
    {
        $this->update($category);
    }

    public function getBySlug(string $slug): ?Category
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, (SELECT COUNT(*) FROM games g WHERE g.category_id = c.id AND g.is_deleted = 0) AS game_count
             FROM categories c WHERE c.slug = :slug AND c.is_deleted = 0'
        );
        $stmt->execute(['slug' => strtolower($slug)]);
        $row = $stmt->fetch();

        return $row ? Category::hydrate($row) : null;
    }

    /**
     * Categorias apagadas (soft delete) continuam na tabela e a coluna slug é UNIQUE.
     * Renomeia o slug de qualquer categoria apagada que ainda use este slug (dados legados),
     * para que ele possa ser reutilizado.
     */
    private function releaseDeletedSlug(string $slug): void
    {
        $stmt = $this->db->prepare(
            "UPDATE categories SET slug = CONCAT(LEFT(slug, 80), '~', SUBSTRING(id, 1, 8))
             WHERE slug = :slug AND is_deleted = 1"
        );
        $stmt->execute(['slug' => $slug]);
    }

    public function slugExists(string $slug, ?string $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = :slug AND is_deleted = 0';
        $params = ['slug' => strtolower($slug)];

        if ($excludeId !== null) {
            $sql .= ' AND id != :excludeId';
            $params['excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return ((int) $stmt->fetchColumn()) > 0;
    }
}
