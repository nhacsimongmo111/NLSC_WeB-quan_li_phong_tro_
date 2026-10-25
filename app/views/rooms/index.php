<?php
render_page_header('Quản lý danh sách phòng', 'rooms');

$totalCount = count($rooms);
$availableCount = 0;
$rentedCount = 0;
$maintenanceCount = 0;

foreach ($rooms as $r) {
    if ($r['status'] === 'available') $availableCount++;
    elseif ($r['status'] === 'rented') $rentedCount++;
    elseif ($r['status'] === 'maintenance') $maintenanceCount++;
}
?>

<!-- TIÊU ĐỀ & NÚT THÊM MỚI -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-door-open-fill text-orange me-2"></i>Quản lý danh sách phòng trọ
        </h4>
        <p class="text-muted small mb-0">Theo dõi thông tin chi tiết, trạng thái phòng và bật/tắt đăng tin lên website</p>
    </div>
    <div>
        <a href="/admin/rooms/create" class="btn btn-orange px-3 py-2 shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Thêm phòng mới</span>
        </a>
    </div>
</div>

<!-- THÔNG BÁO FLASH -->
<?php if (!empty($flash_success)): ?>
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
        <div><?= html_escape($flash_success) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($flash_error)): ?>
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
        <div><?= html_escape($flash_error) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- BỘ LỌC TÌM KIẾM TRÊN BẢNG -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="/admin/rooms" method="GET" class="row g-2 align-items-center">
            <!-- Tìm số phòng / từ khóa -->
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0" placeholder="Tìm số phòng, tên cơ sở..." value="<?= html_escape($keyword ?? '') ?>">
                </div>
            </div>

            <!-- Lọc theo cơ sở -->
            <div class="col-md-3">
                <select name="building_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả cơ sở (<?= count($buildings) ?>) --</option>
                    <?php foreach ($buildings as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $currentBuilding === (int)$b['id'] ? 'selected' : '' ?>>
                            <?= html_escape($b['name']) ?> (<?= html_escape($b['district']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lọc theo trạng thái -->
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="available"   <?= $currentStatus === 'available' ? 'selected' : '' ?>>🟢 Phòng trống</option>
                    <option value="rented"      <?= $currentStatus === 'rented' ? 'selected' : '' ?>>🔵 Đang thuê</option>
                    <option value="maintenance" <?= $currentStatus === 'maintenance' ? 'selected' : '' ?>>🟠 Đang bảo trì</option>
                </select>
            </div>

            <!-- Nút thao tác lọc -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark w-100 fw-medium">Lọc</button>
                <?php if (!empty($currentBuilding) || !empty($currentStatus) || !empty($keyword)): ?>
                    <a href="/admin/rooms" class="btn btn-sm btn-outline-secondary" title="Đặt lại bộ lọc"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- BẢNG DỮ LIỆU DATA TABLE -->
<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h6 class="fw-bold mb-0 text-dark">Danh sách phòng</h6>
            <span class="badge bg-light text-dark border"><?= $totalCount ?> phòng</span>
        </div>
        <div class="small text-muted d-none d-md-flex gap-3">
            <span><span class="badge bg-success me-1">Trống: <?= $availableCount ?></span></span>
            <span><span class="badge bg-primary me-1">Đang thuê: <?= $rentedCount ?></span></span>
            <span><span class="badge bg-warning text-dark me-1">Bảo trì: <?= $maintenanceCount ?></span></span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4" style="width: 170px;">Phòng & Cơ sở</th>
                    <th style="width: 150px;">Loại & Diện tích</th>
                    <th style="width: 140px;">Giá thuê / tháng</th>
                    <th style="width: 150px;">Trạng thái</th>
                    <th class="text-center" style="width: 120px;">Đăng web</th>
                    <th>Khách đang ở</th>
                    <th class="text-end pe-4" style="width: 110px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <div class="fw-semibold">Không tìm thấy phòng nào phù hợp</div>
                            <small>Hãy thử thay đổi điều kiện tìm kiếm hoặc bấm Thêm phòng mới.</small>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <!-- Cột 1: Số phòng & Cơ sở -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-3 bg-light border p-2 text-dark fw-bold text-center" style="min-width: 44px;">
                                        <?= html_escape($room['room_number']) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 160px;">
                                            <?= html_escape($room['building_name']) ?>
                                        </div>
                                        <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= html_escape($room['district']) ?></small>
                                    </div>
                                </div>
                            </td>

                            <!-- Cột 2: Loại & Diện tích -->
                            <td>
                                <div class="small fw-semibold text-dark"><?= html_escape($room['type_name'] ?? 'Tiêu chuẩn') ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-aspect-ratio me-1"></i><?= $room['area_m2'] ?> m² • Tối đa <?= $room['max_occupants'] ?> người
                                </small>
                            </td>

                            <!-- Cột 3: Giá thuê -->
                            <td>
                                <div class="fw-bold text-danger"><?= format_currency($room['rent_price']) ?></div>
                                <small class="text-muted">Cọc: <?= $room['deposit_months'] ?> tháng</small>
                            </td>

                            <!-- Cột 4: Dropdown đổi trạng thái nhanh -->
                            <td>
                                <select class="form-select form-select-sm status-select status-select-<?= $room['status'] ?>" 
                                        onchange="changeRoomStatus(<?= $room['id'] ?>, this.value, this)">
                                    <option value="available"   <?= $room['status'] === 'available' ? 'selected' : '' ?>>🟢 Trống</option>
                                    <option value="rented"      <?= $room['status'] === 'rented' ? 'selected' : '' ?>>🔵 Đang thuê</option>
                                    <option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>🟠 Bảo trì</option>
                                </select>
                            </td>

                            <!-- Cột 5: Toggle Switch Đăng tin web -->
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input toggle-publish-switch" 
                                           type="checkbox" 
                                           role="switch" 
                                           data-room-id="<?= $room['id'] ?>"
                                           <?= !empty($room['is_published']) ? 'checked' : '' ?>
                                           onchange="togglePublished(<?= $room['id'] ?>, this)">
                                </div>
                                <div class="publish-status-label small text-muted" id="publish-label-<?= $room['id'] ?>">
                                    <?= !empty($room['is_published']) ? '<span class="text-success">Hiện</span>' : '<span class="text-secondary">Ẩn</span>' ?>
                                </div>
                            </td>

                            <!-- Cột 6: Khách đang ở -->
                            <td>
                                <?php if ($room['status'] === 'rented' && !empty($room['current_tenant_name'])): ?>
                                    <div class="small fw-semibold text-dark">
                                        <i class="bi bi-person-fill text-primary me-1"></i><?= html_escape($room['current_tenant_name']) ?>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="bi bi-telephone me-1"></i><?= html_escape($room['current_tenant_phone'] ?? 'Chưa có SĐT') ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border fw-normal">Trống</span>
                                <?php endif; ?>
                            </td>

                            <!-- Cột 7: Thao tác -->
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <!-- Nút Sửa -->
                                    <a href="/admin/rooms/<?= $room['id'] ?>/edit" class="btn btn-outline-secondary" title="Chỉnh sửa phòng">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Nút Xóa có modal xác nhận -->
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $room['id'] ?>, '<?= html_escape($room['room_number']) ?>', '<?= html_escape($room['building_name']) ?>', '<?= $room['status'] ?>')"
                                            title="Xóa phòng">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL XÁC NHẬN XÓA PHÒNG -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-danger text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận xóa phòng</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteRoomForm" action="" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="mb-2">Bạn có chắc chắn muốn xóa vĩnh viễn phòng này không?</p>
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-bold fs-5 text-dark" id="deleteRoomTitle">Phòng P.101</div>
                        <div class="small text-muted" id="deleteRoomBuilding">Nhà trọ Ninh Kiều 1</div>
                    </div>
                    <div id="deleteWarningText" class="alert alert-warning small d-none">
                        <i class="bi bi-info-circle me-1"></i>Phòng này đang ở trạng thái <strong>Đang thuê</strong>. Nếu có hợp đồng có hiệu lực, hệ thống sẽ tự động chặn xóa để bảo vệ dữ liệu.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-semibold">Đồng ý xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JAVASCRIPT XỬ LÝ AJAX BẬT TẮT ĐĂNG TIN VÀ ĐỔI TRẠNG THÁI -->
<script>
// 1. Bật/tắt công tắc đăng tin web qua AJAX
async function togglePublished(roomId, switchEl) {
    try {
        const res = await fetch(`/admin/rooms/${roomId}/toggle-published`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            const labelEl = document.getElementById('publish-label-' + roomId) || switchEl.parentElement.nextElementSibling;
            if (data.is_published) {
                labelEl.innerHTML = '<span class="text-success">Hiện</span>';
            } else {
                labelEl.innerHTML = '<span class="text-secondary">Ẩn</span>';
            }
        }
    } catch (err) {
        alert('Có lỗi xảy ra khi đổi trạng thái hiển thị.');
        switchEl.checked = !switchEl.checked;
    }
}

// 2. Đổi nhanh trạng thái phòng (Trống / Đang thuê / Bảo trì)
async function changeRoomStatus(roomId, newStatus, selectEl) {
    const formData = new FormData();
    formData.append('status', newStatus);

    try {
        const res = await fetch(`/admin/rooms/${roomId}/change-status`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            // Thay đổi màu border/class
            selectEl.className = `form-select form-select-sm status-select status-select-${newStatus}`;
        }
    } catch (err) {
        alert('Không thể cập nhật trạng thái phòng.');
    }
}

// 3. Modal xác nhận xóa
function confirmDelete(roomId, roomNumber, buildingName, status) {
    const form = document.getElementById('deleteRoomForm');
    form.action = `/admin/rooms/${roomId}/delete`;

    document.getElementById('deleteRoomTitle').innerText = `Phòng ${roomNumber}`;
    document.getElementById('deleteRoomBuilding').innerText = buildingName;

    const warnEl = document.getElementById('deleteWarningText');
    if (status === 'rented') {
        warnEl.classList.remove('d-none');
    } else {
        warnEl.classList.add('d-none');
    }

    const modal = new bootstrap.Modal(document.getElementById('deleteRoomModal'));
    modal.show();
}
</script>

<?php render_page_footer(); ?>
