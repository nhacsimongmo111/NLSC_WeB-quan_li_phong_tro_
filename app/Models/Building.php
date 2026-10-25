<?php

namespace App\Models;

use PDO;

class Building
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Lấy tất cả quận/huyện có cơ sở (dùng cho bộ lọc trang listing)
    public function getAllDistricts(): array
    {
        $stmt = $this->pdo->query(
            "SELECT DISTINCT district FROM buildings WHERE is_active = TRUE ORDER BY district ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Lấy phường/xã theo quận (dùng cho lọc cascade)
    public function getWardsByDistrict(string $district): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT ward FROM buildings WHERE district = ? AND is_active = TRUE ORDER BY ward ASC"
        );
        $stmt->execute([$district]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM buildings WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
