<?php

namespace App\Controllers;

use App\Models\Contract;

class ContractController extends Controller
{
    // GET /contracts: Trang danh sách hợp đồng
    public function index(): void
    {
        ensure_admin_access();

        $contractModel = new Contract(PDO());
        $contracts = $contractModel->getAllContracts();
        $availableRooms = $contractModel->getAvailableRooms();

        $this->render('contracts/index', [
            'contracts' => $contracts,
            'availableRooms' => $availableRooms
        ]);
    }

    // POST /contracts/store-ajax: Xử lý thêm mới qua fetch API
    public function storeAjax(): void
    {
        ensure_admin_access();
        header('Content-Type: application/json; charset=utf-8');

        if (!verify_csrf_token()) {
            echo json_encode(['success' => false, 'message' => 'Phiên làm việc hết hạn (CSRF lỗi).']);
            exit;
        }

        // Lấy và làm sạch dữ liệu
        $roomId = (int)($_POST['room_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $citizenId = trim($_POST['citizen_id'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $rentPrice = (float)($_POST['rent_price'] ?? 0);
        
        // Validate cơ bản
        $errors = [];
        if ($roomId <= 0) $errors[] = "Vui lòng chọn phòng.";
        if (empty($fullName)) $errors[] = "Họ tên không được để trống.";
        if (empty($citizenId)) $errors[] = "Số CCCD/CMND là bắt buộc.";
        if (empty($startDate)) $errors[] = "Ngày bắt đầu thuê là bắt buộc.";
        if ($rentPrice <= 0) $errors[] = "Giá thuê phải lớn hơn 0.";

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
            exit;
        }

        $tenantData = [
            'full_name'  => $fullName,
            'citizen_id' => $citizenId,
            'phone'      => $phone,
            'gender'     => $_POST['gender'] ?? null,
            'hometown'   => $_POST['hometown'] ?? null,
        ];

        $contractData = [
            'room_id'        => $roomId,
            'start_date'     => $startDate,
            'end_date'       => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'deposit_amount' => (float)($_POST['deposit_amount'] ?? 0),
            'rent_price'     => $rentPrice,
        ];

        $contractModel = new Contract(PDO());
        $result = $contractModel->createContractWithTenant($tenantData, $contractData);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Đã tạo hợp đồng mới thành công!"; // Để load lại trang báo thành công
            echo json_encode(['success' => true, 'redirect' => '/contracts']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $result['error']]);
        }
        exit;
    }
}