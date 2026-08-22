-- ============================================================
--  Terminal 1 — The Startup Canteen  |  PostgreSQL Schema
-- ============================================================

CREATE DATABASE terminal1_db;
\c terminal1_db;

-- EXTENSIONS
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ── USERS ────────────────────────────────────────────────────
CREATE TABLE users (
    id              SERIAL PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(180) UNIQUE NOT NULL,
    password        VARCHAR(255),                    -- NULL for Google-only accounts
    role            VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin','user')),
    google_id       VARCHAR(120) UNIQUE,
    avatar          VARCHAR(300),
    is_verified     BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ── OTP ──────────────────────────────────────────────────────
CREATE TABLE otps (
    id          SERIAL PRIMARY KEY,
    user_id     INT REFERENCES users(id) ON DELETE CASCADE,
    email       VARCHAR(180) NOT NULL,
    otp_code    VARCHAR(10)  NOT NULL,
    purpose     VARCHAR(30)  DEFAULT 'google_verify',  -- google_verify | login | register
    expires_at  TIMESTAMPTZ  NOT NULL,
    used        BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMPTZ  DEFAULT NOW()
);

-- ── MENU CATEGORIES ──────────────────────────────────────────
CREATE TABLE menu_categories (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    slug        VARCHAR(80) UNIQUE NOT NULL,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMPTZ DEFAULT NOW()
);

-- ── MENU ITEMS ───────────────────────────────────────────────
CREATE TABLE menu_items (
    id              SERIAL PRIMARY KEY,
    category_id     INT REFERENCES menu_categories(id) ON DELETE SET NULL,
    name            VARCHAR(120) NOT NULL,
    description     TEXT,
    price           NUMERIC(8,2) NOT NULL,
    badge           VARCHAR(40),      -- e.g. "New", "Signature", "Popular"
    is_available    BOOLEAN DEFAULT TRUE,
    is_veg          BOOLEAN DEFAULT FALSE,
    image_url       VARCHAR(300),
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ── BOOKINGS ─────────────────────────────────────────────────
CREATE TABLE bookings (
    id              SERIAL PRIMARY KEY,
    user_id         INT REFERENCES users(id) ON DELETE SET NULL,
    name            VARCHAR(120) NOT NULL,
    phone           VARCHAR(20)  NOT NULL,
    email           VARCHAR(180),
    occasion        VARCHAR(80),
    guests          SMALLINT DEFAULT 1,
    booking_date    DATE,
    booking_time    TIME,
    message         TEXT,
    status          VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','confirmed','cancelled','completed')),
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- ── SEED: ADMIN USER ─────────────────────────────────────────
-- Password: Admin@1234  (change immediately after deploy!)
INSERT INTO users (name, email, password, role, is_verified)
VALUES (
    'Terminal Admin',
    'admin@terminal1.in',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    TRUE
);

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
CREATE INDEX idx_users_email       ON users(email);
CREATE INDEX idx_users_google_id   ON users(google_id);
CREATE INDEX idx_otps_email        ON otps(email);
CREATE INDEX idx_otps_expires      ON otps(expires_at);
CREATE INDEX idx_bookings_status   ON bookings(status);
CREATE INDEX idx_bookings_date     ON bookings(booking_date);
CREATE INDEX idx_menu_category     ON menu_items(category_id);
