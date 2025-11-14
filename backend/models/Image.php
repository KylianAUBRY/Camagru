<?php
require_once __DIR__ . '/../config/database.php';

class Image {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Créer une nouvelle image
    public function create($userId, $filename) {
        $query = "INSERT INTO images (user_id, filename) VALUES (:user_id, :filename)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':filename', $filename);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'image_id' => $this->db->lastInsertId()
            ];
        }

        return ['success' => false];
    }

    // Récupérer toutes les images avec les informations de l'utilisateur
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT i.*, u.username, 
                  (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes,
                  (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments
                  FROM images i
                  INNER JOIN users u ON i.user_id = u.id
                  ORDER BY i.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Récupérer les images d'un utilisateur
    public function getByUserId($userId, $limit = 50, $offset = 0) {
        $query = "SELECT i.*, u.username,
                  (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes,
                  (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments
                  FROM images i
                  INNER JOIN users u ON i.user_id = u.id
                  WHERE i.user_id = :user_id
                  ORDER BY i.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Récupérer une image par ID
    public function getById($id) {
        $query = "SELECT i.*, u.username,
                  (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as likes,
                  (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comments
                  FROM images i
                  INNER JOIN users u ON i.user_id = u.id
                  WHERE i.id = :id
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Supprimer une image
    public function delete($id, $userId) {
        // Vérifier que l'image appartient à l'utilisateur
        $query = "SELECT filename FROM images WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($image = $stmt->fetch()) {
            // Supprimer le fichier physique
            $filepath = UPLOAD_DIR . $image['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Supprimer de la base de données (les likes et commentaires seront supprimés automatiquement via CASCADE)
            $deleteQuery = "DELETE FROM images WHERE id = :id";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id);

            return $deleteStmt->execute();
        }

        return false;
    }

    // Ajouter un like
    public function addLike($userId, $imageId) {
        $query = "INSERT INTO likes (user_id, image_id) VALUES (:user_id, :image_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':image_id', $imageId);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Le like existe déjà (UNIQUE constraint)
            return false;
        }
    }

    // Retirer un like
    public function removeLike($userId, $imageId) {
        $query = "DELETE FROM likes WHERE user_id = :user_id AND image_id = :image_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':image_id', $imageId);

        return $stmt->execute();
    }

    // Vérifier si l'utilisateur a liké l'image
    public function hasLiked($userId, $imageId) {
        $query = "SELECT COUNT(*) as count FROM likes 
                  WHERE user_id = :user_id AND image_id = :image_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    // Ajouter un commentaire
    public function addComment($userId, $imageId, $comment) {
        $query = "INSERT INTO comments (user_id, image_id, comment) 
                  VALUES (:user_id, :image_id, :comment)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':comment', $comment);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    // Récupérer les commentaires d'une image
    public function getComments($imageId) {
        $query = "SELECT c.*, u.username 
                  FROM comments c
                  INNER JOIN users u ON c.user_id = u.id
                  WHERE c.image_id = :image_id
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Supprimer un commentaire
    public function deleteComment($commentId, $userId) {
        $query = "DELETE FROM comments WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $commentId);
        $stmt->bindParam(':user_id', $userId);

        return $stmt->execute();
    }
}
