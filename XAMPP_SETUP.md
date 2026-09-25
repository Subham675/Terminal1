# Running Terminal 1 on XAMPP (Apache + MySQL + PHP)

## 1. Prerequisites
- Install XAMPP: https://www.apachefriends.org/ (includes Apache, MySQL/MariaDB, PHP, phpMyAdmin)
- PHP 8.1+ (check via XAMPP Control Panel → Config → PHP version)

## 2. Place the project
Copy the `terminal1` folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\terminal1\        (Windows)
/Applications/XAMPP/htdocs/terminal1/   (Mac)
```

## 3. Start Apache + MySQL
Open the **XAMPP Control Panel** → click **Start** next to both **Apache** and **MySQL**.

## 4. Create the database
**Via phpMyAdmin (easiest):**
1. Open http://localhost/phpmyadmin
2. Click **New** (left sidebar)
3. Database name: `terminal1_db` → Create
4. Select `terminal1_db` → **Import** tab → Choose File → pick `database/schema_mysql.sql` → Go

**Or via command line:**
```bash
cd C:\xampp\mysql\bin
mysql -u root -e "CREATE DATABASE terminal1_db CHARACTER SET utf8mb4;"
mysql -u root terminal1_db < C:\xampp\htdocs\terminal1\database\schema_mysql.sql
```

## 5. Set up `.env`
```bash
cd C:\xampp\htdocs\terminal1
copy .env.example .env
```
Open `.env` and confirm these match your XAMPP setup (defaults usually work as-is):
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=terminal1_db
DB_USER=root
DB_PASS=
```
(XAMPP's default MySQL has **no root password** — leave `DB_PASS` blank, exactly as shown.)

Also generate a real app secret (used for session/CSRF security):
```bash
php -r "echo bin2hex(random_bytes(32));"
```
Paste the output into `.env` as `APP_SECRET`.

## 6. Install Composer dependencies (optional but recommended)
If you have Composer installed:
```bash
composer install
```
This enables PDF invoices (dompdf) and SMTP email with attachments (PHPMailer). Without it, the app still runs — those two features just degrade gracefully.

## 7. Open the site
```
http://localhost/terminal1/public/
```

**Tip:** If you want it at `http://localhost/terminal1/` (without `/public/`), either:
- Move everything from `public/` up one level, and update paths in `index.php` accordingly, **or**
- Set up a virtual host in XAMPP pointing its document root directly at the `public/` folder (cleaner, recommended for anything beyond quick local testing)

## 8. Create your admin account
Run the secure CLI setup script to create your first administrator:
```bash
php scripts/create_admin.php
```
Enter your desired admin name, email, and strong password. Then log in at `/auth/login`.


## 9. Real-time features — a note for XAMPP/Apache
The live booking-status tracker and admin live-notifications use Server-Sent
Events (SSE), implemented in plain PHP (`TrackingController.php`) — no extra
server or WebSocket library needed. Apache's default `mod_php` handles this
fine out of the box. If you notice the live updates feel delayed rather than
instant, check:
- `php.ini` → `output_buffering` should be `Off` (or leave as default —
  the app explicitly disables it at runtime as a safeguard)
- Any proxy/antivirus software intercepting local traffic can sometimes buffer
  streamed responses

## 10. Google Sign-In (Optional)
If you want to use "Sign in with Google":
1. Go to the [Google Cloud Console](https://console.cloud.google.com/) and create a project.
2. Navigate to **APIs & Services** → **Credentials** → **Create Credentials** → **OAuth client ID**.
3. Application Type: **Web application**.
4. Set Authorized redirect URIs:
   ```
   http://localhost/terminal1/public/auth/google/callback
   ```
5. Copy your **Client ID** and **Client Secret** into your `.env` file:
   ```
   GOOGLE_CLIENT_ID=your_actual_client_id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your_actual_client_secret
   GOOGLE_REDIRECT_URI=http://localhost/terminal1/public/auth/google/callback
   ```
*(If unconfigured, the app will safely notify you to use email/password instead of showing Google's 401 error).*

## Troubleshooting

**"The OAuth client was not found (Error 401: invalid_client)"** → Google Sign-In was clicked while `.env` still has the placeholder `YOUR_GOOGLE_CLIENT_ID...`. Enter your real Google Cloud OAuth credentials in `.env`, or sign in with your email/password.

**"could not find driver"** → PHP's `pdo_mysql` extension isn't enabled.
In `php.ini` (find via XAMPP Control Panel → Apache → Config → php.ini),
uncomment: `extension=pdo_mysql`. Restart Apache.

**"Access denied for user 'root'@'localhost'"** → Your XAMPP MySQL root
user has a password set (uncommon, but happens if you ran
`mysql_secure_installation` manually at some point). Put that password
in `.env` as `DB_PASS`.

**Blank white page** → Turn on error display temporarily. In `php.ini`:
`display_errors = On`, then check `C:\xampp\apache\logs\error.log` for
the real error.

