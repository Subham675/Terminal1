-- ⚠️ DEPRECATED (PostgreSQL) — already folded into database/schema_mysql.sql. Kept for history only.

-- ============================================================
--  Migration 002 — Payments, Rate Limiting, Slot Capacity
--  Run: psql -d terminal1_db -f database/migrations/002_payments_and_security.sql
-- ============================================================

-- ── PAYMENTS (Razorpay) ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS payments (
    id                  SERIAL PRIMARY KEY,
    booking_id          INT REFERENCES bookings(id) ON DELETE CASCADE,
    razorpay_order_id   VARCHAR(80)  UNIQUE NOT NULL,
    razorpay_payment_id VARCHAR(80),
    razorpay_signature  VARCHAR(255),
    razorpay_refund_id  VARCHAR(80),
    amount              NUMERIC(8,2) NOT NULL,       -- stored in rupees (not paise)
    currency            VARCHAR(10)  DEFAULT 'INR',
    status              VARCHAR(20)  DEFAULT 'created'
                         CHECK (status IN ('created','paid','failed','refunded')),
    created_at          TIMESTAMPTZ  DEFAULT NOW(),
    updated_at          TIMESTAMPTZ  DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_payments_booking ON payments(booking_id);
CREATE INDEX IF NOT EXISTS idx_payments_status  ON payments(status);

-- ── BOOKINGS: add deposit/payment tracking ───────────────────
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS deposit_amount NUMERIC(8,2) DEFAULT 0;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) DEFAULT 'unpaid'
    CHECK (payment_status IN ('unpaid','paid','refunded'));

-- ── RATE LIMITING (login + OTP brute-force protection) ───────
CREATE TABLE IF NOT EXISTS auth_attempts (
    id           SERIAL PRIMARY KEY,
    identifier   VARCHAR(180) NOT NULL,   -- email or IP address
    attempt_type VARCHAR(20)  NOT NULL,   -- 'login' | 'otp'
    success      BOOLEAN DEFAULT FALSE,
    created_at   TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_auth_attempts_lookup ON auth_attempts(identifier, attempt_type, created_at);

-- ── MENU ITEMS: image_url already exists in base schema, nothing to add ──
