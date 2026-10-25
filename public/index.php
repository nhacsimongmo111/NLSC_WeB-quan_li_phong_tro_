<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../bootstrap.php';

session_start();

$router = new \Bramus\Router\Router();

// ============================================================
// TRANG CÔNG KHAI — LISTING PHÒNG TRỌ
// ============================================================
$router->get('/phong-tro',                   '\App\Controllers\ListingController@index');
$router->get('/phong-tro/(\d+)',             '\App\Controllers\ListingController@show');
$router->post('/phong-tro/(\d+)/lien-he',   '\App\Controllers\ListingController@inquiry');

// AJAX: lấy phường/xã theo quận
$router->get('/api/phuong',                  '\App\Controllers\ListingController@getWards');

// Trang chủ → chuyển hướng sang listing
$router->get('/', function () {
    header('Location: /phong-tro', true, 302);
    exit();
});

// ============================================================
// TRANG QUẢN TRỊ — ADMIN (Chủ trọ)
// ============================================================
$router->get('/admin',          '\App\Controllers\HomeController@index');
$router->get('/admin/dashboard','\App\Controllers\HomeController@index');

// Đăng nhập admin
$router->get('/login',          '\App\Controllers\AuthController@showLogin');
$router->post('/login',         '\App\Controllers\AuthController@login');
$router->post('/logout',        '\App\Controllers\AuthController@logout');

// Quản lý phòng trọ (CRUD)
$router->get('/admin/rooms',                         '\App\Controllers\RoomController@index');
$router->get('/admin/rooms/create',                  '\App\Controllers\RoomController@create');
$router->post('/admin/rooms',                        '\App\Controllers\RoomController@store');
$router->get('/admin/rooms/(\d+)/edit',              '\App\Controllers\RoomController@edit');
$router->post('/admin/rooms/(\d+)',                  '\App\Controllers\RoomController@update');
$router->post('/admin/rooms/(\d+)/delete',           '\App\Controllers\RoomController@destroy');
$router->post('/admin/rooms/(\d+)/toggle-published', '\App\Controllers\RoomController@togglePublished');
$router->post('/admin/rooms/(\d+)/change-status',     '\App\Controllers\RoomController@changeStatus');

// Redirect cũ /rooms sang /admin/rooms
$router->get('/rooms', function () {
    header('Location: /admin/rooms', true, 302);
    exit();
});

// Quản lý khách & hợp đồng (Tuần 2)
$router->get('/contracts',                            '\App\Controllers\ContractController@index');
$router->post('/contracts/store-ajax',                '\App\Controllers\ContractController@storeAjax');

$router->get('/admin/contracts', function () {
    header('Location: /contracts', true, 302);
    exit();
});
// Trang yêu thích 
$router->get('/yeu-thich',                 '\App\Controllers\FavoriteController@index');
$router->post('/yeu-thich/toggle/(\d+)',   '\App\Controllers\FavoriteController@toggle');
// Điện nước & hóa đơn (Tuần 3)
// $router->get('/admin/invoices',           '\App\Controllers\InvoiceController@index');

// Báo cáo doanh thu (Tuần 3)
// $router->get('/admin/reports',            '\App\Controllers\ReportController@index');

// 404
$router->set404(function () {
    http_response_code(404);
    require ROOTDIR . 'app/views/errors/404.php';
});

$router->run();
