<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập | Nhà Trọ Cần Thơ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?q=80&w=2070&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.75); /* Lớp phủ nền tối */
            z-index: 1;
        }
        .container { position: relative; z-index: 2; }
        
        /* Box form đăng nhập 2 cột */
        .login-wrapper {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 850px;
            width: 100%;
            overflow: hidden;
            min-height: 500px;
        }

        /* Minh họa bên trái */
        .illustration-section {
            position: relative;
            background-color: #ffffff;
        }
        .circle-bg {
            width: 280px;
            height: 280px;
            background-color: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .floating-shape {
            position: absolute;
            color: #10b981; /* Xanh lá nhạt tạo điểm nhấn giống ảnh mẫu */
            font-size: 1.2rem;
            animation: float 3s ease-in-out infinite;
        }
        .shape-1 { top: 20%; left: 10%; color: #3b82f6; }
        .shape-2 { top: 30%; right: 10%; color: #8b5cf6; }
        .shape-3 { bottom: 25%; left: 15%; color: #10b981; }
        .shape-4 { bottom: 20%; right: 15%; color: #06b6d4; }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Form bên phải */
        .form-section {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Input bo tròn dạng viên thuốc */
        .input-wrapper {
            position: relative;
            margin-bottom: 1.25rem;
        }
        .input-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            z-index: 10;
        }
        .form-control-custom {
            background-color: #f3f4f6;
            border: 2px solid transparent;
            border-radius: 50px;
            padding: 0.85rem 1.25rem 0.85rem 3rem;
            width: 100%;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-control-custom:focus {
            outline: none;
            background-color: #ffffff;
            border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }
        
        /* Nút bấm Cam */
        .btn-orange-pill {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border: none;
            border-radius: 50px;
            color: white;
            padding: 0.85rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.2);
        }
        .btn-orange-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.4);
            color: white;
        }

        /* Responsive: Ẩn hình minh họa trên điện thoại */
        @media (max-width: 767px) {
            .illustration-section { display: none !important; }
            .form-section { padding: 2.5rem 1.5rem; }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="login-wrapper mx-auto row g-0">
            
            <!-- Cột Trái: Hình minh họa -->
            <div class="col-md-6 illustration-section d-flex align-items-center justify-content-center border-end">
                <div class="circle-bg">
                    <!-- Icon Laptop với User bên trong -->
                    <i class="bi bi-person-workspace text-secondary" style="font-size: 8rem; color: #475569 !important;"></i>
                    
                    <!-- Các họa tiết trang trí trôi nổi -->
                    <i class="bi bi-circle floating-shape shape-1"></i>
                    <i class="bi bi-triangle floating-shape shape-2"></i>
                    <i class="bi bi-square floating-shape shape-3"></i>
                    <i class="bi bi-circle-fill floating-shape shape-4" style="font-size: 0.6rem;"></i>
                </div>
            </div>

            <!-- Cột Phải: Form đăng nhập -->
            <div class="col-md-6 form-section">
                <div class="w-100 mx-auto" style="max-width: 320px;">
                    <h3 class="fw-bold mb-4 text-center text-dark" style="font-size: 1.5rem;">Đăng Nhập</h3>

                    <?php if (!empty($login_error)): ?>
                        <div class="alert alert-danger border-0 small py-2 px-3 mb-4 rounded-3 text-center" style="background-color: #fef2f2; color: #991b1b;">
                            <?= htmlspecialchars($login_error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="/login" method="POST">
                        <?= csrf_field() ?? '' ?>

                        <!-- Bỏ nhãn, tích hợp icon vào trong input -->
                        <div class="input-wrapper">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" class="form-control-custom" id="username" name="username" placeholder="Tài khoản / Email" required autofocus>
                        </div>

                        <div class="input-wrapper">
                            <i class="bi bi-shield-lock input-icon"></i>
                            <input type="password" class="form-control-custom" id="password" name="password" placeholder="Mật khẩu" required>
                        </div>

                        <div class="mt-4 mb-3">
                            <button type="submit" class="btn btn-orange-pill">
                                ĐĂNG NHẬP
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="#" class="text-decoration-none small" style="color: #64748b; font-size: 0.85rem;">Quên tài khoản / Mật khẩu?</a>
                        </div>
                        
                        <div class="text-center mt-5">
                            <a href="/" class="text-decoration-none small fw-semibold" style="color: #475569; font-size: 0.85rem;">
                                ← Quay lại trang chủ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>