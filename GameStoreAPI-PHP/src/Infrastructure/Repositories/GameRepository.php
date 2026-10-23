<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Common\PagedResult;
use GameStore\Domain\Entities\Game;
use GameStore\Domain\Interfaces\GameRepositoryInterface;
use PDO;

final class GameRepository implements GameRepositoryInterface
{
    private const BASE_SELECT = <<<SQL
        SELECT g.*,
               c.name AS category_name,
               (SELECT AVG(r.rating) FROM reviews r WHERE r.game_id = g.id AND r.is_deleted = 0) AS average_rating,
               (SELECT COUNT(*) FROM reviews r WHERE r.game_id = g.id AND r.is_deleted = 0) AS review_count,
               (SELECT COUNT(*) FROM reviews r WHERE r.game_id = g.id AND r.is_deleted = 0 AND r.is_recommended = 1) AS recommended_review_count
        FROM games g
        JOIN categories c ON c.id = g.category_id
        SQL;

    public function __construct(private readonly PDO $db)
    {
    }

    public function getById(string $id): ?Game
    {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Game::hydrate($row) : null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(self::BASE_SELECT . ' WHERE g.is_deleted = 0 ORDER BY g.title ASC');

        return array_map(Game::hydrate(...), $stmt->fetchAll());
    }

