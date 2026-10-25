<?php

namespace App\Models;

class Contract
{
    private \PDO $db;

    public function __construct(\PDO $pdo)
    {
        $this->db = $pdo;
    }

    // Lấy danh sách tất cả hợp đồng kèm thông tin phòng và khách
    public function getAllContracts(): array
    {
        $sql = "SELECT c.*, 
                       r.room_number, b.name AS building_name,
                       t.full_name AS tenant_name, t.phone AS tenant_phone, t.citizen_id
                FROM contracts c
                JOIN rooms r ON c.room_id = r.id
                JOIN buildings b ON r.building_id = b.id
                JOIN tenants t ON c.tenant_id = t.id
                ORDER BY c.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Lấy danh sách các phòng đang trống để đưa vào Form Select
    public function getAvailableRooms(): array
    {
        $sql = "SELECT r.id, r.room_number, r.rent_price, r.deposit_months, b.name AS building_name 
                FROM rooms r
                JOIN buildings b ON r.building_id = b.id
                WHERE r.status = 'available'
                ORDER BY b.id, r.room_number";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Giao dịch (Transaction) tạo hợp đồng mới & khách thuê
    public function createContractWithTenant(array $tenantData, array $contractData): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Xử lý khách thuê (Tenant)
            // Kiểm tra xem CCCD đã tồn tại chưa
            $stmtCheck = $this->db->prepare("SELECT id FROM tenants WHERE citizen_id = ?");
            $stmtCheck->execute([$tenantData['citizen_id']]);
            $existingTenant = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            if ($existingTenant) {
                $tenantId = $existingTenant['id'];
                // Có thể UPDATE thêm thông tin mới nhất của khách nếu cần
            } else {
                $stmtInsertTenant = $this->db->prepare("
                    INSERT INTO tenants (full_name, citizen_id, phone, gender, hometown, birth_date) 
                    VALUES (?, ?, ?, ?, ?, ?) RETURNING id
                ");
                $stmtInsertTenant->execute([
                    $tenantData['full_name'],
                    $tenantData['citizen_id'],
                    $tenantData['phone'],
                    $tenantData['gender'] ?? null,
                    $tenantData['hometown'] ?? null,
                    $tenantData['birth_date'] ?? null
                ]);
                $tenantId = $stmtInsertTenant->fetchColumn();
            }

            // 2. Tạo Hợp đồng (Contract)
            // Sinh mã hợp đồng tự động: HD-{Mã Phòng}-{ThángNăm}
            $contractCode = 'HD-' . $contractData['room_id'] . '-' . date('my') . rand(10,99);

            $stmtInsertContract = $this->db->prepare("
                INSERT INTO contracts (contract_code, room_id, tenant_id, start_date, end_date, deposit_amount, rent_price, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $stmtInsertContract->execute([
                $contractCode,
                $contractData['room_id'],
                $tenantId,
                $contractData['start_date'],
                $contractData['end_date'] ?: null,
                $contractData['deposit_amount'],
                $contractData['rent_price']
            ]);

            // 3. Cập nhật trạng thái phòng sang 'rented'
            $stmtUpdateRoom = $this->db->prepare("UPDATE rooms SET status = 'rented' WHERE id = ?");
            $stmtUpdateRoom->execute([$contractData['room_id']]);

            $this->db->commit();
            return ['success' => true, 'contract_code' => $contractCode];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}