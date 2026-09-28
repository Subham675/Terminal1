# Terminal 1 — Fine Dining & Table Reservation Platform

[![CI](https://github.com/Subham675/Terminal1/actions/workflows/ci.yml/badge.svg)](https://github.com/Subham675/Terminal1/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-11%20passed-success.svg)](phpunit.xml)

An artisanal fine-dining restaurant platform and reservation governance system built for **Terminal 1** (Cooch Behar, West Bengal). Combines editorial luxury aesthetics with secure, production-grade backend engineering—featuring real-time concierge streaming, dynamic slot availability, dual-sentiment guestbook ledger, robust IDOR safeguards, and cryptographic payment verification architecture.

---

## 🍽️ System Highlights & Core Architecture

### 1. Concierge Table Reservation Engine
- **Session-Preserving Booking Drafts:** Automatic client-side `sessionStorage` synchronization ensures guests never lose customized requests, party counts, or date preferences when encountering the authentication gate.
- **Dynamic Slot Availability Checking:** Real-time endpoint (`GET /bookings/slots?date=YYYY-MM-DD`) queries existing covers and disables overbooked sitting slots in the dropdown.
- **Accessible Party Size Controller:** Synchronized quick-select chips (`2`, `4`, `6`, `8+` covers) bound to keyboard-accessible visible numeric controls.
- **Guest Self-Service Cancellation:** Transparent customer cancellation policy with dedicated endpoint (`POST /bookings/cancel`) and custom modal confirmation.

### 2. Dual-Sentiment Guestbook Ledger
- **Transparent Public Ledger:** Guest reviews segmented into *Compliments & Praise* and *Critiques & Concerns* with real-time score calculation (`AVG(rating)`).
- **Executive Reply Loop:** System administrators can post official managerial responses directly beneath diner reviews.
- **Zero-Refresh Submissions:** Fully asynchronous AJAX form submissions with instantaneous optimistic card rendering.

### 3. Real-Time Concierge Tracking (Server-Sent Events)
- **Live Stream Architecture:** Persistent SSE streams (`/track/stream` and `/admin/track/stream`) notify staff and diners of table status transitions (Pending &rarr; Confirmed &rarr; Fulfilled &rarr; Cancelled) with 0ms polling overhead.
- **Tokenized URL Access:** Non-predictable cryptographic 64-character tracking tokens (`/track/{token}`) prevent unauthorized enumerations.

### 4. Enterprise Security & Access Governance
- **Strict IDOR & Parameter Tampering Guard:** Middleware verifies session ownership on all customer requests specifying `?id=..` or `?booking_id=..`. Attempts to access another diner's reservation immediately terminate with `HTTP 403 Forbidden` and trigger security audit logs.
- **RFC 5321 Socket Email Validation:** Deep domain verification checking MX records, SMTP socket mail exchange responsiveness, and blocking 100+ disposable temporary email providers.
- **Payment Engine Backend:** Secure server-to-server Razorpay order creation (`/payments/create-order`) and timing-safe `hash_equals()` HMAC-SHA256 signature verification (`/payments/verify`, `/payments/webhook`). Deposit UI is toggleable via `DEPOSIT_AMOUNT` environment flag.
- **Anti-CSRF & Bcrypt Encryption:** Strict token validation across all mutations, with passwords hashed via Bcrypt (Cost 12).

### 5. Luxury Editorial UI & Accessibility (WCAG AA Compliant)
- **Harmonious Typography:** Unified luxury pairing of serif (`Playfair Display`) and clean modern sans-serif (`Plus Jakarta Sans`).
- **Strict Color Contrast:** All text and accent tokens validated for WCAG AA compliance (&ge; 4.5:1 contrast ratio against cream and obsidian backgrounds).
- **Mobile First & Touch Optimized:** All buttons, filter tabs, and interactive chips meet Apple HIG / Material Design minimum 44px tap targets.
- **Asset Optimization:** Compressed image pipeline utilizing high-efficiency WebP assets (reduced bundle size by 85.7% from 3.2MB to ~456KB).

---

## 🛠️ Technology Stack

| Layer | Technologies |
|---|---|
| **Backend Core** | PHP 8.2+ (MVC Architecture, Pure Native Routing, PDO Prepared Statements) |
| **Database** | MySQL 8.0 / MariaDB (Strict Foreign Keys, Check Constraints, InnoDB) |
| **Frontend** | Vanilla ES6+ JavaScript, Semantic HTML5, Custom Modular CSS Design Tokens |
| **Real-time Protocol** | Server-Sent Events (SSE) with `text/event-stream` chunked encoding |
| **Security & Auth** | Custom Session Guard, Google OAuth 2.0, Bcrypt Hashing, HMAC-SHA256 |
| **Containerization** | Docker, Docker Compose, Nginx Reverse Proxy, Alpine Linux |
| **CI / Automation** | GitHub Actions (PHP Linting, Composer Validation, PHPUnit Suite) |

---

## 📂 Modular Architecture & Directory Structure

```
terminal1/
├── app/
│   ├── config/              # Database PDO connection, app environment configuration
│   ├── controllers/         # AdminController, AuthController, BookingController, PaymentController, ReviewController
│   ├── models/              # Booking, MenuItem, Review, User database models
│   └── views/
│       ├── admin/           # Dashboard, Bookings ledger, Users directory, Reviews manager, Menu editor
│       ├── auth/            # Login, Registration, OTP 6-box verification
│       ├── customer/        # My Bookings ledger, Single booking concierge tracker
│       ├── errors/          # Custom 404 Not Found & 500 Server Error luxury views
│       ├── layouts/         # Admin master sidebar, header, and confirmation modal layout
│       └── partials/        # Modular home sections: navbar, hero, experiences, menu, booking, reviews, footer
├── database/                # MySQL schemas (schema_mysql.sql) and historical migrations
├── public/                  # Public web document root (index.php, /css, /images, /uploads)
├── tests/                   # Automated PHPUnit tests, IDOR security tests, guest review suite
├── .env.example             # Documented environment variable template
├── docker-compose.yml       # Production-ready multi-container orchestration
└── README.md
```

---

## 🚀 Quickstart & Setup Guide

### Option 1: Local XAMPP Setup (Windows / macOS / Linux)

1. Clone repository to your web server document root:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/Subham675/Terminal1.git terminal1
   cd terminal1
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy environment configuration:
   ```bash
   cp .env.example .env
   ```
4. Import database schema into MySQL via phpMyAdmin or CLI:
   ```bash
   mysql -u root -p -e "CREATE DATABASE terminal1_db;"
   mysql -u root -p terminal1_db < database/schema_mysql.sql
   ```
5. Seed initial admin user:
   ```bash
   php tests/update_admin_passwords.php <YourSecurePassword> admin@terminal1.in
   ```
6. Open your browser: `http://localhost/terminal1/public/`

> For detailed XAMPP troubleshooting and Apache virtual host instructions, see [XAMPP_SETUP.md](XAMPP_SETUP.md).

---

### Option 2: Docker Compose (Fresh Machine Setup)

Spin up the entire stack (PHP-FPM, Nginx, MySQL 8.0) with a single command:
```bash
docker-compose up -d --build
```
Access the application at `http://localhost:8080`.

---

## 🧪 Automated Testing Suite

Terminal 1 maintains a thorough test suite covering unit logic, integration endpoints, and penetration security:

```bash
# 1. Run core PHPUnit test suite (helpers, sanitization, HMAC signature verification)
composer test

# 2. Run IDOR & URL tampering security assertions
php tests/test_idor_security.php

# 3. Run dual-sentiment guestbook reflection suite
php tests/test_reviews.php

# 4. Run RFC 5321 email validator & DNS MX tests
php tests/test_email_validator.php

# 5. Run reserve-a-table login authentication gate tests
php tests/test_reserve_login_gate.php
```

All 5 test suites pass with 100% green status.

---

## 💼 Recruiter Portfolio & Resume Summary

If you are reviewing this repository as part of a technical portfolio evaluation, here is the verified architectural summary:

- **Unified Product Identity:** *Terminal 1 — Fine Dining & Table Reservation Platform* (Cooch Behar, West Bengal).
- **Backend Payment Architecture:** Engineered end-to-end server-side Razorpay order generation and timing-safe `hash_equals()` HMAC-SHA256 signature verification for table deposits; structured to support instantaneous frontend activation by toggling `DEPOSIT_AMOUNT` in `.env`.
- **Security & Concurrency:** Eliminated double-booking race vulnerabilities by enforcing atomic submission locks; implemented server-enforced IDOR protection isolating customer reservation ledgers by verified session identity.
- **Performance & Asset Pipeline:** Re-architected monlithic views into reusable partials; compressed raster images into modern WebP format, achieving an **85.7% payload reduction** (3.2MB &rarr; 456KB) with zero degradation in visual fidelity.
- **Accessibility:** Overhauled interface to achieve strict **WCAG AA contrast compliance** (&ge; 4.5:1), universal `:focus-visible` keyboard rings, and minimum 44px touch targets.

---

## 📄 License
This project is licensed under the [MIT License](LICENSE).
