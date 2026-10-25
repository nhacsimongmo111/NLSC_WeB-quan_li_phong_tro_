<?php

namespace App\Models;

use PDO;

class Room
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Lấy thống kê số lượng phòng theo trạng thái
    public function getSummaryStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_rooms,
                    COUNT(CASE WHEN status = 'available' THEN 1 END) as available_rooms,
                    COUNT(CASE WHEN status = 'rented' THEN 1 END) as rented_rooms,
                    COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance_rooms
                FROM rooms";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result ?: [
            'total_rooms' => 0,
            'available_rooms' => 0,
            'rented_rooms' => 0,
            'maintenance_rooms' => 0,
        ];
    }

    // Lấy danh sách tất cả phòng kèm bộ lọc tìm kiếm cho trang Admin
    public function getAllWithFilters(?int $buildingId = null, ?string $status = null, ?string $keyword = null): array
    {
        $where = ["1=1"];
        $params = [];

        if ($buildingId) {
            $where[] = "r.building_id = :building_id";
            $params['building_id'] = $buildingId;
        }

        if ($status && in_array($status, ['available', 'rented', 'maintenance'])) {
            $where[] = "r.status = :status";
            $params['status'] = $status;
        }

        if ($keyword) {
            $where[] = "(r.room_number ILIKE :kw OR b.name ILIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        $sql = "SELECT 
                    r.id, 
                    r.building_id,
                    r.room_type_id,
                    r.room_number, 
                    r.status, 
                    r.is_published,
                    r.rent_price,
                    r.rent_price as base_price,
                    r.area_m2,
                    r.max_occupants,
                    r.deposit_months,
                    r.electricity_price,
                    r.water_price,
                    r.amenities,
                    r.available_from,
                    r.description,
                    r.created_at,
                    rt.name as type_name, 
                    b.name as building_name,
                    b.district,
                    b.ward,
                    c.id as contract_id,
                    c.start_date as contract_start,
                    c.end_date as contract_end,
                    t.full_name as current_tenant_name,
                    t.phone as current_tenant_phone
                FROM rooms r
                LEFT JOIN buildings b ON r.building_id = b.id
                LEFT JOIN room_types rt ON r.room_type_id = rt.id
                LEFT JOIN contracts c ON c.room_id = r.id AND c.status = 'active'
                LEFT JOIN tenants t ON c.tenant_id = t.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY b.id ASC, r.room_number ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Dùng cho Dashboard
    public function getAllWithDetails(?string $statusFilter = null): array
    {
        return $this->getAllWithFilters(null, $statusFilter, null);
    }

    // Lấy chi tiết 1 phòng theo ID
    public function findRoomById(int $id): ?array
    {
        $sql = "SELECT 
                    r.*,
                    rt.name as type_name, 
                    b.name as building_name,
                    b.district,
                    b.ward
                FROM rooms r
                LEFT JOIN buildings b ON r.building_id = b.id
                LEFT JOIN room_types rt ON r.room_type_id = rt.id
                WHERE r.id = ? 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $room = $stmt->fetch();
        return $room ?: null;
    }

    // Lấy tất cả danh sách cơ sở/mànhà
    public function getAllBuildings(): array
    {
        $stmt = $this->pdo->query("SELECT id, name, address, ward, district FROM buildings WHERE is_active = TRUE ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    // Lấy tất cả loại phòng
    public function getAllRoomTypes(): array
    {
        $stmt = $this->pdo->query("SELECT id, name, description FROM room_types ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    // Kiểm tra xem số phòng đã tồn tại trong cùng 1 cơ sở chưa
    public function isRoomNumberExists(int $buildingId, string $roomNumber, ?int $excludeRoomId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM rooms WHERE building_id = :b_id AND room_number = :r_num";
        $params = ['b_id' => $buildingId, 'r_num' => $roomNumber];

        if ($excludeRoomId) {
            $sql .= " AND id <> :ex_id";
            $params['ex_id'] = $excludeRoomId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Thêm phòng mới
    public function create(array $data): int
    {
        $sql = "INSERT INTO rooms (
                    building_id, room_type_id, room_number, area_m2, rent_price,
                    deposit_months, electricity_price, water_price, amenities,
                    max_occupants, available_from, description, status, is_published
                ) VALUES (
                    :building_id, :room_type_id, :room_number, :area_m2, :rent_price,
                    :deposit_months, :electricity_price, :water_price, :amenities,
                    :max_occupants, :available_from, :description, :status, :is_published
                ) RETURNING id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'building_id'       => $data['building_id'],
            'room_type_id'      => !empty($data['room_type_id']) ? $data['room_type_id'] : null,
            'room_number'       => $data['room_number'],
            'area_m2'           => (float)$data['area_m2'],
            'rent_price'        => (float)$data['rent_price'],
            'deposit_months'    => (int)($data['deposit_months'] ?? 1),
            'electricity_price' => (float)($data['electricity_price'] ?? 3500),
            'water_price'       => (float)($data['water_price'] ?? 15000),
            'amenities'         => json_encode($data['amenities'] ?? []),
            'max_occupants'     => (int)($data['max_occupants'] ?? 2),
            'available_from'    => !empty($data['available_from']) ? $data['available_from'] : null,
            'description'       => $data['description'] ?? null,
            'status'            => $data['status'] ?? 'available',
            'is_published'      => !empty($data['is_published']) ? 'true' : 'false',
        ]);

        return (int)$stmt->fetchColumn();
    }

    // Cập nhật thông tin phòng
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE rooms SET
                    building_id       = :building_id,
                    room_type_id      = :room_type_id,
                    room_number       = :room_number,
                    area_m2           = :area_m2,
                    rent_price        = :rent_price,
                    deposit_months    = :deposit_months,
                    electricity_price = :electricity_price,
                    water_price       = :water_price,
                    amenities         = :amenities,
                    max_occupants     = :max_occupants,
                    available_from    = :available_from,
                    description       = :description,
                    status            = :status,
                    is_published      = :is_published
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id'                => $id,
            'building_id'       => $data['building_id'],
            'room_type_id'      => !empty($data['room_type_id']) ? $data['room_type_id'] : null,
            'room_number'       => $data['room_number'],
            'area_m2'           => (float)$data['area_m2'],
            'rent_price'        => (float)$data['rent_price'],
            'deposit_months'    => (int)($data['deposit_months'] ?? 1),
            'electricity_price' => (float)($data['electricity_price'] ?? 3500),
            'water_price'       => (float)($data['water_price'] ?? 15000),
            'amenities'         => json_encode($data['amenities'] ?? []),
            'max_occupants'     => (int)($data['max_occupants'] ?? 2),
            'available_from'    => !empty($data['available_from']) ? $data['available_from'] : null,
            'description'       => $data['description'] ?? null,
            'status'            => $data['status'] ?? 'available',
            'is_published'      => !empty($data['is_published']) ? 'true' : 'false',
        ]);
    }

    // Kiểm tra xem phòng có hợp đồng active không (để cấm xóa)
    public function hasActiveContract(int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM contracts WHERE room_id = ? AND status = 'active'");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Xóa phòng
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Đổi trạng thái đăng tin công khai (Toggle is_published)
    public function togglePublished(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE rooms SET is_published = NOT is_published WHERE id = ? RETURNING is_published");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    // Đổi trạng thái phòng (available / rented / maintenance)
    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['available', 'rented', 'maintenance'])) {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
