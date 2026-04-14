<?php

class CommentModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $imageId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (image_id, user_id, content) VALUES (?, ?, ?)'
        );
        $stmt->execute([$imageId, $userId, $content]);
        return (int)$this->db->lastInsertId();
    }

    public function getByImage(int $imageId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.username FROM comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.image_id = ?
             ORDER BY c.created_at ASC'
        );
        $stmt->execute([$imageId]);
        return $stmt->fetchAll();
    }

    public function countByImage(int $imageId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE image_id = ?');
        $stmt->execute([$imageId]);
        return (int)$stmt->fetchColumn();
    }
}
