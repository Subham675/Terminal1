<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms of Dining | Terminal 1</title>
  <meta name="description" content="Review the dining terms, table reservations policy, and guest guidelines for Terminal 1 in Cooch Behar.">
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
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
      <h1>Terms &amp; Conditions</h1>
      <div class="meta">Effective Date: September 2026 | Terminal 1: The Restaurant, Cooch Behar</div>
    </header>

    <div class="highlight-box">
      <p style="margin:0;color:#FFFFFF;">Welcome to Terminal 1: The Restaurant. By accessing our website, creating an account, or submitting a reservation request, you agree to comply with and be bound by the following terms, conditions, and reservation guidelines.</p>
    </div>

    <h2>1. General Information &amp; Services</h2>
    <p>Terminal 1: The Restaurant operates a physical dining establishment located in Cooch Behar, West Bengal, India. Our online platform provides table reservation management, advance deposit collection, menu exploration, and live order tracking services for patrons.</p>

    <h2>2. Table Reservations &amp; Seating Policies</h2>
    <ul>
      <li>Reservations are subject to slot capacity and table availability at the restaurant. Submitting a request does not guarantee automatic confirmation until verified by restaurant management or confirmed via advance deposit payment.</li>
      <li><strong>Grace Period:</strong> Reserved tables will be held for up to 15 minutes past the scheduled arrival time. If the dining party has not arrived within 15 minutes, the reservation may be released to accommodate waiting walk-in guests.</li>
      <li>Guest counts should be accurate to ensure suitable table arrangements. Large party adjustments must be communicated in advance.</li>
    </ul>

    <h2>3. Advance Deposits &amp; Payments (Razorpay)</h2>
    <ul>
      <li>For special occasions, celebrations, or peak dining slots, an advance confirmation deposit (e.g. ₹100 per table) may be required to secure the reservation.</li>
      <li>All online payments are securely processed through Razorpay. By making a payment, you agree to Razorpay's terms of service and payment processing rules.</li>
      <li>Advance deposit amounts are adjusted directly against your final dining bill upon settlement at the restaurant.</li>
    </ul>

    <h2>4. Cancellation &amp; Refund Policy</h2>
    <ul>
      <li><strong>Customer Cancellation:</strong> You may cancel your reservation through your account dashboard up to 2 hours prior to the reserved slot.</li>
      <li><strong>Eligible Refunds:</strong> Cancellations made at least 2 hours before the scheduled dining time are eligible for a 100% refund of the advance deposit.</li>
      <li><strong>Processing Timeframe:</strong> Approved refunds are initiated via the Razorpay API and credited back to the original source payment method (bank account, card, or UPI) within 5 to 7 business days in accordance with standard banking procedures.</li>
      <li><strong>No-Shows &amp; Late Cancellations:</strong> Cancellations made less than 2 hours before the scheduled time or no-show occurrences are non-refundable to cover reserved table holding costs and kitchen prep.</li>
    </ul>

    <h2>5. Pricing &amp; Menu Availability</h2>
    <p>Prices and item availability displayed on our digital menu are indicative and subject to seasonal market supply and chef preparations. While we strive for daily accuracy, item pricing at the physical restaurant premises represents the final billing rate.</p>

    <h2>6. User Conduct &amp; Account Security</h2>
    <p>Users are responsible for maintaining the confidentiality of their account credentials and one-time verification passwords (OTP). Any automated scraping, unauthorized vulnerability testing, IDOR exploitation, or malicious tampering with URL parameters is strictly prohibited and subject to immediate account termination and legal reporting.</p>

    <h2>7. Governing Law &amp; Jurisdiction</h2>
    <p>These terms and conditions are governed by and construed in accordance with the laws of the Republic of India. Any disputes arising in connection with our services shall be subject to the exclusive jurisdiction of the competent courts in Cooch Behar, West Bengal.</p>

    <h2>8. Contact Information</h2>
    <p>For questions or reservation support, please contact:</p>
    <p>
      <strong>Terminal 1: The Restaurant</strong><br>
      Cooch Behar, West Bengal, India<br>
      Operating Hours: Monday – Sunday, 11:00 AM – 10:00 PM<br>
      Email: <a href="mailto:contact@terminal1.in" style="color:#C8860A;text-decoration:none;">contact@terminal1.in</a>
    </p>

    <footer class="footer">
      <div>&copy; <?= date('Y') ?> Terminal 1: The Restaurant. All rights reserved.</div>
      <div>
        <a href="<?= url('/privacy') ?>">Privacy Policy</a> &bull;
        <a href="<?= url('/') ?>">Home</a>
      </div>
    </footer>
  </div>
</body>
</html>
