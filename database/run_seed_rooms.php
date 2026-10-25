<?php

require_once __DIR__ . '/../bootstrap.php';

echo "=== ĐANG BỔ SUNG DỮ LIỆU PHÒNG MẪU VÀO CSDL ===\n";

$sqlFile = __DIR__ . '/add_more_rooms.sql';
if (!file_exists($sqlFile)) {
    die("Không tìm thấy file add_more_rooms.sql\n");
}

$sql = file_get_contents($sqlFile);

try {
    $pdo = PDO();
    $pdo->exec($sql);
    echo "--> BỔ SUNG THÀNH CÔNG 11 PHÒNG MẪU VÀ 2 CƠ SỞ MỚI!\n";

    // Đếm tổng số phòng sau khi nạp
    $count = $pdo->query("SELECT COUNT(*) FROM rooms WHERE is_published = TRUE AND status = 'available'")->fetchColumn();
    echo "--> Tổng số phòng trống công khai hiện tại: {$count} phòng.\n";
    echo "=============================================\n";
} catch (Exception $e) {
    echo "LỖI KHI NẠP: " . $e->getMessage() . "\n";
}
