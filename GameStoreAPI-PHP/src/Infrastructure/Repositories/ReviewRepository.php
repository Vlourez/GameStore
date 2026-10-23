<?php

declare(strict_types=1);

namespace GameStore\Infrastructure\Repositories;

use GameStore\Domain\Common\PagedResult;
use GameStore\Domain\Entities\Review;
use GameStore\Domain\Interfaces\ReviewRepositoryInterface;
use PDO;

final class ReviewRepository implements ReviewRepositoryInterface
{
    private const BASE_SELECT = <<<SQL
        SELECT r.*, c.full_name AS customer_name, g.title AS game_title
        FROM reviews r
        JOIN customers c ON c.id = r.customer_id
        JOIN games g ON g.id = r.game_id
        SQL;

    public function __construct(private readonly PDO $db)
    {
    }

    public function getById(string $id): ?Review
    {
        $stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE r.id = :id AND r.is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Review::hydrate($row) : null;
    }

    public function add(Review $review): void
    {
        // Libera a chave única (customer_id, game_id) caso exista uma review antiga marcada como apagada.
        $purge = $this->db->prepare(
            'DELETE FROM reviews WHERE customer_id = :customer_id AND game_id = :game_id AND is_deleted = 1'
        );
        $purge->execute(['customer_id' => $review->getCustomerId(), 'game_id' => $review->getGameId()]);

        $stmt = $this->db->prepare(
            'INSERT INTO reviews
                (id, customer_id, game_id, rating, title, body, is_recommended, created_at, updated_at, is_deleted)
             VALUES
                (:id, :customer_id, :game_id, :rating, :title, :body, :is_recommended, :created_at, :updated_at, 0)'
        );
        $stmt->execute($this->toParams($review));
    }

    public function update(Review $review): void
    {
        $stmt = $this->db->prepare(
            'UPDATE reviews SET rating = :rating, title = :title, body = :body, is_recommended = :is_recommended,
             updated_at = :updated_at, is_deleted = :is_deleted WHERE id = :id'
        );
        $params = $this->toParams($review);
        $params['is_deleted'] = (int) $review->isDeleted();
        unset($params['customer_id'], $params['game_id'], $params['created_at']);
        $stmt->execute($params);
    }

    public function remove(Review $review): void
    {
        $this->update($review);
    }

    public function getByCustomerAndGame(string $customerId, string $gameId): ?Review
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE r.customer_id = :customerId AND r.game_id = :gameId AND r.is_deleted = 0'
        );
        $stmt->execute(['customerId' => $customerId, 'gameId' => $gameId]);
        $row = $stmt->fetch();

        return $row ? Review::hydrate($row) : null;
    }

    public function getByGame(string $gameId): array
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE r.game_id = :gameId AND r.is_deleted = 0 ORDER BY r.created_at DESC'
        );
        $stmt->execute(['gameId' => $gameId]);

        return array_map(Review::hydrate(...), $stmt->fetchAll());
    }

    public function getPaged(int $page, int $pageSize, string $gameId): PagedResult
    {
        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM reviews WHERE game_id = :gameId AND is_deleted = 0');
        $countStmt->execute(['gameId' => $gameId]);
        $totalCount = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $pageSize;
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE r.game_id = :gameId AND r.is_deleted = 0
             ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':gameId', $gameId);
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = array_map(Review::hydrate(...), $stmt->fetchAll());

        return new PagedResult($items, $totalCount, $page, $pageSize);
    }

    /** @return array<string,mixed> */
    private function toParams(Review $review): array
    {
        return [
            'id' => $review->getId(),
            'customer_id' => $review->getCustomerId(),
            'game_id' => $review->getGameId(),
            'rating' => $review->getRating(),
            'title' => $review->getTitle(),
            'body' => $review->getBody(),
            'is_recommended' => (int) $review->isRecommended(),
            'created_at' => $review->getCreatedAt(),
            'updated_at' => $review->getUpdatedAt(),
        ];
    }
}
