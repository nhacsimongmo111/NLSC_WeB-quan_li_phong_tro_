<?php

namespace App\Controllers;

use App\Models\Room;
use App\Models\RoomListing;

class RoomController extends Controller
{
    // GET /admin/rooms: Danh sách phòng trọ dạng bảng chuyên nghiệp
    public function index(): void
    {
        ensure_admin_access();

        $buildingId = !empty($_GET['building_id']) ? (int)$_GET['building_id'] : null;
        $status     = !empty($_GET['status']) ? trim($_GET['status']) : null;
        $keyword    = !empty($_GET['keyword']) ? trim($_GET['keyword']) : null;

        $roomModel = new Room(PDO());
        $rooms     = $roomModel->getAllWithFilters($buildingId, $status, $keyword);
        $buildings = $roomModel->getAllBuildings();
        $stats     = $roomModel->getSummaryStats();

        $this->render('rooms/index', [
            'rooms'         => $rooms,
            'buildings'     => $buildings,
            'stats'         => $stats,
            'currentBuilding' => $buildingId,
            'currentStatus'   => $status,
            'keyword'         => $keyword,
            'amenityLabels'   => RoomListing::AMENITY_LABELS,
            'flash_success'   => session_get_once('flash_success'),
            'flash_error'     => session_get_once('flash_error'),
        ]);
    }

    // GET /admin/rooms/create: Trang thêm phòng mới
    public function create(): void
    {
        ensure_admin_access();

        $roomModel = new Room(PDO());
        $buildings = $roomModel->getAllBuildings();
        $roomTypes = $roomModel->getAllRoomTypes();

        $this->render('rooms/create', [
            'buildings'     => $buildings,
            'roomTypes'     => $roomTypes,
            'amenityLabels' => RoomListing::AMENITY_LABELS,
            'flash_error'   => session_get_once('flash_error'),
            'oldInput'      => session_get_once('old_input', []),
        ]);
    }

