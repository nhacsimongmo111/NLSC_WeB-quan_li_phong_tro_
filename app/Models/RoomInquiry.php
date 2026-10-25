<?php

namespace App\Models;

use PDO;

class RoomInquiry
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO room_inquiries (room_id, visitor_name, visitor_phone, message)
            VALUES (:room_id, :visitor_name, :visitor_phone, :message)
        ");
        return $stmt->execute([
            'room_id'       => $data['room_id'],
            'visitor_name'  => $data['visitor_name'],
            'visitor_phone' => $data['visitor_phone'],
            'message'       => $data['message'] ?? null,
        ]);
    }

    // Dùng trong trang admin: xem yêu cầu chưa đọc
    public function getUnread(): array
    {
        $stmt = $this->pdo->query("
            SELECT qi.*, r.room_number, b.name AS building_name
            FROM room_inquiries qi
            JOIN rooms r ON qi.room_id = r.id
            JOIN buildings b ON r.building_id = b.id
            WHERE qi.is_read = FALSE
            ORDER BY qi.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
