<?php

class Review {
    /**
     * Retrieve approved reviews for public display, optionally filtered by type.
     */
    public static function all(?string $type = null, string $status = 'approved', int $limit = 50): array {
        $params = [];
        $sql = 'SELECT r.*, u.name as user_name, u.avatar as user_avatar 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE 1=1';

        if ($status !== null) {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }

        if ($type !== null && in_array($type, ['compliment', 'complaint'], true)) {
            $sql .= ' AND r.type = ?';
            $params[] = $type;
        }

        $sql .= ' ORDER BY r.created_at DESC LIMIT ' . (int)$limit;
        return Database::rows($sql, $params);
    }

    /**
     * Retrieve all reviews for administration management.
     */
    public static function allForAdmin(): array {
        return Database::rows(
            'SELECT r.*, u.name as user_name, u.email as user_email, u.avatar as user_avatar 
             FROM reviews r 
             LEFT JOIN users u ON r.user_id = u.id 
             ORDER BY r.created_at DESC'
        );
    }

    /**
     * Get review statistics (average rating, count of compliments, count of complaints).
     */
    public static function stats(): array {
        $row = Database::row(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN type = 'compliment' THEN 1 ELSE 0 END) as compliments,
                SUM(CASE WHEN type = 'complaint' THEN 1 ELSE 0 END) as complaints,
                AVG(rating) as avg_rating
             FROM reviews 
             WHERE status = 'approved'"
        );

        return [
            'total'       => (int)($row['total'] ?? 0),
            'compliments' => (int)($row['compliments'] ?? 0),
            'complaints'  => (int)($row['complaints'] ?? 0),
            'avg_rating'  => $row['avg_rating'] !== null ? round((float)$row['avg_rating'], 1) : 5.0,
        ];
    }

    /**
     * Find single review by ID.
     */
    public static function findById(int $id): ?array {
        return Database::row('SELECT * FROM reviews WHERE id = ?', [$id]);
    }

    /**
     * Create a new review submission.
     */
    public static function create(array $d): int {
        Database::query(
            'INSERT INTO reviews (user_id, name, email, type, rating, title, content, visit_date, status, admin_reply) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $d['user_id'] ?? null,
                $d['name'],
                $d['email'] ?? null,
                $d['type'] === 'complaint' ? 'complaint' : 'compliment',
                max(1, min(5, (int)($d['rating'] ?? 5))),
                $d['title'] ?? null,
                $d['content'],
                $d['visit_date'] ?? null,
                $d['status'] ?? 'approved',
                $d['admin_reply'] ?? null
            ]
        );
        return (int)Database::lastInsertId();
    }

    /**
     * Update moderation status of a review.
     */
    public static function updateStatus(int $id, string $status): void {
        if (in_array($status, ['approved', 'pending', 'hidden'], true)) {
            Database::query('UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
        }
    }

    /**
     * Save an official management reply.
     */
    public static function updateReply(int $id, ?string $reply): void {
        Database::query('UPDATE reviews SET admin_reply = ?, updated_at = NOW() WHERE id = ?', [$reply, $id]);
    }

    /**
     * Delete review.
     */
    public static function delete(int $id): void {
        Database::query('DELETE FROM reviews WHERE id = ?', [$id]);
    }
}
