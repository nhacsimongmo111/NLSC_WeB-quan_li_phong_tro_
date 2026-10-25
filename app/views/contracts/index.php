<?php render_page_header('Quản lý Hợp đồng & Khách thuê', 'contracts'); ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-file-earmark-person-fill text-orange me-2"></i>Danh sách Hợp đồng
        </h4>
        <p class="text-muted small mb-0">Quản lý thông tin khách thuê và hợp đồng đang hiệu lực.</p>
    </div>
    <div>
        <!-- Nút gọi Modal thay vì chuyển trang -->
        <button class="btn btn-orange px-3 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createContractModal">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Tạo hợp đồng mới</span>
        </button>
    </div>
</div>

<!-- Flash Message nếu reload -->
<?php if ($msg = session_get_once('flash_success')): ?>
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= html_escape($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Bảng dữ liệu Hợp đồng -->
<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">Mã HĐ / Phòng</th>
                    <th>Khách thuê</th>
                    <th>Thời hạn</th>
                    <th>Tài chính</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contracts)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Chưa có hợp đồng nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= html_escape($c['room_number']) ?></div>
                                <small class="text-muted"><?= html_escape($c['contract_code']) ?></small><br>
                                <small class="text-muted"><?= html_escape($c['building_name']) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold"><i class="bi bi-person me-1 text-primary"></i><?= html_escape($c['tenant_name']) ?></div>
                                <small class="text-muted">SĐT: <?= html_escape($c['tenant_phone']) ?> | CCCD: <?= html_escape($c['citizen_id']) ?></small>
                            </td>
                            <td>
                                <div><small>Bắt đầu:</small> <?= format_date($c['start_date']) ?></div>
                                <div><small>Kết thúc:</small> <?= $c['end_date'] ? format_date($c['end_date']) : '<span class="text-muted">Không thời hạn</span>' ?></div>
                            </td>
                            <td>
                                <div class="text-danger fw-bold"><?= format_currency($c['rent_price']) ?>/tháng</div>
                                <small class="text-muted">Cọc: <?= format_currency($c['deposit_amount']) ?></small>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Đang hiệu lực</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">Đã kết thúc</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TẠO HỢP ĐỒNG (AJAX) -->
<div class="modal fade" id="createContractModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-orange text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus-fill me-2"></i>Lập Hợp Đồng Cho Thuê Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="ajaxContractForm">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <!-- Khu vực báo lỗi AJAX -->
                    <div id="ajaxErrorBox" class="alert alert-danger d-none small"></div>

                    <div class="row g-4">
                        <!-- Thông tin phòng -->
                        <div class="col-12">
                            <h6 class="text-orange fw-bold border-bottom pb-2 mb-3">1. Chọn Phòng Trống</h6>
                            <select name="room_id" id="roomSelect" class="form-select" required onchange="fillRoomData(this)">
                                <option value="">-- Chọn phòng đang trống --</option>
                                <?php foreach ($availableRooms as $r): ?>
                                    <option value="<?= $r['id'] ?>" 
                                            data-price="<?= $r['rent_price'] ?>" 
                                            data-deposit="<?= $r['rent_price'] * $r['deposit_months'] ?>">
                                        <?= html_escape($r['building_name']) ?> - Phòng <?= html_escape($r['room_number']) ?> (<?= format_currency($r['rent_price']) ?>/tháng)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Thông tin Khách thuê -->
                        <div class="col-md-6">
                            <h6 class="text-orange fw-bold border-bottom pb-2 mb-3">2. Thông Tin Khách Thuê</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" required placeholder="Nguyễn Văn A">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Số CCCD / CMND <span class="text-danger">*</span></label>
                                <input type="text" name="citizen_id" class="form-control" required placeholder="09220300xxxx">
                                <small class="text-muted">Hệ thống sẽ tự nhận diện nếu khách cũ.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required placeholder="0901234567">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Quê quán</label>
                                <input type="text" name="hometown" class="form-control" placeholder="Tỉnh/Thành phố">
                            </div>
                        </div>

                        <!-- Thông tin Tài chính Hợp đồng -->
                        <div class="col-md-6">
                            <h6 class="text-orange fw-bold border-bottom pb-2 mb-3">3. Thỏa Thuận Hợp Đồng</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Ngày kết thúc</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                                <input type="number" name="rent_price" id="rentPriceInput" class="form-control" required min="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tiền cọc (VNĐ)</label>
                                <input type="number" name="deposit_amount" id="depositInput" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-orange fw-semibold" id="btnSubmitContract">
                        Lưu Hợp Đồng & Kích Hoạt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tự động điền giá tiền và cọc khi chọn phòng
function fillRoomData(selectObj) {
    const selectedOption = selectObj.options[selectObj.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('rentPriceInput').value = selectedOption.getAttribute('data-price');
        document.getElementById('depositInput').value = selectedOption.getAttribute('data-deposit');
    } else {
        document.getElementById('rentPriceInput').value = '';
        document.getElementById('depositInput').value = '0';
    }
}

// Xử lý gửi Form bằng AJAX Fetch
document.getElementById('ajaxContractForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitContract');
    const errorBox = document.getElementById('ajaxErrorBox');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang lưu...';
    errorBox.classList.add('d-none');

    const formData = new FormData(this);

    try {
        const response = await fetch('/contracts/store-ajax', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            window.location.href = result.redirect; // Load lại trang để hiện Flash Message
        } else {
            errorBox.innerHTML = result.message;
            errorBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = 'Lưu Hợp Đồng & Kích Hoạt';
        }
    } catch (error) {
        errorBox.innerHTML = 'Lỗi mạng hoặc server không phản hồi.';
        errorBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Lưu Hợp Đồng & Kích Hoạt';
    }
});
</script>

<?php render_page_footer(); ?>