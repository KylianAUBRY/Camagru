<?php

class LikeModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function toggle(int $imageId, int $userId): bool
    {
        if ($this->hasLiked($imageId, $userId)) {
            $stmt = $this->db->prepare('DELETE FROM likes WHERE image_id = ? AND user_id = ?');
            $stmt->execute([$imageId, $userId]);
            return false;
        } else {
            $stmt = $this->db->prepare('INSERT INTO likes (image_id, user_id) VALUES (?, ?)');
            $stmt->execute([$imageId, $userId]);
            return true;
        }
    }

    public function hasLiked(int $imageId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM likes WHERE image_id = ? AND user_id = ?'
        );
        $stmt->execute([$imageId, $userId]);
        return (bool)$stmt->fetch();
    }

    public function countByImage(int $imageId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE image_id = ?');
        $stmt->execute([$imageId]);
        return (int)$stmt->fetchColumn();
    }

    public function getLikedImageIds(int $userId, array $imageIds): array
    {
        if (empty($imageIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $params = array_merge([$userId], $imageIds);
        $stmt = $this->db->prepare(
            "SELECT image_id FROM likes WHERE user_id = ? AND image_id IN ($placeholders)"
        );
        $stmt->execute($params);
        return array_column($stmt->fetchAll(), 'image_id');
    }
}
