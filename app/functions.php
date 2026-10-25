<?php

// ==== Truy cập PDO toàn cục từ bất kỳ đâu (Model, Controller) ====
if (!function_exists('PDO')) {
    function PDO(): \PDO
    {
        global $PDO;
        return $PDO;
    }
}

// ==== Điều hướng ====
if (!function_exists('redirect')) {
    function redirect(string $location): void
    {
        header('Location: ' . $location, true, 302);
        exit();
    }
}

// ==== Đọc và xóa 1 biến trong session (dùng cho flash message / thông báo) ====
if (!function_exists('session_get_once')) {
    function session_get_once(string $name, $default = null)
    {
        $value = $default;
        if (isset($_SESSION[$name])) {
            $value = $_SESSION[$name];
            unset($_SESSION[$name]);
        }
        return $value;
    }
}

// ==== Chống XSS ====
if (!function_exists('html_escape')) {
    function html_escape(?string $string): string
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

// ==== Chống CSRF ====
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . html_escape(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(): bool
    {
        $submitted = $_POST['csrf_token'] ?? '';
        return !empty($submitted) && hash_equals($_SESSION['csrf_token'] ?? '', $submitted);
    }
}

// ==== Xác thực & phân quyền ====
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return ($_SESSION['role'] ?? '') === 'admin';
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        if (!is_logged_in()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'role' => $_SESSION['role'] ?? '',
        ];
    }
}

if (!function_exists('ensure_logged_in')) {
    function ensure_logged_in(): void
    {
        if (!is_logged_in()) {
            redirect('/login');
        }
    }
}

if (!function_exists('ensure_admin_access')) {
    function ensure_admin_access(): void
    {
        ensure_logged_in();
        if (!is_admin()) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập trang quản trị này!');
        }
    }
}

// ==== Định dạng hiển thị tiền tệ & ngày tháng ====
if (!function_exists('format_currency')) {
    function format_currency($amount): string
    {
        if ($amount === null || $amount === '') {
            return '0 đ';
        }
        return number_format((float)$amount, 0, ',', '.') . ' đ';
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $dateStr, string $format = 'd/m/Y'): string
    {
        if (!$dateStr) {
            return '';
        }
        $timestamp = strtotime($dateStr);
        return $timestamp ? date($format, $timestamp) : '';
    }
}
