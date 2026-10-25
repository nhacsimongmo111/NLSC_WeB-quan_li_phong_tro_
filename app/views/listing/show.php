<?php
require_once ROOTDIR . 'app/views/partials/listing_layout.php';

$fullAddress = $room['address'] . ', ' . $room['ward'] . ', ' . $room['district'] . ', ' . $room['city'];
$amenityLabels = \App\Models\RoomListing::AMENITY_LABELS;

render_listing_header('Phòng ' . $room['room_number'] . ' - ' . $room['building_name']);
?>

<div class="container-xl py-3">
    <!-- Breadcrumb -->
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="/" class="text-muted text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="/phong-tro" class="text-muted text-decoration-none">Phòng trọ Cần Thơ</a></li>
            <li class="breadcrumb-item"><a href="/phong-tro?district=<?= urlencode($room['district']) ?>" class="text-muted text-decoration-none"><?= html_escape($room['district']) ?></a></li>
            <li class="breadcrumb-item active">Phòng <?= html_escape($room['room_number']) ?></li>
        </ol>
    </nav>

    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= html_escape($flash_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= html_escape($flash_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 align-items-start">

        <!-- ============================== -->
        <!-- CỘT TRÁI: NỘI DUNG CHI TIẾT   -->
        <!-- ============================== -->
        <div class="col-lg-8">

            <!-- Tiêu đề phòng -->
            <div class="detail-box mb-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <?= html_escape($room['building_name']) ?> — Phòng <?= html_escape($room['room_number']) ?>
                        </h4>
                        <div class="text-muted small">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= html_escape($fullAddress) ?>
                        </div>
                    </div>
                    <?php if (!empty($room['type_name'])): ?>
                        <span class="room-type-badge fs-6 px-3 py-1"><?= html_escape($room['type_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="detail-price mt-2">
                    <?= format_currency($room['rent_price']) ?>
                    <span class="text-muted fw-normal fs-6">/tháng</span>
                </div>
            </div>

            <!-- GALLERY PLACEHOLDER (để trống để thêm ảnh sau) -->
            <div class="mb-3">
                <div class="room-gallery-placeholder">
                    <i class="bi bi-images"></i>
                    <span class="fw-semibold">Khu vực hiển thị ảnh phòng</span>
                    <small class="text-muted mt-1">Chưa có ảnh — sẽ cập nhật sau</small>
                </div>
                <!-- Thumbnail dưới gallery -->
                <div class="d-flex gap-2 mt-2">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div style="width: 80px; height: 60px; background: #eeeeee; border-radius: 6px; border: 1px dashed #bdbdbd;
                                    display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- THÔNG TIN CHI TIẾT -->
            <div class="detail-box mb-3">
                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>Thông tin phòng
                </h6>

                <div class="detail-meta-item">
                    <span class="label text-muted"><i class="bi bi-aspect-ratio me-2"></i>Diện tích</span>
                    <span class="value"><?= $room['area_m2'] ?> m²</span>
                </div>
                <div class="detail-meta-item">
                    <span class="label text-muted"><i class="bi bi-people me-2"></i>Sức chứa</span>
                    <span class="value">Tối đa <?= $room['max_occupants'] ?> người</span>
                </div>
                <div class="detail-meta-item">
                    <span class="label text-muted"><i class="bi bi-shield-check me-2"></i>Tiền cọc</span>
                    <span class="value"><?= $room['deposit_months'] ?> tháng (<?= format_currency($room['rent_price'] * $room['deposit_months']) ?>)</span>
                </div>
                <div class="detail-meta-item">
                    <span class="label text-muted"><i class="bi bi-lightning-charge me-2"></i>Giá điện</span>
                    <span class="value"><?= number_format($room['electricity_price'], 0, ',', '.') ?> đ/kWh</span>
                </div>
                <div class="detail-meta-item">
                    <span class="label text-muted"><i class="bi bi-droplet me-2"></i>Giá nước</span>
                    <span class="value"><?= number_format($room['water_price'], 0, ',', '.') ?> đ/m³</span>
                </div>
                <?php if (!empty($room['available_from'])): ?>
                    <div class="detail-meta-item">
                        <span class="label text-muted"><i class="bi bi-calendar-check me-2"></i>Ngày trống</span>
                        <span class="value text-success fw-bold"><?= format_date($room['available_from']) ?></span>
                    </div>
                <?php else: ?>
                    <div class="detail-meta-item">
                        <span class="label text-muted"><i class="bi bi-calendar-check me-2"></i>Ngày trống</span>
                        <span class="value text-success fw-bold">Sẵn sàng vào ở ngay</span>
                    </div>
                <?php endif; ?>
                <div class="detail-meta-item">
                    <span class="label text-muted"><i class="bi bi-geo-alt me-2"></i>Địa chỉ</span>
                    <span class="value"><?= html_escape($fullAddress) ?></span>
                </div>
            </div>

            <!-- TIỆN ÍCH -->
            <?php if (!empty($amenities)): ?>
                <div class="detail-box mb-3">
                    <h6 class="fw-bold mb-3 pb-2 border-bottom">
                        <i class="bi bi-check2-circle text-success me-2"></i>Tiện ích đi kèm
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($amenities as $key):
                            $info = $amenityLabels[$key] ?? null;
                            if (!$info) continue;
                        ?>
                            <span class="amenity-tag fs-6 px-3 py-2">
                                <i class="bi <?= $info['icon'] ?>"></i>
                                <?= $info['label'] ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- MÔ TẢ THÊM -->
            <?php if (!empty($room['description'])): ?>
                <div class="detail-box mb-3">
                    <h6 class="fw-bold mb-3 pb-2 border-bottom">
                        <i class="bi bi-file-text me-2"></i>Mô tả chi tiết
                    </h6>
                    <p class="text-muted mb-0" style="white-space: pre-line; line-height: 1.7;"><?= html_escape($room['description']) ?></p>
                </div>
            <?php endif; ?>

            <!-- FORM YÊU CẦU XEM PHÒNG -->
            <div class="detail-box mb-3">
                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                    <i class="bi bi-envelope-fill text-danger me-2"></i>Gửi yêu cầu xem phòng
                </h6>
                <form action="/phong-tro/<?= $room['id'] ?>/lien-he" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6">
                            <input type="text" name="visitor_name" class="form-control"
                                   placeholder="Họ và tên của bạn *" required>
                        </div>
                        <div class="col-sm-6">
                            <input type="tel" name="visitor_phone" class="form-control"
                                   placeholder="Số điện thoại *" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea name="message" class="form-control" rows="3"
                                  placeholder="Lời nhắn (thời gian muốn xem phòng, câu hỏi...)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger px-4 fw-semibold">
                        <i class="bi bi-send-fill me-1"></i> Gửi yêu cầu
                    </button>
                </form>
            </div>

            <!-- PHÒNG LIÊN QUAN -->
            <?php if (!empty($related)): ?>
                <div class="mb-3">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-grid me-2 text-danger"></i>Phòng trống cùng khu vực
                    </h6>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($related as $r):
                            $rAmenities = \App\Models\RoomListing::parseAmenities($r['amenities']);
                        ?>
                            <a href="/phong-tro/<?= $r['id'] ?>" class="room-card text-decoration-none" style="min-height: 90px;">
                                <div class="room-card-img" style="width: 110px; min-width: 110px;">
                                    <i class="bi bi-image img-placeholder-icon" style="font-size: 1.5rem;"></i>
                                </div>
                                <div class="room-card-body">
                                    <div class="room-card-title"><?= html_escape($r['building_name']) ?> — Phòng <?= html_escape($r['room_number']) ?></div>
                                    <div class="room-card-price" style="font-size: 14px;"><?= format_currency($r['rent_price']) ?>/tháng</div>
                                    <div class="room-card-meta">
                                        <span><i class="bi bi-aspect-ratio"></i> <?= $r['area_m2'] ?> m²</span>
                                        <span><i class="bi bi-geo-alt"></i> <?= html_escape($r['ward']) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================== -->
        <!-- CỘT PHẢI: LIÊN HỆ CHỦ TRỌ     -->
        <!-- ============================== -->
        <div class="col-lg-4">
            <div class="contact-box">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <!-- Avatar placeholder chủ trọ -->
                    <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width: 48px; height: 48px; flex-shrink: 0; font-size: 1.3rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Chủ nhà trọ</div>
                        <div class="small text-success fw-semibold">
                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> Đang hoạt động
                        </div>
                    </div>
                </div>

                <div class="phone-number mb-3">
                    <?= html_escape($room['contact_phone'] ?? 'Chưa có SĐT') ?>
                </div>

                <a href="tel:<?= html_escape($room['contact_phone'] ?? '') ?>" class="btn-call mb-2">
                    <i class="bi bi-telephone-fill"></i> Gọi ngay
                </a>

                <?php if (!empty($room['contact_zalo'])): ?>
                    <a href="https://zalo.me/<?= html_escape($room['contact_zalo']) ?>"
                       target="_blank" rel="noopener"
                       class="btn btn-outline-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-chat-dots-fill"></i> Chat Zalo
                    </a>
                <?php endif; ?>

                <hr>

                <div class="small text-muted mb-1">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <strong><?= html_escape($room['building_name']) ?></strong>
                </div>
                <div class="small text-muted"><?= html_escape($fullAddress) ?></div>

                <hr>

                <!-- Lưu yêu thích -->
                <button class="btn btn-outline-danger w-100 fw-semibold d-flex align-items-center justify-content-center gap-2"
                        onclick="toggleFavoriteDetail(<?= $room['id'] ?>, this)">
                    <i class="bi bi-heart" id="favIconDetail"></i> Lưu phòng yêu thích
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFavoriteDetail(roomId, btn) {
    <?php if (!is_logged_in()): ?>
        if (confirm('Bạn cần đăng nhập để lưu phòng yêu thích. Đi đến trang đăng nhập?')) {
            window.location.href = '/login';
        }
        return;
    <?php endif; ?>

    fetch(`/yeu-thich/toggle/${roomId}`, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        const icon = document.getElementById('favIconDetail');
        if (data.favorited) {
            icon.classList.replace('bi-heart', 'bi-heart-fill');
            btn.innerHTML = '<i class="bi bi-heart-fill" id="favIconDetail"></i> Đã lưu yêu thích';
        } else {
            icon.classList.replace('bi-heart-fill', 'bi-heart');
            btn.innerHTML = '<i class="bi bi-heart" id="favIconDetail"></i> Lưu phòng yêu thích';
        }
    });
}
</script>

<?php render_listing_footer(); ?>
