<?php

namespace App\Controllers;

use App\Models\RoomListing;
use App\Models\Building;
use App\Models\RoomInquiry;
use App\Models\Room;

class ListingController extends Controller
{
    // GET /phong-tro — Trang danh sách phòng trống công khai
    public function index(): void
    {
        $filters = [
            'district'     => trim($_GET['district']   ?? ''),
            'ward'         => trim($_GET['ward']        ?? ''),
            'room_type_id' => $_GET['room_type_id']    ?? '',
            'min_price'    => $_GET['min_price']        ?? '',
            'max_price'    => $_GET['max_price']        ?? '',
            'min_area'     => $_GET['min_area']         ?? '',
            'max_area'     => $_GET['max_area']         ?? '',
            'keyword'      => trim($_GET['keyword']     ?? ''),
            'amenities'    => $_GET['amenities']        ?? [],
        ];

        $sort = $_GET['sort'] ?? 'newest';

        $listingModel  = new RoomListing(PDO());
        $buildingModel = new Building(PDO());

        $rooms     = $listingModel->getPublished($filters, $sort);
        $districts = $buildingModel->getAllDistricts();

        // Lấy phường nếu đã chọn quận
        $wards = [];
        if (!empty($filters['district'])) {
            $wards = $buildingModel->getWardsByDistrict($filters['district']);
        }

        $this->render('listing/index', [
            'rooms'         => $rooms,
            'filters'       => $filters,
            'sort'          => $sort,
            'districts'     => $districts,
            'wards'         => $wards,
            'totalResults'  => count($rooms),
            'amenityLabels' => RoomListing::AMENITY_LABELS,
        ]);
    }

    // GET /phong-tro/{id} — Chi tiết phòng
    public function show(int $id): void
    {
        $listingModel = new RoomListing(PDO());
        $room = $listingModel->findPublishedById($id);

        if (!$room) {
            http_response_code(404);
            require ROOTDIR . 'app/views/errors/404.php';
            return;
        }

        $amenities   = RoomListing::parseAmenities($room['amenities']);
        $related     = $listingModel->getRelated($id, $room['district']);

        $this->render('listing/show', [
            'room'          => $room,
            'amenities'     => $amenities,
            'related'       => $related,
            'amenityLabels' => RoomListing::AMENITY_LABELS,
            'flash_success' => session_get_once('inquiry_success'),
            'flash_error'   => session_get_once('inquiry_error'),
        ]);
    }

    // POST /phong-tro/{id}/lien-he — Gửi form yêu cầu xem phòng
    public function inquiry(int $id): void
    {
        if (!verify_csrf_token()) {
            $_SESSION['inquiry_error'] = 'Phiên đã hết hạn, vui lòng thử lại.';
            redirect("/phong-tro/{$id}");
        }

        $name  = trim($_POST['visitor_name']  ?? '');
        $phone = trim($_POST['visitor_phone'] ?? '');
        $msg   = trim($_POST['message']       ?? '');

        if ($name === '' || $phone === '') {
            $_SESSION['inquiry_error'] = 'Vui lòng nhập đầy đủ họ tên và số điện thoại.';
            redirect("/phong-tro/{$id}");
        }

        $model = new RoomInquiry(PDO());
        $ok = $model->create([
            'room_id'       => $id,
            'visitor_name'  => $name,
            'visitor_phone' => $phone,
            'message'       => $msg,
        ]);

        if ($ok) {
            $_SESSION['inquiry_success'] = 'Yêu cầu của bạn đã được gửi! Chủ trọ sẽ liên hệ lại trong thời gian sớm nhất.';
        } else {
            $_SESSION['inquiry_error'] = 'Có lỗi xảy ra, vui lòng thử lại.';
        }

        redirect("/phong-tro/{$id}");
    }

    // POST /api/phuong — Trả về JSON danh sách phường theo quận (AJAX)
    public function getWards(): void
    {
        $district = trim($_GET['district'] ?? '');
        if ($district === '') {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit();
        }

        $model = new Building(PDO());
        $wards = $model->getWardsByDistrict($district);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($wards, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
