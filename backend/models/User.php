<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Créer un nouvel utilisateur
    public function create($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));

        $query = "INSERT INTO users (username, email, password, verification_token) 
                  VALUES (:username, :email, :password, :token)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':token', $verificationToken);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'user_id' => $this->db->lastInsertId(),
                'verification_token' => $verificationToken
            ];
        }

        return ['success' => false, 'message' => 'Erreur lors de la création de l\'utilisateur'];
    }

    // Trouver un utilisateur par email ou username
    public function findByEmailOrUsername($identifier) {
        $query = "SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Trouver un utilisateur par ID
    public function findById($id) {
        $query = "SELECT id, username, email, is_verified, notifications_enabled, created_at 
                  FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Vérifier un utilisateur
    public function verify($token) {
        $query = "UPDATE users SET is_verified = TRUE, verification_token = NULL 
                  WHERE verification_token = :token";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    // Vérifier le mot de passe
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password']);
    }

    // Mettre à jour le token de réinitialisation
    public function setResetToken($email, $token, $expiry) {
        $query = "UPDATE users SET reset_token = :token, reset_token_expiry = :expiry 
                  WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }

    // Réinitialiser le mot de passe
    public function resetPassword($token, $newPassword) {
        $query = "SELECT id FROM users 
                  WHERE reset_token = :token AND reset_token_expiry > NOW()";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($user = $stmt->fetch()) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = :password, 
                           reset_token = NULL, reset_token_expiry = NULL 
                           WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $user['id']);

            return $updateStmt->execute();
        }

        return false;
    }

    // Mettre à jour les préférences de notification
    public function updateNotificationSettings($userId, $enabled) {
        $query = "UPDATE users SET notifications_enabled = :enabled WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':enabled', $enabled, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $userId);

        return $stmt->execute();
    }
}
