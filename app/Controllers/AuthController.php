<?php

namespace App\Controllers;

use App\Models\User;

class AuthController extends Controller
{
    // GET /login: Hiển thị form đăng nhập
    public function showLogin(): void
    {
        if (is_logged_in()) {
            // Đã đăng nhập thì đẩy về đúng trang theo role
            redirect(is_admin() ? '/admin/rooms' : '/phong-tro');
        }

        $this->render('auth/login', [
            'login_error' => session_get_once('login_error'),
        ]);
    }

    // POST /login: Xử lý đăng nhập
    public function login(): void
    {
        if (!verify_csrf_token()) {
            $_SESSION['login_error'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại!';
            redirect('/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = 'Vui lòng nhập đầy đủ tài khoản và mật khẩu!';
            redirect('/login');
        }

        // Khởi tạo Model (Sử dụng hàm PDO() helper)
        $userModel = new User(PDO());
        $user = $userModel->findByUsernameOrEmail($username);


        // Kiểm tra mật khẩu mã hóa bcrypt
        // if ($user && password_verify($password, $user['password_hash'])) {
        // Kiểm tra mật khẩu (chấp nhận cả chưa mã hóa trong lúc test DB cũ)
            if ($user && ($password === $user['password_hash'] || password_verify($password, $user['password_hash']))) {
            session_regenerate_id(true); // Chống Session Fixation
            
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['avatar']    = $user['avatar_url'];

            // Phân luồng điều hướng theo Role
            if ($user['role'] === 'admin') {
                redirect('/admin/rooms'); // Chủ trọ vào trang quản lý
            } else {
                redirect('/phong-tro');   // Khách thuê vào trang tìm/xem phòng
            }
        }

        $_SESSION['login_error'] = 'Tài khoản hoặc mật khẩu không chính xác!';
        redirect('/login');
    }

    // POST /logout: Xử lý đăng xuất
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        redirect('/login');
    }
}