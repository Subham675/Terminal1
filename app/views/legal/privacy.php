<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privacy Policy | Terminal 1: The Restaurant</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&family=Playfair+Display:wght@600;700;900&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #0F0E0B;
      color: rgba(255, 255, 255, 0.85);
      font-family: 'DM Sans', system-ui, sans-serif;
      line-height: 1.7;
      padding: 40px 20px 80px;
    }
    .container {
      max-width: 820px;
      margin: 0 auto;
    }
    .header {
      border-bottom: 1px solid rgba(200, 134, 10, 0.25);
      padding-bottom: 24px;
      margin-bottom: 36px;
    }
    .brand {
      color: #E8A820;
      font-family: 'Playfair Display', serif;
      font-size: 1.4rem;
      font-weight: 900;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 12px;
      letter-spacing: 1px;
    }
    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2.2rem;
      color: #FFFFFF;
      margin-bottom: 8px;
    }
    .meta {
      font-size: 0.82rem;
      color: rgba(255, 255, 255, 0.45);
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    h2 {
      font-family: 'Playfair Display', serif;
      color: #E8A820;
      font-size: 1.3rem;
      margin: 32px 0 12px;
    }
    p, li {
      font-size: 0.95rem;
      color: rgba(255, 255, 255, 0.75);
      margin-bottom: 14px;
    }
    ul {
      margin-left: 20px;
      margin-bottom: 16px;
    }
    .highlight-box {
      background: rgba(200, 134, 10, 0.08);
      border-left: 3px solid #C8860A;
      padding: 16px 20px;
      border-radius: 2px;
      margin: 20px 0;
    }
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #C8860A;
      text-decoration: none;
      font-size: 0.85rem;
      letter-spacing: 1px;
      text-transform: uppercase;
      margin-bottom: 24px;
      transition: color 0.2s;
    }
    .back-link:hover {
      color: #E8A820;
    }
    .footer {
      margin-top: 60px;
      padding-top: 24px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.4);
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }
    .footer a {
      color: rgba(255, 255, 255, 0.6);
      text-decoration: none;
    }
    .footer a:hover {
      color: #C8860A;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="<?= url('/') ?>" class="back-link">&larr; Return to Terminal 1</a>
    
    <header class="header">
      <a href="<?= url('/') ?>" class="brand">TERMINAL 1</a>
      <h1>Privacy Policy</h1>
      <div class="meta">Effective Date: September 2026 | Terminal 1: The Restaurant, Cooch Behar</div>
    </header>

    <div class="highlight-box">
      <p style="margin:0;color:#FFFFFF;">Terminal 1: The Restaurant values your privacy. We collect only the information necessary to fulfill table reservations, process billing transactions, and deliver dining services at our premises in Cooch Behar, West Bengal.</p>
    </div>

    <h2>1. Information We Collect</h2>
    <p>When you register an account, reserve a table, or make a deposit on our website, we may collect the following details:</p>
    <ul>
      <li><strong>Personal Information:</strong> Full name, verified email address, and contact phone number.</li>
      <li><strong>Reservation Specifications:</strong> Date of booking, preferred time slot, number of guests, special occasion details, and dietary or seating requests.</li>
      <li><strong>Transaction Data:</strong> Payment transaction identifiers, deposit payment status, and timestamp records handled through our payment gateway partner (Razorpay). We do not store raw credit card, debit card, or UPI PIN credentials on our servers.</li>
      <li><strong>Security Logs:</strong> IP address and session authentication tokens utilized for rate-limiting, brute-force defense, and session security.</li>
    </ul>

    <h2>2. Purpose of Data Processing</h2>
    <p>We process your information exclusively for legitimate hospitality operations:</p>
    <ul>
      <li>Confirming table reservations and transmitting one-time verification passwords (OTP) and booking vouchers.</li>
      <li>Communicating reservation updates, confirmations, or unavoidable schedule modifications.</li>
      <li>Administering deposit receipts and processing authorized refunds via Razorpay in accordance with our cancellation terms.</li>
      <li>Preventing automated abuse, fraud, and duplicate reservation attempts.</li>
    </ul>

    <h2>3. Data Sharing & Third-Party Service Providers</h2>
    <p>We never sell, rent, or trade your personal information. Data is shared strictly with authorized operational vendors required to deliver our services:</p>
    <ul>
      <li><strong>Payment Processing:</strong> Razorpay Software Private Limited for secure payment gateway processing and transaction settlement.</li>
      <li><strong>Communication Infrastructure:</strong> Transactional SMTP infrastructure for reservation notifications and account security OTP delivery.</li>
      <li><strong>Legal Obligations:</strong> Compliance with competent law enforcement or regulatory authorities when mandated by applicable Indian law.</li>
    </ul>

    <h2>4. Data Storage & Security Measures</h2>
    <p>We implement industry-standard technical safeguards to protect your personal data:</p>
    <ul>
      <li>Passwords are hashed using modern one-way cryptographic algorithms (Bcrypt).</li>
      <li>All database interactions utilize parameterized PDO queries to eliminate SQL injection vulnerabilities.</li>
      <li>Cryptographic CSRF tokens and session-bound authorization checks safeguard customer accounts against unauthorized data retrieval.</li>
    </ul>

    <h2>5. Cookies & Session Management</h2>
    <p>Our website utilizes strictly necessary HTTP cookies for user authentication, CSRF protection, and session security. We do not employ third-party advertising trackers or behavioral profiling pixels.</p>

    <h2>6. Your Rights</h2>
    <p>You may request review, modification, or deletion of your account record and booking history by contacting our administration directly at our restaurant premises or via email.</p>

    <h2>7. Contact Information</h2>
    <p>For questions or requests regarding your data, please contact:</p>
    <p>
      <strong>Terminal 1: The Restaurant</strong><br>
      Cooch Behar, West Bengal, India<br>
      Email: <a href="mailto:contact@terminal1.in" style="color:#C8860A;text-decoration:none;">contact@terminal1.in</a>
    </p>

    <footer class="footer">
      <div>&copy; <?= date('Y') ?> Terminal 1: The Restaurant. All rights reserved.</div>
      <div>
        <a href="<?= url('/terms') ?>">Terms &amp; Conditions</a> &bull;
        <a href="<?= url('/') ?>">Home</a>
      </div>
    </footer>
  </div>
</body>
</html>
