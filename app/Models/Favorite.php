<?php

namespace App\Models;

use PDO;

class Favorite
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getFavorites(int $userId): array
    {
        $sql = "SELECT 
                    uf.id AS favorite_id, 
                    r.id AS room_id, 
                    r.room_number, 
                    r.rent_price, 
                    r.area_m2, 
                    b.name AS building_name, 
                    b.district, 
                    b.city,
                    uf.created_at AS saved_at
                FROM user_favorites uf
                JOIN rooms r ON uf.room_id = r.id
                JOIN buildings b ON r.building_id = b.id
                WHERE uf.user_id = :user_id 
                ORDER BY uf.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function toggleFavorite(int $userId, int $roomId): bool
    {
        $del = $this->db->prepare(
            'DELETE FROM user_favorites WHERE user_id = :user_id AND room_id = :room_id'
        );
        $del->execute(['user_id' => $userId, 'room_id' => $roomId]);

        if ($del->rowCount() > 0) {
            return false; // vừa bỏ lưu → tim xám
        }

        $ins = $this->db->prepare(
            'INSERT INTO user_favorites (user_id, room_id) VALUES (:user_id, :room_id)
         ON CONFLICT (user_id, room_id) DO NOTHING'
        );
        $ins->execute(['user_id' => $userId, 'room_id' => $roomId]);

        return true; // vừa lưu → tim đỏ
    }
}
