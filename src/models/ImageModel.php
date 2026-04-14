<?php

class ImageModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, string $filename): int
    {
        $stmt = $this->db->prepare('INSERT INTO images (user_id, filename) VALUES (?, ?)');
        $stmt->execute([$userId, $filename]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, u.username FROM images i
             JOIN users u ON i.user_id = u.id
             WHERE i.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getPaginated(int $page, int $perPage, ?int $userId = null): array
    {
        $offset = ($page - 1) * $perPage;
        if ($userId !== null) {
            $stmt = $this->db->prepare(
                'SELECT i.*, u.username FROM images i
                 JOIN users u ON i.user_id = u.id
                 WHERE i.user_id = ?
                 ORDER BY i.created_at DESC
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute([$userId, $perPage, $offset]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT i.*, u.username FROM images i
                 JOIN users u ON i.user_id = u.id
                 ORDER BY i.created_at DESC
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute([$perPage, $offset]);
        }
        return $stmt->fetchAll();
    }

    public function countAll(?int $userId = null): int
    {
        if ($userId !== null) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM images WHERE user_id = ?');
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM images');
            $stmt->execute();
        }
        return (int)$stmt->fetchColumn();
    }

    public function getAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM images WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM images WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}
