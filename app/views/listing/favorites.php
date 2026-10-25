<?php
require_once ROOTDIR . 'app/views/partials/listing_layout.php';
render_listing_header('Phòng đã lưu');
?>
<style>
    /* Toast thông báo xám bo tròn ở đáy màn hình */
    .custom-toast {
        position: fixed;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        background-color: #333333;
        color: #ffffff;
        padding: 12px 36px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        pointer-events: none;
    }

    .custom-toast.show {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    /* Kiểu dáng nút trái tim bật/tắt */
    .btn-favorite-toggle {
        background: transparent;
        border: none;
        padding: 6px;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .btn-favorite-toggle:hover {
        transform: scale(1.15);
    }

    .btn-favorite-toggle .bi-heart-fill {
        color: #d0021b;
        /* Màu đỏ khi đã lưu */
    }

    .btn-favorite-toggle .bi-heart {
        color: #666666;
        /* Màu xám viền khi bỏ lưu */
    }
</style>
<div class="container-xl py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-muted text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="/phong-tro" class="text-muted text-decoration-none">Phòng trọ Cần Thơ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tin đã lưu</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <h4 class="fw-bold mb-0">Tin đã lưu</h4>
                <span class="badge bg-danger rounded-pill fs-6"><?= count($favorites) ?> tin</span>
            </div>

            <?php if (empty($favorites)): ?>
                <div class="text-center py-5 bg-light rounded-3 border">
                    <i class="bi bi-heart-break text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold">Bạn chưa lưu phòng nào</h5>
                    <p class="text-muted">Hãy lướt xem các phòng trọ và bấm trái tim để lưu lại nhé.</p>
                    <a href="/phong-tro" class="btn btn-danger mt-2 px-4">Tìm phòng ngay</a>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($favorites as $room):
                        $fullAddress = $room['district'] . ', ' . $room['city'];
                        $timeAgo = '';
                        if (!empty($room['created_at'])) {
                            $diff = time() - strtotime($room['created_at']);
                            if ($diff < 3600)       $timeAgo = 'Vừa đăng';
                            elseif ($diff < 86400)  $timeAgo = floor($diff / 3600) . ' giờ trước';
                            else                    $timeAgo = floor($diff / 86400) . ' ngày trước';
                        }
                    ?>
                        <div class="room-card d-flex position-relative border bg-white rounded-3 shadow-sm overflow-hidden"
                            style="cursor: pointer;"
                            onclick="window.location.href='/phong-tro/<?= $room['room_id'] ?>'">

                            <!-- Nút trái tim bật/tắt -->
                            <button type="button"
                                class="btn-favorite-toggle position-absolute top-0 end-0 m-2"
                                onclick="event.stopPropagation(); toggleFavoriteItem(<?= $room['room_id'] ?>, this);"
                                title="Bật/Tắt yêu thích">
                                <i class="bi bi-heart-fill fs-5 fav-icon"></i>
                            </button>

                            <!-- Khung ảnh -->
                            <div class="bg-light d-flex align-items-center justify-content-center border-end" style="width: 160px; min-width: 160px; min-height: 140px;">
                                <i class="bi bi-image text-muted fs-1"></i>
                            </div>

                            <!-- Nội dung -->
                            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">
                                        <?= html_escape($room['building_name']) ?> — Phòng <?= html_escape($room['room_number']) ?>
                                    </h6>
                                    <div class="text-danger fw-bold mb-2">
                                        <?= format_currency($room['rent_price']) ?><span class="fw-normal text-muted small">/tháng</span>
                                    </div>
                                    <div class="small text-muted mb-1">
                                        <i class="bi bi-aspect-ratio"></i> <?= $room['area_m2'] ?> m²
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-geo-alt"></i> <?= html_escape($fullAddress) ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-end mt-2">
                                    <div class="small text-muted">
                                        Lưu lúc: <?= date('d/m/Y H:i', strtotime($room['saved_at'])) ?>
                                    </div>
                                    <?php if (!empty($timeAgo)): ?>
                                        <span class="badge bg-light text-dark border"><?= $timeAgo ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Khung Toast Popup ẩn -->
<div id="customToast" class="custom-toast"></div>

<script>
let toastTimer = null;

// Hàm hiển thị Popup Toast xám dưới đáy màn hình
function showToast(message) {
    const toast = document.getElementById('customToast');
    if (!toast) return;

    toast.textContent = message;
    toast.classList.add('show');

    if (toastTimer) clearTimeout(toastTimer);

    toastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}

// Hàm xử lý Bật/Tắt trái tim và xóa/thêm tin
function toggleFavoriteItem(roomId, btn) {
    const icon = btn.querySelector('.fav-icon');
    const card = btn.closest('.room-card');

    fetch(`/yeu-thich/toggle/${roomId}`, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            showToast(data.message || 'Có lỗi xảy ra');
            return;
        }

        if (data.favorited) {
            // Trường hợp BẬT lại yêu thích (Lưu lại)
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            showToast('Đã lưu tin');

            // Hủy đếm ngược xóa thẻ nếu người dùng bấm lưu lại kịp thời
            if (card.removeTimeout) {
                clearTimeout(card.removeTimeout);
                card.removeTimeout = null;
            }
            card.style.opacity = "1";
            card.style.transform = "none";
        } else {
            // Trường hợp TẮT yêu thích (Bỏ lưu -> Hiện trái tim XÁM)
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            showToast('Đã bỏ lưu');

            // Đợi 0.7s để người dùng nhìn thấy rõ trái tim xám & Toast "Đã bỏ lưu"
            card.removeTimeout = setTimeout(() => {
                card.style.transition = "all 0.4s ease";
                card.style.opacity = "0";
                card.style.transform = "scale(0.95)";

                setTimeout(() => {
                    card.remove();
                    updateSavedCount();
                }, 400);
            }, 700);
        }
    })
    .catch(err => {
        console.error('Lỗi:', err);
        showToast('Không thể kết nối máy chủ');
    });
}

// Hàm cập nhật số lượng tin đã lưu trên Badge
function updateSavedCount() {
    const remainingCards = document.querySelectorAll('.room-card').length;
    
    const badge = document.querySelector('.badge.bg-danger');
    if (badge) {
        badge.textContent = remainingCards + ' tin';
    }

    // Nếu đã xóa hết, tải lại trang để hiển thị thông báo "Chưa lưu phòng nào"
    if (remainingCards === 0) {
        setTimeout(() => location.reload(), 300);
    }
}
</script>
    

<?php render_listing_footer(); ?>

