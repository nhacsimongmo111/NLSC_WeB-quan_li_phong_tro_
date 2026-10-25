<?php

if (!function_exists('render_page_header')) {
    function render_page_header(string $title = 'Hệ Thống Quản Lý Nhà Trọ', string $activeMenu = 'dashboard'): void
    {
        $user = current_user();
?>
        <!doctype html>
        <html lang="vi">

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <link rel="stylesheet" href="/css/style.css">
            <title><?= html_escape($title) ?> | Quản Lý Nhà Trọ</title>
        </head>

        <body>
            <div class="app-wrapper">
                <!-- SIDEBAR BÊN TRÁI-->
                <aside class="app-sidebar" id="appSidebar">
                    <div class="sidebar-brand">
                        <div class="rounded-3 bg-orange text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; background-color: var(--primary-orange);">
                            <i class="bi bi-houses-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="lh-1">Trọ Manager</span>
                            <span class="text-white-50" style="font-size: 0.72rem; font-weight: normal;">Niên luận cơ sở</span>
                        </div>
                    </div>

                    <div class="p-3">
                        <small class="text-uppercase text-white-50 px-2 fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Menu chính</small>
                    </div>

                    <nav class="nav flex-column flex-grow-1">
                        <a class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="/admin">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>Dashboard</span>
                        </a>
                        <a class="nav-link" href="/phong-tro" target="_blank">
                            <i class="bi bi-globe2 text-warning"></i>
                            <span>Xem trang listing</span>
                        </a>
                        <a class="nav-link <?= $activeMenu === 'rooms' ? 'active' : '' ?>" href="/admin/rooms">
                            <i class="bi bi-door-open-fill"></i>
                            <span>Danh sách phòng</span>
                        </a>
                        <a class="nav-link <?= $activeMenu === 'contracts' ? 'active' : '' ?>" href="/contracts">
                            <i class="bi bi-file-earmark-person-fill"></i>
                            <span>Khách & Hợp đồng</span>
                        </a>
                        <a class="nav-link <?= $activeMenu === 'invoices' ? 'active' : '' ?>" href="/invoices">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span>Điện nước & Hóa đơn</span>
                        </a>
                        <a class="nav-link <?= $activeMenu === 'reports' ? 'active' : '' ?>" href="/reports">
                            <i class="bi bi-bar-chart-fill"></i>
                            <span>Thống kê doanh thu</span>
                        </a>
                    </nav>

                    <!-- User Card ở cuối sidebar -->
                    <div class="p-3 border-top border-secondary border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 36px; height: 36px;">
                                    <?= strtoupper(substr($user['full_name'] ?? ($user['username'] ?? 'U'), 0, 1)) ?>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="text-white small fw-semibold text-truncate"><?= html_escape($user['full_name'] ?? ($user['username'] ?? 'Quản trị viên')) ?></div>
                                    <div class="text-white-50" style="font-size: 0.75rem;"><?= html_escape($user['role'] ?? 'Chủ trọ') ?></div>
                                </div>
                            </div>
                            <form action="/logout" method="POST" class="m-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link text-white-50 p-1 hover-white" title="Đăng xuất">
                                    <i class="bi bi-box-arrow-right fs-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>

                <!-- Lớp mờ cho Mobile khi mở Sidebar -->
                <div class="sidebar-backdrop d-none" id="sidebarBackdrop"></div>

                <!-- PHẦN NỘI DUNG CHÍNH (MAIN CONTENT) -->
                <div class="app-main">
                    <!-- Topbar header -->
                    <header class="app-topbar">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-light d-lg-none p-2 rounded-3 border" type="button" id="sidebarToggle">
                                <i class="bi bi-list fs-5"></i>
                            </button>
                            <nav aria-label="breadcrumb" class="d-none d-sm-block">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item text-muted">Hệ thống</li>
                                    <li class="breadcrumb-item active fw-medium text-dark" aria-current="page"><?= html_escape($title) ?></li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-light text-dark border px-3 py-2 fw-medium d-none d-md-inline-block">
                                <i class="bi bi-calendar3 me-1 text-primary"></i> <?= date('d/m/Y') ?>
                            </span>

                            <div class="dropdown">
                                <button class="btn btn-light rounded-pill border py-1 px-3 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-shield-check text-success"></i>
                                    <span class="small fw-semibold"><?= html_escape($user['username'] ?? 'admin') ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                    <li>
                                        <h6 class="dropdown-header">Tài khoản quản lý</h6>
                                    </li>
                                    <li><span class="dropdown-item-text text-muted small">Họ tên: <?= html_escape($user['full_name'] ?? 'Admin') ?></span></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="/logout" method="POST" class="m-0">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger small">
                                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </header>

                    <main class="p-4 flex-grow-1">
                    <?php
                }
            }

            if (!function_exists('render_page_footer')) {
                function render_page_footer(): void
                {
                    ?>
                    </main>

                    <footer class="bg-white border-top py-3 px-4 text-muted small d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <strong>Niên luận cơ sở</strong> &copy; <?= date('Y') ?> - Hệ thống quản lý nhà trọ (PostgreSQL)
                        </div>
                        <div>
                            Khoa CNTT&TT - Trường Đại học Cần Thơ
                        </div>
                    </footer>
                </div>
            </div>

            <!-- Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                // Xử lý đóng mở sidebar trên thiết bị di động
                const sidebarToggle = document.getElementById('sidebarToggle');
                const appSidebar = document.getElementById('appSidebar');
                const sidebarBackdrop = document.getElementById('sidebarBackdrop');

                if (sidebarToggle && appSidebar && sidebarBackdrop) {
                    sidebarToggle.addEventListener('click', () => {
                        appSidebar.classList.toggle('show');
                        sidebarBackdrop.classList.toggle('d-none');
                    });

                    sidebarBackdrop.addEventListener('click', () => {
                        appSidebar.classList.remove('show');
                        sidebarBackdrop.classList.add('d-none');
                    });
                }
            </script>
        </body>

        </html>
<?php
                }
            }
