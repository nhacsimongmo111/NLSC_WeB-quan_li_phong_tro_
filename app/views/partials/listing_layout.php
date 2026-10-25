<?php
// Partial: Navbar trang công khai (Listing)
// Dùng riêng biệt với layout admin (sidebar)
if (!function_exists('render_listing_header')) {
    function render_listing_header(string $title = 'Tìm phòng trọ Cần Thơ'): void
    {
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/listing.css">
    <title><?= html_escape($title) ?> | Nhà Trọ Cần Thơ</title>
</head>
<body class="listing-body">

<!-- HEADER / NAVBAR CÔNG KHAI -->
<header class="listing-header shadow-sm sticky-top">
    <div class="container-xl">
        <div class="d-flex align-items-center justify-content-between py-2 gap-3">
            <!-- Logo -->
            <a href="/" class="listing-logo text-decoration-none d-flex align-items-center gap-2">
                <div class="logo-icon">
                    <i class="bi bi-houses-fill"></i>
                </div>
                <div class="d-none d-sm-block">
                    <span class="logo-text">NhàTrọ</span><span class="logo-ct">CầnThơ</span>
                </div>
            </a>

            <!-- Thanh tìm kiếm nhanh -->
            <form action="/phong-tro" method="GET" class="flex-grow-1 d-none d-md-block" style="max-width: 420px;">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control rounded-start-pill border-0 shadow-none bg-light"
                        placeholder="Tìm theo tên đường, phường, quận..."
                        value="<?= html_escape($_GET['keyword'] ?? '') ?>">
                    <button class="btn btn-danger rounded-end-pill px-3" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <!-- Actions -->
            <div class="d-flex align-items-center gap-2">
                <a href="/phong-tro" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-none d-lg-inline-flex align-items-center gap-1">
                    <i class="bi bi-list-ul"></i> Danh sách phòng
                </a>
                <?php if (is_logged_in()): ?>
                    <a href="/yeu-thich" class="btn btn-light btn-sm rounded-pill border">
                        <i class="bi bi-heart-fill text-danger"></i>
                        <span class="d-none d-sm-inline ms-1">Yêu thích</span>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-pill border d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                            <?php $u = current_user(); ?>
                            <?php if (!empty($u['avatar_url'])): ?>
                                <img src="<?= html_escape($u['avatar_url']) ?>"
                                     class="rounded-circle" width="24" height="24"
                                     style="object-fit:cover;" alt="">
                            <?php else: ?>
                                <i class="bi bi-person-circle fs-5 text-secondary"></i>
                            <?php endif; ?>
                            <span class="d-none d-sm-inline small fw-semibold"><?= html_escape($u['full_name'] ?? 'Tài khoản') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <?php if (is_admin()): ?>
                                <li>
                                    <a class="dropdown-item small" href="/admin">
                                        <i class="bi bi-speedometer2 me-1 text-primary"></i> Quản trị hệ thống
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <form action="/logout" method="POST" class="m-0">
                                    <?= csrf_field() ?>
                                    <button class="dropdown-item text-danger small">
                                        <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-person-circle me-1"></i> Đăng nhập
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<?php
    }
}

if (!function_exists('render_listing_footer')) {
    function render_listing_footer(): void
    {
?>
<footer class="listing-footer mt-5 py-4 border-top bg-white">
    <div class="container-xl">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="logo-icon"><i class="bi bi-houses-fill"></i></div>
                    <span class="fw-bold fs-5">NhàTrọ<span class="text-primary">CầnThơ</span></span>
                </div>
                <p class="text-muted small mb-0">Hệ thống tìm kiếm phòng trọ cho thuê tại TP. Cần Thơ. Thông tin cập nhật trực tiếp từ chủ trọ.</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Khu vực cho thuê</h6>
                <ul class="list-unstyled small text-muted mb-0">
                    <li><a href="/phong-tro?district=Quận+Ninh+Kiều" class="text-muted text-decoration-none hover-primary">Phòng trọ Quận Ninh Kiều</a></li>
                    <li><a href="/phong-tro?district=Quận+Bình+Thủy" class="text-muted text-decoration-none hover-primary">Phòng trọ Quận Bình Thủy</a></li>
                    <li><a href="/phong-tro?district=Quận+Cái+Răng"  class="text-muted text-decoration-none hover-primary">Phòng trọ Quận Cái Răng</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Liên hệ & Hỗ trợ</h6>
                <ul class="list-unstyled small text-muted mb-0">
                    <li><i class="bi bi-geo-alt-fill text-primary me-1"></i> TP. Cần Thơ, Việt Nam</li>
                    <li class="mt-1"><i class="bi bi-mortarboard-fill text-primary me-1"></i> Niên luận cơ sở - ĐH Cần Thơ</li>
                </ul>
            </div>
        </div>
        <hr class="my-3">
        <p class="text-center text-muted small mb-0">&copy; <?= date('Y') ?> NhàTrọCầnThơ. Khoa CNTT&TT - Trường Đại học Cần Thơ.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    }
}