    // POST /admin/rooms: Xử lý lưu phòng mới
    public function store(): void
    {
        ensure_admin_access();

        if (!verify_csrf_token()) {
            $_SESSION['flash_error'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại!';
            redirect('/admin/rooms/create');
        }

        $buildingId = (int)($_POST['building_id'] ?? 0);
        $roomNumber = trim($_POST['room_number'] ?? '');
        $rentPrice  = (float)($_POST['rent_price'] ?? 0);
        $areaM2     = (float)($_POST['area_m2'] ?? 0);

        $_SESSION['old_input'] = $_POST;

        if ($buildingId <= 0 || $roomNumber === '') {
            $_SESSION['flash_error'] = 'Vui lòng chọn cơ sở và nhập số phòng!';
            redirect('/admin/rooms/create');
        }

        if ($rentPrice <= 0 || $areaM2 <= 0) {
            $_SESSION['flash_error'] = 'Giá thuê và diện tích phải lớn hơn 0!';
            redirect('/admin/rooms/create');
        }

        $roomModel = new Room(PDO());
        if ($roomModel->isRoomNumberExists($buildingId, $roomNumber)) {
            $_SESSION['flash_error'] = "Số phòng '{$roomNumber}' đã tồn tại trong cơ sở này!";
            redirect('/admin/rooms/create');
        }

        $roomModel->create([
            'building_id'       => $buildingId,
            'room_type_id'      => !empty($_POST['room_type_id']) ? (int)$_POST['room_type_id'] : null,
            'room_number'       => $roomNumber,
            'area_m2'           => $areaM2,
            'rent_price'        => $rentPrice,
            'deposit_months'    => (int)($_POST['deposit_months'] ?? 1),
            'electricity_price' => (float)($_POST['electricity_price'] ?? 3500),
            'water_price'       => (float)($_POST['water_price'] ?? 15000),
            'amenities'         => $_POST['amenities'] ?? [],
            'max_occupants'     => (int)($_POST['max_occupants'] ?? 2),
            'available_from'    => !empty($_POST['available_from']) ? $_POST['available_from'] : null,
            'description'       => trim($_POST['description'] ?? ''),
            'status'            => $_POST['status'] ?? 'available',
            'is_published'      => isset($_POST['is_published']),
        ]);

        unset($_SESSION['old_input']);
        $_SESSION['flash_success'] = "Đã thêm phòng {$roomNumber} thành công!";
        redirect('/admin/rooms');
    }

    // GET /admin/rooms/{id}/edit: Trang sửa phòng
    public function edit(int $id): void
    {
        ensure_admin_access();

        $roomModel = new Room(PDO());
        $room = $roomModel->findRoomById($id);

        if (!$room) {
            http_response_code(404);
            require ROOTDIR . 'app/views/errors/404.php';
            return;
        }

        $buildings = $roomModel->getAllBuildings();
        $roomTypes = $roomModel->getAllRoomTypes();
        $amenities = RoomListing::parseAmenities($room['amenities']);

        $this->render('rooms/edit', [
            'room'          => $room,
            'buildings'     => $buildings,
            'roomTypes'     => $roomTypes,
            'amenities'     => $amenities,
            'amenityLabels' => RoomListing::AMENITY_LABELS,
            'flash_error'   => session_get_once('flash_error'),
        ]);
    }

    // POST /admin/rooms/{id}: Xử lý cập nhật phòng
    public function update(int $id): void
    {
        ensure_admin_access();

        if (!verify_csrf_token()) {
            $_SESSION['flash_error'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại!';
            redirect("/admin/rooms/{$id}/edit");
        }

        $buildingId = (int)($_POST['building_id'] ?? 0);
        $roomNumber = trim($_POST['room_number'] ?? '');
        $rentPrice  = (float)($_POST['rent_price'] ?? 0);
        $areaM2     = (float)($_POST['area_m2'] ?? 0);

        if ($buildingId <= 0 || $roomNumber === '') {
            $_SESSION['flash_error'] = 'Vui lòng chọn cơ sở và nhập số phòng!';
            redirect("/admin/rooms/{$id}/edit");
        }

        if ($rentPrice <= 0 || $areaM2 <= 0) {
            $_SESSION['flash_error'] = 'Giá thuê và diện tích phải lớn hơn 0!';
            redirect("/admin/rooms/{$id}/edit");
        }

        $roomModel = new Room(PDO());
        if ($roomModel->isRoomNumberExists($buildingId, $roomNumber, $id)) {
            $_SESSION['flash_error'] = "Số phòng '{$roomNumber}' đã tồn tại trong cơ sở này!";
            redirect("/admin/rooms/{$id}/edit");
        }

        $roomModel->update($id, [
            'building_id'       => $buildingId,
            'room_type_id'      => !empty($_POST['room_type_id']) ? (int)$_POST['room_type_id'] : null,
            'room_number'       => $roomNumber,
            'area_m2'           => $areaM2,
            'rent_price'        => $rentPrice,
            'deposit_months'    => (int)($_POST['deposit_months'] ?? 1),
            'electricity_price' => (float)($_POST['electricity_price'] ?? 3500),
            'water_price'       => (float)($_POST['water_price'] ?? 15000),
            'amenities'         => $_POST['amenities'] ?? [],
            'max_occupants'     => (int)($_POST['max_occupants'] ?? 2),
            'available_from'    => !empty($_POST['available_from']) ? $_POST['available_from'] : null,
            'description'       => trim($_POST['description'] ?? ''),
            'status'            => $_POST['status'] ?? 'available',
            'is_published'      => isset($_POST['is_published']),
        ]);

        $_SESSION['flash_success'] = "Đã cập nhật thông tin phòng {$roomNumber} thành công!";
        redirect('/admin/rooms');
    }

    // POST /admin/rooms/{id}/delete: Xóa phòng an toàn
    public function destroy(int $id): void
    {
        ensure_admin_access();

        if (!verify_csrf_token()) {
            $_SESSION['flash_error'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại!';
            redirect('/admin/rooms');
        }

        $roomModel = new Room(PDO());

        // Kiểm tra an toàn: cấm xóa phòng đang có hợp đồng active
        if ($roomModel->hasActiveContract($id)) {
            $_SESSION['flash_error'] = 'KHÔNG THỂ XÓA! Phòng này đang có hợp đồng thuê còn hiệu lực. Vui lòng thanh lý hoặc kết thúc hợp đồng trước.';
            redirect('/admin/rooms');
        }

        $room = $roomModel->findRoomById($id);
        $roomNumber = $room ? $room['room_number'] : "ID #{$id}";

        if ($roomModel->delete($id)) {
            $_SESSION['flash_success'] = "Đã xóa phòng {$roomNumber} thành công!";
        } else {
            $_SESSION['flash_error'] = "Không thể xóa phòng {$roomNumber} do có dữ liệu liên quan.";
        }

        redirect('/admin/rooms');
    }

    // POST /admin/rooms/{id}/toggle-published: Bật/tắt đăng tin qua AJAX
    public function togglePublished(int $id): void
    {
        ensure_admin_access();

        $roomModel = new Room(PDO());
        $newState = $roomModel->togglePublished($id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'is_published' => $newState]);
        exit();
    }

    // POST /admin/rooms/{id}/change-status: Đổi trạng thái nhanh qua AJAX
    public function changeStatus(int $id): void
    {
        ensure_admin_access();

        $status = trim($_POST['status'] ?? '');
        $roomModel = new Room(PDO());
        $ok = $roomModel->updateStatus($id, $status);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $ok, 'status' => $status]);
        exit();
    }
}
