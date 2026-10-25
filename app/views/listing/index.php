<?php
require_once ROOTDIR . 'app/views/partials/listing_layout.php';
render_listing_header('Tìm phòng trọ Cần Thơ');

// Các khoảng giá để lọc
$priceRanges = [
    ['label' => 'Dưới 1 triệu',   'min' => '',        'max' => 1000000],
    ['label' => '1 - 2 triệu',    'min' => 1000000,   'max' => 2000000],
    ['label' => '2 - 3 triệu',    'min' => 2000000,   'max' => 3000000],
    ['label' => '3 - 5 triệu',    'min' => 3000000,   'max' => 5000000],
    ['label' => 'Trên 5 triệu',   'min' => 5000000,   'max' => ''],
];

$sortOptions = [
    'newest'     => 'Mới nhất',
    'price_asc'  => 'Giá tăng dần',
    'price_desc' => 'Giá giảm dần',
    'area_asc'   => 'Diện tích nhỏ nhất',
    'area_desc'  => 'Diện tích lớn nhất',
];

// Danh sách tiện ích có thể lọc
$amenityOptions = [
    'wifi'      => ['label' => 'Wifi',        'icon' => 'bi-wifi'],
    'dieu_hoa'  => ['label' => 'Điều hòa',    'icon' => 'bi-thermometer-snow'],
    'wc_rieng'  => ['label' => 'WC riêng',    'icon' => 'bi-droplet-fill'],
    'cho_de_xe' => ['label' => 'Chỗ để xe',   'icon' => 'bi-bicycle'],
    'bep_rieng' => ['label' => 'Bếp riêng',   'icon' => 'bi-fire'],
    'may_giat'  => ['label' => 'Máy giặt',    'icon' => 'bi-gear-wide'],
];

$selectedAmenities = $filters['amenities'] ?? [];
?>

