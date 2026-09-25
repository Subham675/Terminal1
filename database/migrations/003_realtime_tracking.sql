-- ⚠️ DEPRECATED (PostgreSQL) — already folded into database/schema_mysql.sql. Kept for history only.

-- ============================================================
--  Migration 003 — Real-Time Booking Tracking
--  Run: psql -d terminal1_db -f database/migrations/003_realtime_tracking.sql
-- ============================================================

-- A random, unguessable token per booking — required to open the live status
-- stream for that booking. Prevents anyone from tracking someone else's
-- booking just by guessing sequential booking IDs.
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS tracking_token VARCHAR(64) UNIQUE;

-- Backfill existing rows with a token so old bookings remain trackable too.
UPDATE bookings SET tracking_token = encode(gen_random_bytes(24), 'hex')
WHERE tracking_token IS NULL;

CREATE INDEX IF NOT EXISTS idx_bookings_tracking_token ON bookings(tracking_token);
