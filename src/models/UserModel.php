<?php

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $username, string $email, string $password): int
    {
        $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $token = bin2hex(random_bytes(32));
        $stmt  = $this->db->prepare(
            'INSERT INTO users (username, email, password, verify_token) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hash, $token]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findByVerifyToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE verify_token = ?');
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function verify(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    public function setResetToken(int $id, string $token): void
    {
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?'
        );
        $stmt->execute([$token, $expires, $id]);
    }

    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            'UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?'
        );
        $stmt->execute([$hash, $id]);
    }

    public function updateProfile(int $id, string $username, string $email, int $notifyComments): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET username = ?, email = ?, notify_comments = ? WHERE id = ?'
        );
        $stmt->execute([$username, $email, $notifyComments, $id]);
    }

    public function usernameExists(string $username, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $stmt->execute([$username, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $excludeId]);
        return (bool)$stmt->fetch();
    }
}
