<?php

namespace App\Models;

use PDO;

/**
 * Model dành cho trang LISTING CÔNG KHAI
 * Chỉ trả về phòng trống (status = available) và đã bật is_published
 */
class RoomListing
{
    private PDO $pdo;

    // Ánh xạ key tiện ích sang nhãn hiển thị
    public const AMENITY_LABELS = [
        'wifi'       => ['label' => 'Wifi',         'icon' => 'bi-wifi'],
        'dieu_hoa'   => ['label' => 'Điều hòa',     'icon' => 'bi-thermometer-snow'],
        'wc_rieng'   => ['label' => 'WC riêng',     'icon' => 'bi-droplet-fill'],
        'cho_de_xe'  => ['label' => 'Chỗ để xe',    'icon' => 'bi-bicycle'],
        'bep_rieng'  => ['label' => 'Bếp riêng',    'icon' => 'bi-fire'],
        'may_giat'   => ['label' => 'Máy giặt',     'icon' => 'bi-gear-wide'],
        'ban_cong'   => ['label' => 'Ban công',     'icon' => 'bi-building'],
        'gac_lung'   => ['label' => 'Gác lửng',     'icon' => 'bi-layers-fill'],
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy danh sách phòng trống có bộ lọc + sắp xếp
     */
    public function getPublished(array $filters = [], string $sort = 'newest'): array
    {
        $where  = ["r.status = 'available'", "r.is_published = TRUE", "b.is_active = TRUE"];
        $params = [];

        if (!empty($filters['district'])) {
            $where[]             = "b.district = :district";
            $params['district']  = $filters['district'];
        }

        if (!empty($filters['ward'])) {
            $where[]         = "b.ward = :ward";
            $params['ward']  = $filters['ward'];
        }

        if (!empty($filters['room_type_id'])) {
            $where[]                = "r.room_type_id = :room_type_id";
            $params['room_type_id'] = (int)$filters['room_type_id'];
        }

        if (!empty($filters['min_price'])) {
            $where[]              = "r.rent_price >= :min_price";
            $params['min_price']  = (float)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[]              = "r.rent_price <= :max_price";
            $params['max_price']  = (float)$filters['max_price'];
        }

        if (!empty($filters['min_area'])) {
            $where[]             = "r.area_m2 >= :min_area";
            $params['min_area']  = (float)$filters['min_area'];
        }

        if (!empty($filters['max_area'])) {
            $where[]             = "r.area_m2 <= :max_area";
            $params['max_area']  = (float)$filters['max_area'];
        }

        // Lọc tiện ích (JSONB contains)
        if (!empty($filters['amenities']) && is_array($filters['amenities'])) {
            foreach ($filters['amenities'] as $idx => $amenity) {
                $key              = "amenity_{$idx}";
                $where[]          = "r.amenities @> :{$key}::jsonb";
                $params[$key]     = json_encode([$amenity]);
            }
        }

        // Tìm kiếm từ khóa
        if (!empty($filters['keyword'])) {
            $where[]             = "(b.name ILIKE :kw OR b.address ILIKE :kw OR b.ward ILIKE :kw OR b.district ILIKE :kw)";
            $params['kw']        = '%' . $filters['keyword'] . '%';
        }

        $orderBy = match ($sort) {
            'price_asc'  => "r.rent_price ASC",
            'price_desc' => "r.rent_price DESC",
            'area_asc'   => "r.area_m2 ASC",
            'area_desc'  => "r.area_m2 DESC",
            default      => "r.created_at DESC",
        };

        $sql = "
            SELECT
                r.id,
                r.room_number,
                r.area_m2,
                r.rent_price,
                r.deposit_months,
                r.max_occupants,
                r.amenities,
                r.available_from,
                r.description,
                r.created_at,
                rt.name  AS type_name,
                b.id     AS building_id,
                b.name   AS building_name,
                b.address,
                b.ward,
                b.district,
                b.city,
                b.phone  AS contact_phone,
                b.zalo   AS contact_zalo
            FROM rooms r
            LEFT JOIN buildings  b  ON r.building_id   = b.id
            LEFT JOIN room_types rt ON r.room_type_id   = rt.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY {$orderBy}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số kết quả (dùng phân trang nếu cần sau này)
     */
    public function countPublished(array $filters = []): int
    {
        $rows = $this->getPublished($filters);
        return count($rows);
    }

    /**
     * Chi tiết 1 phòng công khai
     */
    public function findPublishedById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                rt.name  AS type_name,
                b.name   AS building_name,
                b.address,
                b.ward,
                b.district,
                b.city,
                b.phone  AS contact_phone,
                b.zalo   AS contact_zalo,
                b.description AS building_desc
            FROM rooms r
            LEFT JOIN buildings  b  ON r.building_id  = b.id
            LEFT JOIN room_types rt ON r.room_type_id = rt.id
            WHERE r.id = :id AND r.status = 'available' AND r.is_published = TRUE
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Danh sách phòng liên quan cùng quận (gợi ý phía dưới trang chi tiết)
     */
    public function getRelated(int $roomId, string $district, int $limit = 4): array
    {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.room_number, r.area_m2, r.rent_price, r.max_occupants, r.amenities,
                   rt.name AS type_name,
                   b.name AS building_name, b.ward, b.district, b.address
            FROM rooms r
            LEFT JOIN buildings b ON r.building_id = b.id
            LEFT JOIN room_types rt ON r.room_type_id = rt.id
            WHERE r.id <> :id
              AND r.status = 'available'
              AND r.is_published = TRUE
              AND b.district = :district
            ORDER BY r.created_at DESC
            LIMIT {$limit}
        ");
        $stmt->execute(['id' => $roomId, 'district' => $district]);
        return $stmt->fetchAll();
    }

    /**
     * Parse amenities từ JSONB string thành array PHP
     */
    public static function parseAmenities(mixed $raw): array
    {
        if (is_array($raw)) return $raw;
        if (is_string($raw)) return json_decode($raw, true) ?? [];
        return [];
    }
}