<div class="container-xl py-3">
    <!-- Breadcrumb nhỏ -->
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="/" class="text-muted text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold">Phòng trọ Cần Thơ</li>
        </ol>
    </nav>

    <div class="row g-3 align-items-start">

        <!-- ============================== -->
        <!-- CỘT TRÁI: BỘ LỌC (Sidebar)    -->
        <!-- ============================== -->
        <div class="col-lg-3 col-xl-2-5 d-none d-lg-block" style="max-width: 280px;">
            <form action="/phong-tro" method="GET" id="filterForm">
                <div class="filter-sidebar">
                    <div class="filter-title"><i class="bi bi-funnel-fill me-1"></i> Bộ lọc tìm kiếm</div>

                    <!-- Tìm từ khóa -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Từ khóa</label>
                        <input type="text" name="keyword" class="form-control form-control-sm"
                               placeholder="Tên đường, phường, quận..."
                               value="<?= html_escape($filters['keyword']) ?>">
                    </div>

                    <!-- Quận/Huyện -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Quận / Huyện</label>
                        <select name="district" class="form-select form-select-sm" id="districtSelect"
                                onchange="loadWards(this.value)">
                            <option value="">-- Tất cả quận/huyện --</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?= html_escape($d) ?>" <?= $filters['district'] === $d ? 'selected' : '' ?>>
                                    <?= html_escape($d) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Phường/Xã (load động) -->
                    <div class="mb-3" id="wardWrapper" <?= empty($filters['district']) ? 'style="display:none"' : '' ?>>
                        <label class="form-label small fw-semibold">Phường / Xã</label>
                        <select name="ward" class="form-select form-select-sm" id="wardSelect">
                            <option value="">-- Tất cả phường/xã --</option>
                            <?php foreach ($wards as $w): ?>
                                <option value="<?= html_escape($w) ?>" <?= $filters['ward'] === $w ? 'selected' : '' ?>>
                                    <?= html_escape($w) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Khoảng giá -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Khoảng giá</label>
                        <div class="d-flex flex-column gap-1">
                            <a href="?<?= http_build_query(array_merge($filters, ['min_price' => '', 'max_price' => '', 'sort' => $sort])) ?>"
                               class="sort-btn <?= (empty($filters['min_price']) && empty($filters['max_price'])) ? 'active' : '' ?>">
                                Tất cả mức giá
                            </a>
                            <?php foreach ($priceRanges as $pr): ?>
                                <?php
                                    $isActive = (string)($filters['min_price'] ?? '') === (string)$pr['min']
                                             && (string)($filters['max_price'] ?? '') === (string)$pr['max'];
                                ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['min_price' => $pr['min'], 'max_price' => $pr['max'], 'sort' => $sort])) ?>"
                                   class="sort-btn <?= $isActive ? 'active' : '' ?>">
                                    <?= $pr['label'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Diện tích -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Diện tích (m²)</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_area" class="form-control form-control-sm"
                                   placeholder="Từ" min="0"
                                   value="<?= html_escape($filters['min_area']) ?>">
                            <input type="number" name="max_area" class="form-control form-control-sm"
                                   placeholder="Đến" min="0"
                                   value="<?= html_escape($filters['max_area']) ?>">
                        </div>
                    </div>

                    <!-- Tiện ích -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tiện ích</label>
                        <div class="d-flex flex-column gap-1">
                            <?php foreach ($amenityOptions as $key => $info): ?>
                                <label class="amenity-checkbox-label">
                                    <input type="checkbox" name="amenities[]" value="<?= $key ?>"
                                           <?= in_array($key, $selectedAmenities) ? 'checked' : '' ?>>
                                    <i class="bi <?= $info['icon'] ?> small"></i>
                                    <?= $info['label'] ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Nút lọc -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                            <i class="bi bi-search me-1"></i> Tìm kiếm
                        </button>
                        <a href="/phong-tro" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============================== -->
        <!-- CỘT PHẢI: DANH SÁCH PHÒNG     -->
        <!-- ============================== -->
        <div class="col-lg">

            <!-- Thanh tìm kiếm mobile -->
            <form action="/phong-tro" method="GET" class="d-lg-none mb-3">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control"
                           placeholder="Tìm theo tên đường, phường, quận..."
                           value="<?= html_escape($filters['keyword']) ?>">
                    <button class="btn btn-danger" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <!-- Thanh kết quả + sắp xếp -->
            <div class="sort-bar mb-3">
                <span class="sort-label fw-semibold">
                    <i class="bi bi-house-door me-1 text-danger"></i>
                    <strong class="text-danger"><?= $totalResults ?></strong> phòng trống tại Cần Thơ
                </span>
                <div class="ms-auto d-flex align-items-center gap-1 flex-wrap">
                    <span class="sort-label">Sắp xếp:</span>
                    <?php foreach ($sortOptions as $key => $label): ?>
                        <a href="?<?= http_build_query(array_merge($filters, ['sort' => $key])) ?>"
                           class="sort-btn <?= $sort === $key ? 'active' : '' ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- DANH SÁCH PHÒNG -->
            <?php if (empty($rooms)): ?>
                <div class="empty-state">
                    <i class="bi bi-house-slash text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold">Không tìm thấy phòng phù hợp</h5>
                    <p class="text-muted mb-3">Thử điều chỉnh bộ lọc hoặc tìm kiếm với từ khóa khác.</p>
                    <a href="/phong-tro" class="btn btn-danger btn-sm px-4">Xem tất cả phòng</a>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($rooms as $room):
                        $amenities = \App\Models\RoomListing::parseAmenities($room['amenities']);
                        $fullAddress = $room['address'] . ', ' . $room['ward'] . ', ' . $room['district'] . ', ' . $room['city'];
                        $timeAgo = '';
                        if (!empty($room['created_at'])) {
                            $diff = time() - strtotime($room['created_at']);
                            if ($diff < 3600)       $timeAgo = 'Vừa đăng';
                            elseif ($diff < 86400)  $timeAgo = floor($diff/3600) . ' giờ trước';
                            else                    $timeAgo = floor($diff/86400) . ' ngày trước';
                        }
                    ?>
                        <div class="room-card" onclick="window.location.href='/phong-tro/<?= $room['id'] ?>'">

                            <!-- KHUNG ẢNH PLACEHOLDER -->
                            <div class="room-card-img">
                                <i class="bi bi-image img-placeholder-icon"></i>
                                <span class="img-placeholder-text">Chưa có ảnh</span>
                                <?php if (!empty($room['type_name'])): ?>
                                    <span class="position-absolute bottom-0 start-0 m-2 room-type-badge">
                                        <?= html_escape($room['type_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- NỘI DUNG CARD -->
                            <div class="room-card-body">
                                <div class="room-card-title">
                                    <?= html_escape($room['building_name']) ?> — Phòng <?= html_escape($room['room_number']) ?>
                                </div>

                                <div class="room-card-price">
                                    <?= format_currency($room['rent_price']) ?><span class="fw-normal text-muted fs-6">/tháng</span>
                                </div>

                                <div class="room-card-meta">
                                    <span><i class="bi bi-aspect-ratio text-secondary"></i> <?= $room['area_m2'] ?? '0' ?> m²</span>
                                    <span><i class="bi bi-people text-secondary"></i> <?= (int)($room['max_occupants'] ?? 2) ?> người</span>
                                    <span><i class="bi bi-shield-check text-secondary"></i> Cọc <?= (int)($room['deposit_months'] ?? 1) ?> tháng</span>
                                    <?php if (!empty($timeAgo)): ?>
                                        <span class="ms-auto text-muted" style="font-size: 11.5px;"><?= $timeAgo ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="room-card-address">
                                    <i class="bi bi-geo-alt-fill"></i> <?= html_escape($fullAddress) ?>
                                </div>

                                <!-- Hàng đáy bên trong khung: Tiện ích bên trái, SĐT và Lưu bên phải -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-auto pt-2">
                                    <!-- Tiện ích -->
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if (!empty($amenities)): ?>
                                            <?php foreach (array_slice($amenities, 0, 3) as $key):
                                                $info = $amenityLabels[$key] ?? null;
                                                if (!$info) continue;
                                            ?>
                                                <span class="amenity-tag">
                                                    <i class="bi <?= $info['icon'] ?>"></i>
                                                    <?= $info['label'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($amenities) > 3): ?>
                                                <span class="amenity-tag bg-light text-secondary border">
                                                    +<?= count($amenities) - 3 ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Nút SĐT & Lưu yêu thích (gọn gàng bên trong khung) -->
                                    <div class="d-flex align-items-center gap-2 ms-auto" onclick="event.stopPropagation();">
                                        <a href="tel:<?= html_escape($room['contact_phone'] ?? '') ?>"
                                           class="btn-contact-inline"
                                           title="Gọi cho chủ trọ">
                                            <i class="bi bi-telephone-fill"></i>
                                            <span><?= html_escape($room['contact_phone'] ?? 'Xem SĐT') ?></span>
                                        </a>

                                        <button type="button" class="btn-favorite-inline"
                                                onclick="toggleFavorite(<?= $room['id'] ?>, this);"
                                                title="Lưu yêu thích">
                                            <i class="bi bi-heart"></i>
                                            <span class="small d-none d-sm-inline">Lưu</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Load phường/xã theo quận qua AJAX
async function loadWards(district) {
    const wardWrapper = document.getElementById('wardWrapper');
    const wardSelect  = document.getElementById('wardSelect');

    if (!district) {
        wardWrapper.style.display = 'none';
        wardSelect.innerHTML = '<option value="">-- Tất cả phường/xã --</option>';
        return;
    }

    const res    = await fetch(`/api/phuong?district=${encodeURIComponent(district)}`);
    const wards  = await res.json();

    wardSelect.innerHTML = '<option value="">-- Tất cả phường/xã --</option>';
    wards.forEach(w => {
        const opt = document.createElement('option');
        opt.value = w;
        opt.textContent = w;
        wardSelect.appendChild(opt);
    });

    wardWrapper.style.display = '';
}

// Yêu thích (cần đăng nhập)
function toggleFavorite(roomId, btn) {
    <?php if (!is_logged_in()): ?>
        if (confirm('Bạn cần đăng nhập để lưu phòng yêu thích. Đi đến trang đăng nhập?')) {
            window.location.href = '/login';
        }
        return;
    <?php endif; ?>

    fetch(`/yeu-thich/toggle/${roomId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const icon = btn.querySelector('i');
        if (data.favorited) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            btn.classList.add('active');
        } else {
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            btn.classList.remove('active');
        }
    });
}
</script>

<?php render_listing_footer(); ?>
