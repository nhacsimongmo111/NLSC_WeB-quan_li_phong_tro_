<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Tìm user theo username hoặc email (dùng cho form đăng nhập)
    public function findByUsernameOrEmail(string $keyword): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, username, full_name, email, phone, role, password_hash, avatar_url 
             FROM users 
             WHERE username = :kw OR email = :kw 
             LIMIT 1"
        );
        $stmt->execute(['kw' => $keyword]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, username, full_name, email, phone, role, avatar_url 
             FROM users 
             WHERE id = ? 
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }
}