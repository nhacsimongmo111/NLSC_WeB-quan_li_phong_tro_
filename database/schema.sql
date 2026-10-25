-- ====================================================================
-- NIÊN LUẬN CƠ SỞ: XÂY DỰNG WEBSITE QUẢN LÝ NHÀ TRỌ
-- Hệ quản trị CSDL: PostgreSQL
-- Chạy trên pgAdmin 4: Mở Query Tool -> Dán toàn bộ -> Execute (F5)
-- ====================================================================

-- 1. Xóa bảng cũ (theo thứ tự khóa ngoại)
DROP TABLE IF EXISTS user_favorites CASCADE;
DROP TABLE IF EXISTS room_inquiries CASCADE;
DROP TABLE IF EXISTS invoice_details CASCADE;
DROP TABLE IF EXISTS invoices CASCADE;
DROP TABLE IF EXISTS meter_readings CASCADE;
DROP TABLE IF EXISTS contract_members CASCADE;
DROP TABLE IF EXISTS contracts CASCADE;
DROP TABLE IF EXISTS tenants CASCADE;
DROP TABLE IF EXISTS rooms CASCADE;
DROP TABLE IF EXISTS room_types CASCADE;
DROP TABLE IF EXISTS buildings CASCADE;
DROP TABLE IF EXISTS service_settings CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- ====================================================================
-- 2. BẢNG USERS
-- ====================================================================
CREATE TABLE users (
    id             SERIAL PRIMARY KEY,
    username       VARCHAR(50)  UNIQUE,
    email          VARCHAR(100) NOT NULL UNIQUE,
    password_hash  VARCHAR(255),
    full_name      VARCHAR(100) NOT NULL,
    phone          VARCHAR(20),
    role           VARCHAR(20)  NOT NULL DEFAULT 'guest',
    google_id      VARCHAR(100) UNIQUE,
    avatar_url     TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ====================================================================
-- 3. BẢNG BUILDINGS (Cơ sở / Mànhà)
-- ====================================================================
CREATE TABLE buildings (
    id             SERIAL PRIMARY KEY,
    name           VARCHAR(150) NOT NULL,
    address        VARCHAR(200) NOT NULL,
    ward           VARCHAR(100) NOT NULL,
    district       VARCHAR(100) NOT NULL,
    city           VARCHAR(50)  NOT NULL DEFAULT 'Cần Thơ',
    phone          VARCHAR(20),
    zalo           VARCHAR(20),
    description    TEXT,
    is_active      BOOLEAN DEFAULT TRUE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ====================================================================
-- 4. BẢNG ROOM_TYPES
-- ====================================================================
CREATE TABLE room_types (
    id             SERIAL PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    description    TEXT
);

-- ====================================================================
-- 5. BẢNG ROOMS
-- ====================================================================
CREATE TABLE rooms (
    id                 SERIAL PRIMARY KEY,
    building_id        INT          NOT NULL REFERENCES buildings(id) ON DELETE CASCADE,
    room_type_id       INT          REFERENCES room_types(id) ON DELETE SET NULL,
    room_number        VARCHAR(20)  NOT NULL,
    area_m2            NUMERIC(6,1) NOT NULL DEFAULT 0,
    rent_price         NUMERIC(12,2) NOT NULL DEFAULT 0,
    deposit_months     SMALLINT     DEFAULT 1,
    electricity_price  NUMERIC(10,2) DEFAULT 3500,
    water_price        NUMERIC(10,2) DEFAULT 15000,
    amenities          JSONB        DEFAULT '[]',
    max_occupants      SMALLINT     DEFAULT 2,
    available_from     DATE,
    description        TEXT,
    status             VARCHAR(20)  NOT NULL DEFAULT 'available',
    is_published       BOOLEAN      DEFAULT TRUE,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (building_id, room_number)
);

-- ====================================================================
-- 6. BẢNG TENANTS
-- ====================================================================
CREATE TABLE tenants (
    id             SERIAL PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    citizen_id     VARCHAR(20)  NOT NULL UNIQUE,
    phone          VARCHAR(20)  NOT NULL,
    email          VARCHAR(100),
    gender         VARCHAR(10),
    birth_date     DATE,
    hometown       VARCHAR(200),
    username       VARCHAR(50) UNIQUE NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    license_plate  VARCHAR(30),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- ====================================================================
-- 7. BẢNG CONTRACTS
-- ====================================================================
CREATE TABLE contracts (
    id              SERIAL PRIMARY KEY,
    contract_code   VARCHAR(30)  NOT NULL UNIQUE,
    room_id         INT          NOT NULL REFERENCES rooms(id) ON DELETE RESTRICT,
    tenant_id       INT          NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT,
    start_date      DATE         NOT NULL,
    end_date        DATE,
    deposit_amount  NUMERIC(12,2) DEFAULT 0,
    rent_price      NUMERIC(12,2) NOT NULL,
    status          VARCHAR(20)  NOT NULL DEFAULT 'active',
    note            TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ====================================================================
-- 8. BẢNG CONTRACT_MEMBERS
-- ====================================================================
CREATE TABLE contract_members (
    id             SERIAL PRIMARY KEY,
    contract_id    INT          NOT NULL REFERENCES contracts(id) ON DELETE CASCADE,
    full_name      VARCHAR(100) NOT NULL,
    citizen_id     VARCHAR(20),
    phone          VARCHAR(20),
    relationship   VARCHAR(50)
);

-- ====================================================================
-- 9. BẢNG SERVICE_SETTINGS
-- ====================================================================
CREATE TABLE service_settings (
    id             SERIAL PRIMARY KEY,
    service_name   VARCHAR(50)  NOT NULL UNIQUE,
    unit_name      VARCHAR(20)  NOT NULL,
    unit_price     NUMERIC(12,2) NOT NULL,
    is_active      BOOLEAN DEFAULT TRUE
);

-- ====================================================================
-- 10. BẢNG METER_READINGS
-- ====================================================================
CREATE TABLE meter_readings (
    id               SERIAL PRIMARY KEY,
    room_id          INT      NOT NULL REFERENCES rooms(id) ON DELETE CASCADE,
    billing_month    SMALLINT NOT NULL,
    billing_year     SMALLINT NOT NULL,
    old_electricity  INT      NOT NULL DEFAULT 0,
    new_electricity  INT      NOT NULL DEFAULT 0,
    old_water        INT      NOT NULL DEFAULT 0,
    new_water        INT      NOT NULL DEFAULT 0,
    recorded_date    DATE     DEFAULT CURRENT_DATE,
    UNIQUE (room_id, billing_month, billing_year)
);

-- ====================================================================
-- 11. BẢNG INVOICES (đạt BCNF — tổng tiền tính qua VIEW)
-- ====================================================================
CREATE TABLE invoices (
    id                 SERIAL PRIMARY KEY,
    invoice_code       VARCHAR(30)   NOT NULL UNIQUE,
    contract_id        INT           NOT NULL REFERENCES contracts(id) ON DELETE RESTRICT,
    billing_month      SMALLINT      NOT NULL,
    billing_year       SMALLINT      NOT NULL,
    room_charge        NUMERIC(12,2) NOT NULL DEFAULT 0,
    electricity_usage  INT           DEFAULT 0,
    electricity_charge NUMERIC(12,2) DEFAULT 0,
    water_usage        INT           DEFAULT 0,
    water_charge       NUMERIC(12,2) DEFAULT 0,
    other_charge       NUMERIC(12,2) DEFAULT 0,
    payment_status     VARCHAR(20)   NOT NULL DEFAULT 'unpaid',
    paid_at            TIMESTAMP,
    note               TEXT,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (contract_id, billing_month, billing_year)
);

-- VIEW tính tổng hóa đơn (không lưu cột dẫn xuất => đạt BCNF)
CREATE OR REPLACE VIEW invoices_with_total AS
SELECT *,
       (room_charge + electricity_charge + water_charge + other_charge) AS total_amount
FROM invoices;

-- ====================================================================
-- 12. BẢNG ROOM_INQUIRIES (Form xem phòng từ khách vãng lai)
-- ====================================================================
CREATE TABLE room_inquiries (
    id             SERIAL PRIMARY KEY,
    room_id        INT          NOT NULL REFERENCES rooms(id) ON DELETE CASCADE,
    visitor_name   VARCHAR(100) NOT NULL,
    visitor_phone  VARCHAR(20)  NOT NULL,
    message        TEXT,
    is_read        BOOLEAN DEFAULT FALSE,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ====================================================================
-- 13. BẢNG USER_FAVORITES (Phòng yêu thích — khách đăng nhập Google)
-- ====================================================================
CREATE TABLE user_favorites (
    id         SERIAL PRIMARY KEY,
    user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    room_id    INT NOT NULL REFERENCES rooms(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, room_id)
);

-- ====================================================================
-- DỮ LIỆU MẪU (SEED DATA)
-- ====================================================================

INSERT INTO service_settings (service_name, unit_name, unit_price) VALUES
('Rác sinh hoạt', 'tháng', 30000),
('Internet Wifi',  'tháng', 50000);

INSERT INTO room_types (name, description) VALUES
('Phòng trọ thường',  'Phòng cơ bản không máy lạnh'),
('Phòng máy lạnh',    'Trang bị máy lạnh Inverter'),
('Phòng có gác lửng', 'Có tầng lửng làm chỗ ngủ'),
('Căn hộ mini',       'Rộng, có bếp và phòng tắm riêng biệt');

INSERT INTO buildings (name, address, ward, district, phone, zalo) VALUES
('Nhà trọ Ninh Kiều 1',        '12 Đường 3/2',                         'Phường Xuân Khánh', 'Quận Ninh Kiều', '0901111111', '0901111111'),
('Nhà trọ Ninh Kiều 2',        '45 Nguyễn Văn Cừ',                     'Phường An Hòa',     'Quận Ninh Kiều', '0901111111', '0901111111'),
('Nhà trọ Bình Thủy',          '88 Cách Mạng Tháng 8',                 'Phường Long Hòa',   'Quận Bình Thủy', '0901111111', '0901111111'),
('Nhà trọ Sinh viên An Bình',  '78 KDC Hồng Phát, Hoàng Quốc Việt',     'Phường An Bình',    'Quận Ninh Kiều', '0901234567', '0901234567'),
('Dãy trọ Đại học Tây Đô',     '36 Đường Trần Hưng Đạo',                'Phường Lê Bình',    'Quận Cái Răng',  '0909876543', '0909876543');

INSERT INTO rooms (building_id, room_type_id, room_number, area_m2, rent_price,
                   deposit_months, electricity_price, water_price,
                   amenities, max_occupants, status, is_published) VALUES
-- Cơ sở 1: Ninh Kiều 1
(1, 3, 'P.101', 18.0, 1500000, 1, 3500, 15000, '["wifi","wc_rieng","cho_de_xe"]',                        2, 'available',   TRUE),
(1, 3, 'P.102', 18.0, 1500000, 1, 3500, 15000, '["wifi","wc_rieng","cho_de_xe"]',                        2, 'rented',      FALSE),
(1, 2, 'P.201', 22.0, 2200000, 2, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","cho_de_xe"]',             2, 'available',   TRUE),
(1, 2, 'P.202', 22.0, 2200000, 2, 3500, 15000, '["wifi","dieu_hoa","wc_rieng"]',                         2, 'rented',      FALSE),
(1, 3, 'P.203', 19.0, 1600000, 1, 3500, 15000, '["wifi","wc_rieng","cho_de_xe","gac_lung"]',             2, 'available',   TRUE),
(1, 1, 'P.301', 15.0, 1200000, 1, 3500, 15000, '["wifi","cho_de_xe"]',                                   2, 'maintenance', FALSE),
(1, 1, 'P.302', 14.0,  950000, 1, 3500, 15000, '["wifi","cho_de_xe"]',                                   1, 'available',   TRUE),

-- Cơ sở 2: Ninh Kiều 2
(2, 2, 'A.101', 20.0, 1800000, 1, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","cho_de_xe"]',             2, 'available',   TRUE),
(2, 2, 'A.102', 22.0, 2100000, 1, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","cho_de_xe"]',             2, 'available',   TRUE),
(2, 4, 'A.201', 30.0, 3000000, 2, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","cho_de_xe","bep_rieng"]', 2, 'available',   TRUE),
(2, 4, 'A.202', 32.0, 3200000, 2, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","cho_de_xe","bep_rieng","may_giat"]', 3, 'available', TRUE),

-- Cơ sở 3: Bình Thủy
(3, 1, 'B.101', 16.0, 1100000, 1, 3500, 15000, '["wifi","cho_de_xe"]',                                   2, 'available',   TRUE),
(3, 2, 'B.102', 20.0, 1900000, 2, 3500, 15000, '["wifi","dieu_hoa","wc_rieng"]',                         2, 'rented',      FALSE),
(3, 3, 'B.201', 20.0, 1400000, 1, 3500, 15000, '["wifi","wc_rieng","cho_de_xe","gac_lung"]',             2, 'available',   TRUE),
(3, 1, 'B.202', 15.0,  900000, 1, 3500, 15000, '["wifi","cho_de_xe"]',                                   1, 'available',   TRUE),

-- Cơ sở 4: Sinh viên An Bình (Ninh Kiều)
(4, 1, 'C.101', 15.0,  850000, 1, 3500, 15000, '["wifi","cho_de_xe"]',                                   1, 'available',   TRUE),
(4, 3, 'C.102', 18.0, 1350000, 1, 3500, 15000, '["wifi","wc_rieng","cho_de_xe","gac_lung"]',             2, 'available',   TRUE),
(4, 2, 'C.201', 24.0, 2400000, 1, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","cho_de_xe","ban_cong"]', 2, 'available',   TRUE),

-- Cơ sở 5: Dãy trọ Đại học Tây Đô (Cái Răng)
(5, 1, 'CR.101', 16.0,  890000, 1, 3500, 15000, '["wifi","cho_de_xe"]',                                  1, 'available',   TRUE),
(5, 2, 'CR.102', 20.0, 1750000, 1, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","cho_de_xe"]',           2, 'available',   TRUE),
(5, 4, 'CR.201', 35.0, 3800000, 2, 3500, 15000, '["wifi","dieu_hoa","wc_rieng","bep_rieng","may_giat","ban_cong"]', 4, 'available', TRUE);

INSERT INTO tenants (
    full_name, citizen_id, phone, email, gender, birth_date,
    hometown, license_plate, username, password_hash
) VALUES
('Phạm Thị Hạnh',   '091300112233', '0901122334', 'phamhanh@example.com',  'Nữ',  '1995-04-12', 'Cần Thơ',    '51A-12345', 'phamh',    '$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Nguyễn Thị Lan',  '092400445566', '0902233445', 'lan.nguyen@example.com', 'Nữ',  '1993-07-08', 'Hậu Giang', NULL,        'nguyelan','$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Trần Văn Tuấn',   '093500778899', '0903344556', 'tuan.tran@example.com',  'Nam', '1990-02-20', 'An Giang',   '51B-67890','tranvantu','$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Lê Thị Thu',      '094600991122', '0904455667', 'thu.le@example.com',    'Nữ',  '1997-11-30', 'Bình Thủy', NULL,        'lethu',    '$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Vũ Văn Dũng',     '095700223344', '0905566778', 'dung.vu@example.com',   'Nam', '1988-05-15', 'Cái Răng',  '51C-13579','vuvandung','$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Đỗ Thị Mai',      '096800334455', '0906677889', 'mai.do@example.com',    'Nữ',  '1992-09-22', 'Phú Thọ',   NULL,        'domaimai', '$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Hoàng Văn Hùng',  '097900445566', '0907788990', 'hung.hoang@example.com','Nam', '1991-01-05', 'Hậu Giang', '51D-24680','hoangg',   '$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Ngô Thị Thảo',    '098100556677', '0908899001', 'thao.ngo@example.com',  'Nữ',  '1998-03-18', 'Cần Thơ',   NULL,        'ngothao',  '$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Phan Văn Minh',   '099200667788', '0909900112', 'minh.phan@example.com','Nam', '1985-12-10', 'Bến Tre',   '51E-11223','phanminh', '$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W'),
('Trịnh Thị Hương', '090300778899', '0901011223', 'huong.trinh@example.com','Nữ','1994-06-25', 'Vĩnh Long', NULL,        'trinhhuong','$2y$10$e0NR8dCrd17Bj9d3zkXFneXhEBl.aQ5DLeGdxD3J9EwQ4byJPYu2W');

