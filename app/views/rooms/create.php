<?php
render_page_header('Thêm phòng mới', 'rooms');
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="/admin/rooms" class="text-decoration-none">Quản lý phòng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Thêm phòng mới</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-plus-circle-fill text-orange me-2"></i>Thêm phòng trọ mới</h4>
    </div>
    <div>
        <a href="/admin/rooms" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>
</div>

<?php if (!empty($flash_error)): ?>
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
        <div><?= html_escape($flash_error) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="/admin/rooms" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- CỘT TRÁI: THÔNG TIN PHÒNG, GIÁ & TIỆN ÍCH -->
        <div class="col-lg-8">
            <!-- Thẻ 1: Cơ sở & Vị trí -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2 text-primary"></i>Cơ sở & Loại phòng</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Chọn cơ sở / Dãy trọ <span class="text-danger">*</span></label>
                            <select name="building_id" class="form-select" required>
                                <option value="">-- Chọn cơ sở --</option>
                                <?php foreach ($buildings as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= (($oldInput['building_id'] ?? '') == $b['id']) ? 'selected' : '' ?>>
                                        <?= html_escape($b['name']) ?> (<?= html_escape($b['district']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Số phòng <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" placeholder="VD: P.101" 
                                   value="<?= html_escape($oldInput['room_number'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Loại phòng</label>
                            <select name="room_type_id" class="form-select">
                                <option value="">-- Loại phòng --</option>
                                <?php foreach ($roomTypes as $rt): ?>
                                    <option value="<?= $rt['id'] ?>" <?= (($oldInput['room_type_id'] ?? '') == $rt['id']) ? 'selected' : '' ?>>
                                        <?= html_escape($rt['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thẻ 2: Giá thuê, Cọc & Diện tích -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack me-2 text-success"></i>Giá thuê & Thông số phòng</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Giá thuê (đ/tháng) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="rent_price" class="form-control" placeholder="1500000" step="10000" min="0" 
                                       value="<?= html_escape($oldInput['rent_price'] ?? '') ?>" required>
                                <span class="input-group-text bg-light">đ</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Diện tích (m²) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="area_m2" class="form-control" placeholder="20" step="0.5" min="1" 
                                       value="<?= html_escape($oldInput['area_m2'] ?? '') ?>" required>
                                <span class="input-group-text bg-light">m²</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tiền cọc (Số tháng)</label>
                            <input type="number" name="deposit_months" class="form-control" value="<?= html_escape($oldInput['deposit_months'] ?? '1') ?>" min="0" max="6">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Sức chứa tối đa (người)</label>
                            <input type="number" name="max_occupants" class="form-control" value="<?= html_escape($oldInput['max_occupants'] ?? '2') ?>" min="1" max="10">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Đơn giá điện riêng (đ/kWh)</label>
                            <input type="number" name="electricity_price" class="form-control" value="<?= html_escape($oldInput['electricity_price'] ?? '3500') ?>" step="100">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Đơn giá nước riêng (đ/m³)</label>
                            <input type="number" name="water_price" class="form-control" value="<?= html_escape($oldInput['water_price'] ?? '15000') ?>" step="500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thẻ 3: Tiện ích phòng -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-circle me-2 text-info"></i>Tiện ích trang bị</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php 
                        $selectedAmenities = $oldInput['amenities'] ?? ['wifi', 'wc_rieng', 'cho_de_xe'];
                        foreach ($amenityLabels as $key => $info): 
                        ?>
                            <div class="col-sm-6 col-md-3">
                                <label class="amenity-checkbox-label w-100 justify-content-start p-2">
                                    <input type="checkbox" name="amenities[]" value="<?= $key ?>" 
                                           <?= in_array($key, $selectedAmenities) ? 'checked' : '' ?>>
                                    <i class="bi <?= $info['icon'] ?> text-secondary"></i>
                                    <span><?= $info['label'] ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: TRẠNG THÁI, HIỂN THỊ & MÔ TẢ -->
        <div class="col-lg-4">
            <!-- Thẻ trạng thái & xuất bản -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-toggle-on me-2 text-success"></i>Trạng thái & Đăng tin</h6>
                </div>
                <div class="card-body p-4">
                    <!-- Trạng thái phòng -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Trạng thái phòng</label>
                        <select name="status" class="form-select">
                            <option value="available"   <?= (($oldInput['status'] ?? 'available') === 'available') ? 'selected' : '' ?>>🟢 Phòng trống</option>
                            <option value="rented"      <?= (($oldInput['status'] ?? '') === 'rented') ? 'selected' : '' ?>>🔵 Đang thuê</option>
                            <option value="maintenance" <?= (($oldInput['status'] ?? '') === 'maintenance') ? 'selected' : '' ?>>🟠 Đang bảo trì</option>
                        </select>
                    </div>

                    <!-- Ngày trống sớm nhất -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ngày trống sớm nhất</label>
                        <input type="date" name="available_from" class="form-control" value="<?= html_escape($oldInput['available_from'] ?? '') ?>">
                        <small class="text-muted" style="font-size: 11px;">Để trống nếu phòng đã dọn dẹp sẵn sàng vào ở ngay.</small>
                    </div>

                    <!-- Switch Đăng lên web công khai -->
                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" 
                                   <?= isset($oldInput['is_published']) || empty($oldInput) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold small text-dark" for="is_published">
                                Đăng lên trang tìm phòng
                            </label>
                        </div>
                        <small class="text-muted d-block" style="font-size: 11.5px;">
                            Bật công tắc này để phòng hiển thị cho khách xem trên trang công khai (nếu trạng thái là Trống).
                        </small>
                    </div>
                </div>
            </div>

            <!-- Thẻ mô tả -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square me-2 text-secondary"></i>Mô tả chi tiết</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="description" class="form-control" rows="5" placeholder="Ghi chú thêm về phòng (giờ giấc, cổng riêng, view ban công, đồ dùng có sẵn...)..."><?= html_escape($oldInput['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Nút hành động Lưu -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-grid gap-2">
                    <button type="submit" class="btn btn-orange py-2 fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i> Lưu phòng mới
                    </button>
                    <a href="/admin/rooms" class="btn btn-outline-secondary py-2">
                        Hủy bỏ
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php render_page_footer(); ?>
