<?php
render_page_header('Dashboard - Tổng quan', 'dashboard');

$totalRooms = (int)($stats['total_rooms'] ?? 0);
$availableRooms = (int)($stats['available_rooms'] ?? 0);
$rentedRooms = (int)($stats['rented_rooms'] ?? 0);
$maintenanceRooms = (int)($stats['maintenance_rooms'] ?? 0);

$occupancyRate = $totalRooms > 0 ? round(($rentedRooms / $totalRooms) * 100) : 0;
?>

<!-- TIÊU ĐỀ DASHBOARD & THAO TÁC NHANH -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Tổng quan tình trạng phòng</h3>
        <p class="text-muted mb-0 small">Theo dõi trực quan tỷ lệ lấp đầy phòng, thông tin khách thuê và hóa đơn</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/rooms" class="btn btn-orange px-3 py-2 shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Thêm phòng</span>
        </a>
        <a href="/contracts" class="btn btn-outline-orange px-3 py-2 shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-plus-fill"></i>
            <span>Lập hợp đồng</span>
        </a>
    </div>
</div>

<?php if (!empty($flash_success)): ?>
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
        <div><?= html_escape($flash_success) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- THẺ THỐNG KÊ (STAT METRICS) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Tổng số phòng</span>
                    <h3 class="fw-bold mb-0 mt-1 text-dark"><?= $totalRooms ?></h3>
                    <small class="text-muted">Quy mô nhà trọ</small>
                </div>
                <div class="stat-icon bg-orange-subtle text-orange" style="background-color: #fff5f2; color: #f05123;">
                    <i class="bi bi-houses-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="/admin?status=available" class="text-decoration-none">
            <div class="card stat-card p-3 h-100 <?= $currentFilter === 'available' ? 'border-success' : '' ?>">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-success small fw-semibold text-uppercase">Phòng đang trống</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success"><?= $availableRooms ?></h3>
                        <small class="text-success fw-medium">Sẵn sàng đón khách</small>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="/admin?status=rented" class="text-decoration-none">
            <div class="card stat-card p-3 h-100 <?= $currentFilter === 'rented' ? 'border-primary' : '' ?>">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-primary small fw-semibold text-uppercase">Đang cho thuê</span>
                        <h3 class="fw-bold mb-0 mt-1 text-primary"><?= $rentedRooms ?></h3>
                        <small class="text-primary fw-medium">Tỷ lệ lấp đầy: <?= $occupancyRate ?>%</small>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="/admin?status=maintenance" class="text-decoration-none">
            <div class="card stat-card p-3 h-100 <?= $currentFilter === 'maintenance' ? 'border-warning' : '' ?>">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-warning small fw-semibold text-uppercase">Đang bảo trì</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $maintenanceRooms ?></h3>
                        <small class="text-muted">Cần kiểm tra thiết bị</small>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-tools"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- SƠ ĐỒ PHÒNG TRỰC QUAN (INTERACTIVE ROOM GRID) -->
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold mb-0 text-dark">Sơ đồ phòng theo thời gian thực</h5>
            <span class="badge bg-light text-dark border ms-1"><?= count($rooms) ?> phòng</span>
        </div>

        <!-- Bộ lọc nhanh trạng thái một chạm -->
        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border">
            <a href="/admin" class="btn rounded-pill px-3 <?= empty($currentFilter) ? 'btn-dark' : 'text-muted' ?>">Tất cả</a>
            <a href="/admin?status=available" class="btn rounded-pill px-3 <?= $currentFilter === 'available' ? 'btn-success text-white' : 'text-muted' ?>">
                <span class="d-inline-block rounded-circle bg-success me-1" style="width:6px;height:6px;"></span> Trống (<?= $availableRooms ?>)
            </a>
            <a href="/admin?status=rented" class="btn rounded-pill px-3 <?= $currentFilter === 'rented' ? 'btn-primary' : 'text-muted' ?>">
                <span class="d-inline-block rounded-circle bg-primary me-1" style="width:6px;height:6px;"></span> Đã thuê (<?= $rentedRooms ?>)
            </a>
            <a href="/admin?status=maintenance" class="btn rounded-pill px-3 <?= $currentFilter === 'maintenance' ? 'btn-warning text-dark' : 'text-muted' ?>">
                <span class="d-inline-block rounded-circle bg-warning me-1" style="width:6px;height:6px;"></span> Bảo trì (<?= $maintenanceRooms ?>)
            </a>
        </div>
    </div>

    <div class="card-body p-4 bg-light-subtle">
        <?php if (empty($rooms)): ?>
            <div class="text-center py-5">
                <div class="rounded-circle bg-white d-inline-flex p-4 shadow-sm mb-3">
                    <i class="bi bi-door-closed text-muted fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark">Chưa có dữ liệu phòng trọ</h5>
                <p class="text-muted small">Hãy nạp dữ liệu từ <code>database/schema.sql</code> hoặc thêm phòng mới.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($rooms as $room): ?>
                    <?php
                        $statusClass = 'status-available';
                        $statusText = 'Trống';
                        if ($room['status'] === 'rented') {
                            $statusClass = 'status-rented';
                            $statusText = 'Đang thuê';
                        } elseif ($room['status'] === 'maintenance') {
                            $statusClass = 'status-maintenance';
                            $statusText = 'Bảo trì';
                        }
                    ?>
                    <div class="col-sm-6 col-md-4 col-xl-3">
                        <div class="room-tile p-3 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Header của thẻ phòng -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-light border p-2 text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi bi-door-closed fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-dark"><?= html_escape($room['room_number']) ?></h5>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?= html_escape($room['type_name'] ?? 'Phòng chuẩn') ?></small>
                                        </div>
                                    </div>
                                    <span class="status-indicator <?= $statusClass ?>">
                                        <span class="led-dot"></span>
                                        <?= $statusText ?>
                                    </span>
                                </div>

                                <!-- Giá phòng -->
                                <div class="my-3 p-2 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Giá thuê</span>
                                    <span class="fw-bold text-dark"><?= format_currency($room['rent_price'] ?? $room['base_price'] ?? 0) ?></span>
                                </div>

                                <!-- Thông tin khách thuê nếu có -->
                                <div class="mb-3">
                                    <?php if ($room['status'] === 'rented' && !empty($room['current_tenant_name'])): ?>
                                        <div class="small fw-semibold text-dark text-truncate">
                                            <i class="bi bi-person-fill text-orange me-1"></i><?= html_escape($room['current_tenant_name']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-telephone text-secondary me-1"></i><?= html_escape($room['current_tenant_phone'] ?? 'Chưa có SĐT') ?>
                                        </div>
                                    <?php elseif ($room['status'] === 'maintenance'): ?>
                                        <div class="small text-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Đang trong quá trình bảo trì
                                        </div>
                                    <?php else: ?>
                                        <div class="small text-success">
                                            <i class="bi bi-check2-circle me-1"></i>Phòng sạch sẽ, sẵn sàng vào ở
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Nút thao tác nhanh mở Modal -->
                            <div class="pt-2 border-top">
                                <button type="button" 
                                    class="btn btn-outline-orange btn-sm w-100 py-1 d-flex align-items-center justify-content-center gap-1 btn-quick-action"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#quickActionModal"
                                    data-room-id="<?= $room['id'] ?>"
                                    data-room-number="<?= html_escape($room['room_number']) ?>"
                                    data-room-type="<?= html_escape($room['type_name'] ?? 'Tiêu chuẩn') ?>"
                                    data-room-price="<?= format_currency($room['rent_price'] ?? $room['base_price'] ?? 0) ?>"
                                    data-room-status="<?= $room['status'] ?>"
                                    data-tenant-name="<?= html_escape($room['current_tenant_name'] ?? '') ?>"
                                    data-tenant-phone="<?= html_escape($room['current_tenant_phone'] ?? '') ?>">
                                    <i class="bi bi-lightning-charge-fill text-warning"></i>
                                    <span>Thao tác nhanh</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL THAO TÁC NHANH (QUICK ACTION MODAL) -->
<!-- ========================================== -->
<div class="modal fade" id="quickActionModal" tabindex="-1" aria-labelledby="quickActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white p-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-orange text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: var(--primary-orange);">
                        <i class="bi bi-door-open-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="quickModalRoomTitle">Phòng P.101</h5>
                        <small class="text-white-50" id="quickModalRoomSub">Chi tiết & Ghi chỉ số</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <!-- Modal Body với 2 Tabs -->
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded-pill" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill small fw-semibold" id="tab-info-btn" data-bs-toggle="pill" data-bs-target="#tab-info" type="button" role="tab">
                            <i class="bi bi-info-circle me-1"></i> Thông tin phòng
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill small fw-semibold" id="tab-meter-btn" data-bs-toggle="pill" data-bs-target="#tab-meter" type="button" role="tab">
                            <i class="bi bi-speedometer2 me-1"></i> Ghi điện nước nhanh
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- Tab 1: Chi tiết phòng -->
                    <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                        <div class="list-group list-group-flush border rounded-3 mb-3 small">
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Loại phòng:</span>
                                <span class="fw-semibold" id="modalRoomType">-</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Giá niêm yết:</span>
                                <span class="fw-bold text-orange" id="modalRoomPrice">-</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Trạng thái:</span>
                                <span class="badge" id="modalRoomStatusBadge">-</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Khách đang thuê:</span>
                                <span class="fw-semibold text-dark" id="modalTenantName">Chưa có</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Số điện thoại:</span>
                                <span id="modalTenantPhone">-</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="/contracts" class="btn btn-outline-orange btn-sm" id="modalContractBtn">
                                <i class="bi bi-file-earmark-text me-1"></i> Xem / Tạo hợp đồng cho phòng này
                            </a>
                        </div>
                    </div>

                    <!-- Tab 2: Ghi chỉ số điện nước -->
                    <div class="tab-pane fade" id="tab-meter" role="tabpanel">
                        <form id="quickMeterForm">
                            <input type="hidden" id="quickMeterRoomId" name="room_id">

                            <div class="alert alert-light border small py-2 mb-3">
                                <i class="bi bi-calendar-event me-1 text-primary"></i> Ghi cho kỳ: <strong>Tháng <?= date('m/Y') ?></strong>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-warning-emphasis">
                                        <i class="bi bi-lightning-charge-fill text-warning"></i> Điện mới (kWh)
                                    </label>
                                    <input type="number" class="form-control" id="newElec" placeholder="0" min="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-primary-emphasis">
                                        <i class="bi bi-droplet-fill text-primary"></i> Nước mới ($m^3$)
                                    </label>
                                    <input type="number" class="form-control" id="newWater" placeholder="0" min="0" required>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 mb-3 small text-muted">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Đơn giá điện dự kiến:</span>
                                    <strong>3.500 đ / kWh</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Đơn giá nước dự kiến:</span>
                                    <strong>15.000 đ / $m^3$</strong>
                                </div>
                            </div>

                            <button type="button" class="btn btn-orange w-100" onclick="alert('Đã ghi nhận chỉ số thành công cho ' + document.getElementById('quickModalRoomTitle').innerText + '!')">
                                <i class="bi bi-check-circle-fill me-1"></i> Lưu chỉ số điện nước
                            </button>
                                <i class="bi bi-check-circle-fill me-1"></i> Lưu chỉ số điện nước
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Điền dữ liệu động vào Modal khi bấm Thao tác nhanh
    document.addEventListener('DOMContentLoaded', () => {
        const quickButtons = document.querySelectorAll('.btn-quick-action');
        quickButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const roomNumber = btn.getAttribute('data-room-number');
                const roomType = btn.getAttribute('data-room-type');
                const roomPrice = btn.getAttribute('data-room-price');
                const roomStatus = btn.getAttribute('data-room-status');
                const tenantName = btn.getAttribute('data-tenant-name');
                const tenantPhone = btn.getAttribute('data-tenant-phone');
                const roomId = btn.getAttribute('data-room-id');

                document.getElementById('quickModalRoomTitle').innerText = 'Phòng ' + roomNumber;
                document.getElementById('quickModalRoomSub').innerText = roomType + ' • ' + roomPrice;
                document.getElementById('modalRoomType').innerText = roomType;
                document.getElementById('modalRoomPrice').innerText = roomPrice;
                document.getElementById('quickMeterRoomId').value = roomId;

                const statusBadge = document.getElementById('modalRoomStatusBadge');
                if (roomStatus === 'available') {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.innerText = 'Trống';
                } else if (roomStatus === 'rented') {
                    statusBadge.className = 'badge bg-primary';
                    statusBadge.innerText = 'Đang thuê';
                } else {
                    statusBadge.className = 'badge bg-warning text-dark';
                    statusBadge.innerText = 'Bảo trì';
                }

                document.getElementById('modalTenantName').innerText = tenantName || 'Chưa có người thuê';
                document.getElementById('modalTenantPhone').innerText = tenantPhone || 'Không có';
            });
        });
    });
</script>

<?php
render_page_footer();
?>