    public function add(Game $game): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO games
                (id, title, description, developer, publisher, price, discount_price, release_date,
                 cover_image_url, status, tags, category_id, created_at, updated_at, is_deleted)
             VALUES
                (:id, :title, :description, :developer, :publisher, :price, :discount_price, :release_date,
                 :cover_image_url, :status, :tags, :category_id, :created_at, :updated_at, 0)'
        );
        $stmt->execute($this->toParams($game));
    }

    public function update(Game $game): void
    {
        $stmt = $this->db->prepare(
            'UPDATE games SET title = :title, description = :description, developer = :developer,
             publisher = :publisher, price = :price, discount_price = :discount_price, release_date = :release_date,
             cover_image_url = :cover_image_url, status = :status, tags = :tags, category_id = :category_id,
             updated_at = :updated_at, is_deleted = :is_deleted WHERE id = :id'
        );
        $params = $this->toParams($game);
        $params['is_deleted'] = (int) $game->isDeleted();
        unset($params['created_at']);
        $stmt->execute($params);
    }

    public function remove(Game $game): void
    {
        $this->update($game);
    }

    public function getByIdWithDetails(string $id): ?Game
    {
        $stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE g.id = :id AND g.is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Game::hydrate($row) : null;
    }

    public function getByCategory(string $categoryId): array
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE g.is_deleted = 0 AND g.category_id = :categoryId ORDER BY g.title ASC'
        );
        $stmt->execute(['categoryId' => $categoryId]);

        return array_map(Game::hydrate(...), $stmt->fetchAll());
    }

    public function getRecommendedForCustomer(string $customerId, int $top = 10): array
    {
        // Recommend highly-rated games from categories the customer already owns games in,
        // excluding games already in their library.
        $sql = self::BASE_SELECT . "
            WHERE g.is_deleted = 0
              AND g.status = 2
              AND g.id NOT IN (SELECT game_id FROM library_entries WHERE customer_id = :customerId1 AND is_deleted = 0)
              AND g.category_id IN (
                  SELECT DISTINCT gg.category_id
                  FROM library_entries le
                  JOIN games gg ON gg.id = le.game_id
                  WHERE le.customer_id = :customerId2 AND le.is_deleted = 0
              )
            ORDER BY average_rating DESC, review_count DESC
            LIMIT :top";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':customerId1', $customerId);
        $stmt->bindValue(':customerId2', $customerId);
        $stmt->bindValue(':top', $top, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (count($rows) < $top) {
            // Fall back to top-rated available games overall to fill the list.
            $fallback = $this->db->prepare(
                self::BASE_SELECT . ' WHERE g.is_deleted = 0 AND g.status = 2
                 AND g.id NOT IN (SELECT game_id FROM library_entries WHERE customer_id = :customerId AND is_deleted = 0)
                 ORDER BY average_rating DESC, review_count DESC LIMIT :limit'
            );
            $fallback->bindValue(':customerId', $customerId);
            $fallback->bindValue(':limit', $top, PDO::PARAM_INT);
            $fallback->execute();

            $existingIds = array_column($rows, 'id');
            foreach ($fallback->fetchAll() as $row) {
                if (count($rows) >= $top) {
                    break;
                }
                if (!in_array($row['id'], $existingIds, true)) {
                    $rows[] = $row;
                    $existingIds[] = $row['id'];
                }
            }
        }

        return array_map(Game::hydrate(...), array_slice($rows, 0, $top));
    }

    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE g.is_deleted = 0
             AND (g.title LIKE :q1 OR g.developer LIKE :q2 OR g.publisher LIKE :q3)
             ORDER BY g.title ASC LIMIT 50'
        );
        $like = '%' . $query . '%';
        $stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);

        return array_map(Game::hydrate(...), $stmt->fetchAll());
    }

    public function existsInLibrary(string $customerId, string $gameId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM library_entries WHERE customer_id = :customerId AND game_id = :gameId AND is_deleted = 0'
        );
        $stmt->execute(['customerId' => $customerId, 'gameId' => $gameId]);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function getPaged(int $page, int $pageSize, array $filters = [], ?string $orderBy = null, bool $descending = false): PagedResult
    {
        $where = ['g.is_deleted = 0'];
        $params = [];

        if (!empty($filters['search'])) {
            // Um nome por uso: com EMULATE_PREPARES desligado, o PDO não aceita repetir o mesmo :parametro.
            $where[] = '(g.title LIKE :search1 OR g.developer LIKE :search2 OR g.publisher LIKE :search3)';
            $like = '%' . $filters['search'] . '%';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }
        if (!empty($filters['categoryId'])) {
            $where[] = 'g.category_id = :categoryId';
            $params['categoryId'] = $filters['categoryId'];
        }
        if (isset($filters['minPrice'])) {
            $where[] = 'COALESCE(g.discount_price, g.price) >= :minPrice';
            $params['minPrice'] = $filters['minPrice'];
        }
        if (isset($filters['maxPrice'])) {
            $where[] = 'COALESCE(g.discount_price, g.price) <= :maxPrice';
            $params['maxPrice'] = $filters['maxPrice'];
        }
        if (isset($filters['status'])) {
            $where[] = 'g.status = :status';
            $params['status'] = $filters['status'] instanceof \BackedEnum ? $filters['status']->value : $filters['status'];
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM games g {$whereSql}");
        $countStmt->execute($params);
        $totalCount = (int) $countStmt->fetchColumn();

        $allowedOrderColumns = ['title', 'price', 'release_date', 'created_at', 'average_rating'];
        $orderColumn = in_array($orderBy, $allowedOrderColumns, true) ? $orderBy : 'created_at';
        $direction = $descending ? 'DESC' : 'ASC';

        $offset = ($page - 1) * $pageSize;

        $sql = self::BASE_SELECT . " {$whereSql} ORDER BY {$orderColumn} {$direction} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = array_map(Game::hydrate(...), $stmt->fetchAll());

        return new PagedResult($items, $totalCount, $page, $pageSize);
    }

    /** @return array<string,mixed> */
    private function toParams(Game $game): array
    {
        return [
            'id' => $game->getId(),
            'title' => $game->getTitle(),
            'description' => $game->getDescription(),
            'developer' => $game->getDeveloper(),
            'publisher' => $game->getPublisher(),
            'price' => $game->getPrice(),
            'discount_price' => $game->getDiscountPrice(),
            'release_date' => $game->getReleaseDate(),
            'cover_image_url' => $game->getCoverImageUrl(),
            'status' => $game->getStatus()->value,
            'tags' => json_encode($game->getTagsArray(), JSON_THROW_ON_ERROR),
            'category_id' => $game->getCategoryId(),
            'created_at' => $game->getCreatedAt(),
            'updated_at' => $game->getUpdatedAt(),
        ];
    }
}
