-- ============================================================
--  Terminal 1 — The Startup Canteen  |  MySQL / MariaDB Schema
--  For use with XAMPP (Apache + MySQL + PHP)
--
--  HOW TO IMPORT (phpMyAdmin):
--    1. Start Apache + MySQL from the XAMPP Control Panel
--    2. Open http://localhost/phpmyadmin
--    3. Click "New" → database name: terminal1_db → Create
--    4. Select terminal1_db → "Import" tab → choose this file → Go
--
--  OR via command line:
--    mysql -u root -p -e "CREATE DATABASE terminal1_db;"
--    mysql -u root -p terminal1_db < database/schema_mysql.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS terminal1_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE terminal1_db;

-- ── USERS ────────────────────────────────────────────────────
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(180) UNIQUE NOT NULL,
    password        VARCHAR(255),                    -- NULL for Google-only accounts
    role            VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin','user')),
    google_id       VARCHAR(120) UNIQUE,
    avatar          VARCHAR(300),
    is_verified     BOOLEAN DEFAULT FALSE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── OTP ──────────────────────────────────────────────────────
CREATE TABLE otps (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT,
    email       VARCHAR(180) NOT NULL,
    otp_code    VARCHAR(255) NOT NULL,               -- stores a bcrypt hash, not the raw OTP
    purpose     VARCHAR(30)  DEFAULT 'google_verify', -- google_verify | login | register
    expires_at  DATETIME     NOT NULL,
    used        BOOLEAN DEFAULT FALSE,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── MENU CATEGORIES ──────────────────────────────────────────
CREATE TABLE menu_categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    slug        VARCHAR(80) UNIQUE NOT NULL,
    sort_order  INT DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── MENU ITEMS ───────────────────────────────────────────────
CREATE TABLE menu_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    category_id     INT,
    name            VARCHAR(120) NOT NULL,
    description     TEXT,
    price           DECIMAL(8,2) NOT NULL,
    badge           VARCHAR(40),      -- e.g. "New", "Signature", "Popular"
    is_available    BOOLEAN DEFAULT TRUE,
    is_veg          BOOLEAN DEFAULT FALSE,
    image_url       VARCHAR(300),
    sort_order      INT DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── BOOKINGS ─────────────────────────────────────────────────
CREATE TABLE bookings (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT,
    name            VARCHAR(120) NOT NULL,
    phone           VARCHAR(20)  NOT NULL,
    email           VARCHAR(180),
    occasion        VARCHAR(80),
    guests          SMALLINT DEFAULT 1,
    booking_date    DATE,
    booking_time    TIME,
    message         TEXT,
    status          VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','confirmed','cancelled','completed')),
    -- Added by migration 002 (Razorpay payments):
    deposit_amount  DECIMAL(8,2) DEFAULT 0,
    payment_status  VARCHAR(20) DEFAULT 'unpaid' CHECK (payment_status IN ('unpaid','paid','refunded')),
    -- Added by migration 003 (real-time tracking):
    tracking_token  VARCHAR(64) UNIQUE,               -- random token, required to open the live-status stream for this booking
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── PAYMENTS (Razorpay) — from migration 002 ──────────────────
CREATE TABLE payments (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    booking_id          INT,
    razorpay_order_id   VARCHAR(80)  UNIQUE NOT NULL,
    razorpay_payment_id VARCHAR(80),
    razorpay_signature  VARCHAR(255),
    razorpay_refund_id  VARCHAR(80),
    amount              DECIMAL(8,2) NOT NULL,        -- stored in rupees (not paise)
    currency            VARCHAR(10)  DEFAULT 'INR',
    status              VARCHAR(20)  DEFAULT 'created' CHECK (status IN ('created','paid','failed','refunded')),
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── RATE LIMITING (login/OTP brute-force protection) — from migration 002 ──
CREATE TABLE auth_attempts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    identifier   VARCHAR(180) NOT NULL,   -- email or IP address
    attempt_type VARCHAR(20)  NOT NULL,   -- 'login' | 'login_ip' | 'otp' | 'otp_resend' | 'register'
    success      BOOLEAN DEFAULT FALSE,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── ADMIN USER CREATION ──────────────────────────────────────
-- Security Notice: No default admin account is seeded into this schema.
-- To create your initial admin account securely, run via CLI:
--   php scripts/create_admin.php


-- ── SEED: MENU CATEGORIES ────────────────────────────────────
INSERT INTO menu_categories (name, slug, sort_order) VALUES
    ('Chef''s Specials',  'specials',     1),
    ('Rice & Polao',      'rice',         2),
    ('Starters',          'starters',     3),
    ('Breads',            'breads',       4),
    ('Celebration Thali', 'thali',        5),
    ('Beverages',         'beverages',    6);

-- ── SEED: MENU ITEMS ─────────────────────────────────────────
INSERT INTO menu_items (category_id, name, description, price, badge, is_veg) VALUES
    (1, 'Dhonkami Chicken 4.0', '2 Naan + 1 Kulcha + 2 Corn — the ultimate Terminal experience', 850.00, 'Signature', FALSE),
    (1, 'Dhonkami Chicken',     'Legendary smoky bold chicken preparation',                      690.00, NULL,        FALSE),
    (1, 'Sendori Chicken',      'Rich slow-cooked chicken with aromatic spices',                 600.00, NULL,        FALSE),
    (1, 'Jangli Chicken',       'Wild-spiced rustic chicken packed with flavour',                630.00, NULL,        FALSE),
    (2, 'Kashmiri Polao',       'Fragrant saffron-kissed rice with dried fruits',                260.00, NULL,        TRUE),
    (2, 'Mixed Fried Rice',     NULL,                                                            230.00, NULL,        FALSE),
    (2, 'Egg Chicken Fried Rice',NULL,                                                           210.00, NULL,        FALSE),
    (2, 'Golden Garlic Fried Rice',NULL,                                                         200.00, 'New',       TRUE),
    (2, 'Chicken Fried Rice',   NULL,                                                            190.00, NULL,        FALSE),
    (2, 'Schezwan Fried Rice',  NULL,                                                            180.00, 'New',       TRUE),
    (2, 'Egg Fried Rice',       NULL,                                                            170.00, NULL,        FALSE),
    (2, 'Veg Polao',            NULL,                                                            160.00, NULL,        TRUE),
    (2, 'Sweet Fried Rice',     NULL,                                                            150.00, 'New',       TRUE),
    (2, 'Mushroom Fried Rice',  NULL,                                                            140.00, 'New',       TRUE),
    (2, 'Spicy Fried Rice',     NULL,                                                            120.00, NULL,        TRUE),
    (2, 'Steam Rice',           NULL,                                                             70.00, NULL,        TRUE);

-- ── INDEXES ──────────────────────────────────────────────────
CREATE INDEX idx_users_email          ON users(email);
CREATE INDEX idx_users_google_id      ON users(google_id);
CREATE INDEX idx_otps_email           ON otps(email);
CREATE INDEX idx_otps_expires         ON otps(expires_at);
CREATE INDEX idx_bookings_status      ON bookings(status);
CREATE INDEX idx_bookings_date        ON bookings(booking_date);
CREATE INDEX idx_bookings_tracking    ON bookings(tracking_token);
CREATE INDEX idx_menu_category        ON menu_items(category_id);
CREATE INDEX idx_payments_booking     ON payments(booking_id);
CREATE INDEX idx_payments_status      ON payments(status);
CREATE INDEX idx_auth_attempts_lookup ON auth_attempts(identifier, attempt_type, created_at);
