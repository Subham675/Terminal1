# Terminal 1 — Full VPS Deployment Guide
## Ubuntu 22.04 LTS + Nginx + PHP 8.2 + PostgreSQL 15

---

## 1. Initial Server Setup

```bash
# Update system
apt update && apt upgrade -y

# Create app user
adduser terminal1
usermod -aG sudo terminal1

# SSH hardening — edit /etc/ssh/sshd_config
PermitRootLogin no
PasswordAuthentication no    # use SSH keys only
systemctl restart sshd

# Firewall
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

---

## 2. Install PHP 8.2 + Extensions

```bash
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2 php8.2-fpm php8.2-pgsql php8.2-curl \
  php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl

# Verify
php -v
```

---

## 3. Install PostgreSQL 15

```bash
apt install -y postgresql postgresql-contrib

# Create DB and user
sudo -u postgres psql << SQL
CREATE USER terminal1_user WITH PASSWORD 'STRONG_PASSWORD_HERE';
CREATE DATABASE terminal1_db OWNER terminal1_user;
GRANT ALL PRIVILEGES ON DATABASE terminal1_db TO terminal1_user;
SQL

# Run schema
sudo -u postgres psql -d terminal1_db < /var/www/terminal1/database/schema.sql
```

---

## 4. Install Nginx

```bash
apt install -y nginx
systemctl enable nginx

# Copy config
cp /var/www/terminal1/nginx.conf /etc/nginx/sites-available/terminal1
ln -s /etc/nginx/sites-available/terminal1 /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default

# Test and reload
nginx -t && systemctl reload nginx
```

---

## 5. Deploy Application

```bash
# Upload your project to /var/www/terminal1
# (via scp, rsync, or git clone)

# Set permissions
chown -R www-data:www-data /var/www/terminal1
chmod -R 755 /var/www/terminal1
chmod -R 775 /var/www/terminal1/storage

# Copy and configure .env
cp /var/www/terminal1/.env.example /var/www/terminal1/.env
nano /var/www/terminal1/.env
# Fill in: DB credentials, APP_SECRET, Google OAuth, SMTP details

# Place the index.html (restaurant frontend) inside:
mkdir -p /var/www/terminal1/public/site
cp /path/to/index.html /var/www/terminal1/public/site/
cp /path/to/*.png /var/www/terminal1/public/site/
```

---

## 6. Install PHPMailer (for SMTP OTP emails)

```bash
apt install -y composer
cd /var/www/terminal1
composer require phpmailer/phpmailer
```

---

## 7. SSL via Let's Encrypt

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
systemctl status snap.certbot.renew.timer
# or add cron:
crontab -e
# 0 12 * * * certbot renew --quiet
```

---

## 8. Google OAuth Setup

1. Go to https://console.cloud.google.com
2. Create new project → APIs & Services → Credentials
3. Create OAuth 2.0 Client ID (Web Application)
4. Add Authorized redirect URIs:
   `https://yourdomain.com/auth/google/callback`
5. Copy Client ID and Secret into `.env`

---

## 9. Fail2Ban (brute-force protection)

```bash
apt install -y fail2ban
systemctl enable fail2ban

# Protect SSH and Nginx
cat > /etc/fail2ban/jail.local << 'JAIL'
[DEFAULT]
bantime  = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true

[nginx-http-auth]
enabled = true
JAIL

systemctl restart fail2ban
```

---

## 10. PHP-FPM Tuning

```bash
# Edit /etc/php/8.2/fpm/php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
session.cookie_secure = 1
session.cookie_httponly = 1

systemctl restart php8.2-fpm
```

---

## 11. Admin Login

After deployment, log in at:  
`https://yourdomain.com/auth/login`

Default admin credentials (CHANGE IMMEDIATELY):
- Email: `admin@terminal1.in`
- Password: `Admin@1234`

---

## 12. Production Security Checklist

- [x] APP_ENV=production in .env
- [x] display_errors=Off in php.ini
- [x] .env blocked by Nginx config
- [x] CSRF tokens on all POST forms
- [x] PDO prepared statements (no SQL injection)
- [x] XSS prevention via e() on all output
- [x] Password hashing with password_hash()
- [x] Session regeneration on login
- [x] OTP expiry enforcement
- [x] Google OAuth state parameter validation
- [ ] Change default admin password immediately
- [ ] Set up database backups (pg_dump cron)
- [ ] Configure UFW firewall rules
- [ ] Set up Fail2Ban

---

## Folder Structure

```
/var/www/terminal1/
├── .env                    ← secret config (never commit!)
├── .htaccess
├── nginx.conf
├── app/
│   ├── config/
│   │   ├── config.php      ← env loader, helpers, session
│   │   ├── Database.php    ← PDO singleton
│   │   └── Mailer.php      ← SMTP / OTP email
│   ├── controllers/
│   │   ├── AuthController.php   ← login, register, Google OAuth, OTP
│   │   ├── AdminController.php  ← dashboard, bookings, menu, users
│   │   └── BookingController.php
│   ├── models/
│   │   ├── User.php
│   │   ├── OtpModel.php
│   │   ├── Booking.php
│   │   └── MenuItem.php
│   └── views/
│       ├── auth/            ← login, register, otp
│       ├── admin/           ← dashboard, bookings, menu, users
│       └── layouts/         ← admin sidebar layout
├── database/
│   └── schema.sql
├── public/
│   ├── index.php           ← front controller / router
│   └── site/               ← put index.html + images here
└── storage/logs/
```
