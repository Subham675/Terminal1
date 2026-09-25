# Terminal 1 — Feature Additions Changelog

This documents everything added on top of the original booking system, in order.

## 1. Razorpay Payment System
- `app/config/Razorpay.php` — lightweight cURL-based Razorpay REST client (no SDK/composer dependency required)
- `app/models/Payment.php` — payments table CRUD + revenue analytics queries
- `app/controllers/PaymentController.php` — order creation, signature verification, webhook handler, admin-triggered refunds
- Booking form (`home.php`) now opens Razorpay Checkout after a reservation is submitted, if a deposit is required
- **Server-side signature verification** on every payment (never trusts the client alone) — see `Razorpay::verifySignature()`
- Webhook endpoint (`/payments/webhook`) as the source of truth for payment status, independent of the browser flow

## 2. Email Confirmation + PDF Invoice
- `Mailer::sendBookingConfirmation()` — sent automatically once a payment is verified
- `app/models/Invoice.php` — generates a PDF invoice via `dompdf` (optional composer package)
- Gracefully degrades: if `dompdf`/`PHPMailer` aren't installed, the email still sends (via PHP's built-in `mail()`), just without the PDF attachment

## 3. Admin Analytics Dashboard
- Revenue chart (last 14 days) and bookings chart (last 14 days) using Chart.js
- New stat cards: Total Revenue, Payments Received, Refunded count
- `Payment::revenueByDay()` and `Booking::bookingsByDay()` power the charts

## 4. Rate Limiting (Brute-Force Protection)
- `app/middleware/RateLimiter.php` — tracks failed attempts in a new `auth_attempts` table
- Wired into both `AuthController::login()` and `AuthController::verifyOtp()`
- Default: 5 failed attempts locks out for 15 minutes (configurable in the class)

## 5. Menu Image Upload
- `app/middleware/FileUpload.php` — validates MIME type (JPG/PNG/WEBP only, via `finfo`, not just the extension), enforces a 3MB size cap, generates random filenames (no path traversal risk)
- Admin menu forms (`add`/`edit`) now have a file input; old images are cleaned up on replace/delete

## 6. Booking Slot Availability
- `Booking::countForSlot()` checks existing non-cancelled bookings for the same date+time
- Configurable via `SLOT_CAPACITY` in `.env` (default: 8 per slot)
- Booking form now returns HTTP 409 with a clear message if a slot is full

## 7. Automated Tests
- `tests/RazorpaySignatureTest.php` — verifies the HMAC signature logic can't be bypassed or reused across orders
- `tests/HelpersTest.php` — covers `sanitize()`, `e()`, `isAdmin()` etc.
- Run with: `composer install && composer test`
- **Note:** these are unit tests with no live database — they test pure logic (signature verification, sanitization). Testing `Booking`/`User`/`Payment` models directly would need a running PostgreSQL instance (see Docker setup below) and is a good next step if you want deeper coverage.

## 8. Docker + Docker Compose
- `Dockerfile` — PHP 8.2-FPM with required extensions (`pdo_pgsql`, `curl`) + Composer
- `docker-compose.yml` — one-command local stack: app + Nginx + PostgreSQL (auto-loads `schema.sql` + migrations on first run)
- `nginx.local.conf` — HTTP-only config for local dev (the existing `nginx.conf` is for production HTTPS deploys)
- Run: `docker compose up --build` → visit `http://localhost:8080`

## 9. GitHub Actions CI
- `.github/workflows/ci.yml` — runs on every push/PR to `main`
- Steps: PHP syntax lint on every file → `composer validate` → `composer install` → `composer test`

---

## New Environment Variables (add to your `.env`)

```
RAZORPAY_KEY_ID=rzp_test_YOUR_KEY_ID
RAZORPAY_KEY_SECRET=YOUR_KEY_SECRET
RAZORPAY_WEBHOOK_SECRET=YOUR_WEBHOOK_SECRET
DEPOSIT_AMOUNT=100
SLOT_CAPACITY=8
```

Get test-mode Razorpay keys free at: https://dashboard.razorpay.com/app/keys

## New Database Migration

Run this once against your existing database:
```bash
psql -d terminal1_db -f database/migrations/002_payments_and_security.sql
```
(If you're setting up fresh via Docker Compose, this runs automatically.)

## Optional Composer Packages

Neither is required for the app to run, but both unlock features:
```bash
composer require phpmailer/phpmailer   # SMTP email + attachments
composer require dompdf/dompdf          # PDF invoice generation
```

## Honest Notes for Interviews

- Razorpay integration is in **test mode** — say so plainly if asked; production keys were never used
- The signature verification is the security-critical part — be ready to explain *why* it matters (a client could otherwise fake a "successful" payment)
- The webhook handler exists because client-side confirmation alone isn't trustworthy — the webhook is the real source of truth
- Rate limiting is basic (per-identifier, database-backed) — a production system at scale would likely use Redis instead of a SQL table for this
