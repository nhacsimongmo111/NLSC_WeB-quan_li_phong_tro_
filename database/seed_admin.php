<?php

require_once __DIR__ . '/../bootstrap.php';

echo "=== TẠO TÀI KHOẢN ADMIN MẶC ĐỊNH ===\n";

$username = 'admin';
$password = 'admin123';
$email    = 'admin@nhatro.com';
$fullName = 'Quản Trị Viên';
$phone    = '0901234567';
$role     = 'admin';

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo = PDO();

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    $existing = $check->fetch();

    if ($existing) {
        $upd = $pdo->prepare("UPDATE users SET password_hash = ?, full_name = ?, role = ? WHERE id = ?");
        $upd->execute([$hash, $fullName, $role, $existing['id']]);
        echo "--> Đã CẬP NHẬT tài khoản '{$username}'.\n";
    } else {
        $ins = $pdo->prepare(
            "INSERT INTO users (username, email, password_hash, full_name, phone, role)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ins->execute([$username, $email, $hash, $fullName, $phone, $role]);
        echo "--> Đã TẠO MỚI tài khoản '{$username}'.\n";
    }

    echo "----------------------------------------\n";
    echo "URL Admin     : http://localhost:8000/login\n";
    echo "Tên đăng nhập : {$username}\n";
    echo "Mật khẩu      : {$password}\n";
    echo "URL Listing   : http://localhost:8000/phong-tro\n";
    echo "========================================\n";

} catch (Exception $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
}
