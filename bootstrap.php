<?php

define('ROOTDIR', __DIR__ . DIRECTORY_SEPARATOR);

if (!file_exists(ROOTDIR . 'vendor/autoload.php')) {
    die("Vui lòng chạy lệnh 'composer install' hoặc sao chép thư mục 'vendor' vào dự án để tiếp tục.");
}

require_once ROOTDIR . 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOTDIR);
$dotenv->load();

try {
    $PDO = (new App\Models\PDOFactory())->create([
        'dbhost' => $_ENV['DB_HOST'] ?? 'localhost',
        'dbport' => $_ENV['DB_PORT'] ?? '5432',
        'dbname' => $_ENV['DB_NAME'] ?? 'nhatro_db',
        'dbuser' => $_ENV['DB_USER'] ?? 'postgres',
        'dbpass' => $_ENV['DB_PASS'] ?? '',
    ]);
} catch (Exception $ex) {
    http_response_code(500);
    exit('Không thể kết nối đến cơ sở dữ liệu PostgreSQL. Chi tiết lỗi: ' . $ex->getMessage());
}
