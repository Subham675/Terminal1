<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 — Kitchen Delay | Terminal 1</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-dark: #0A0908;
      --card-bg: #141311;
      --gold-accent: #C8860A;
      --gold-hover: #E8A820;
      --border-dark: rgba(255, 255, 255, 0.08);
      --text-light: #F5EFE6;
      --text-muted: rgba(245, 239, 230, 0.6);
      --font-serif: 'Playfair Display', Georgia, serif;
      --font-sans: 'Plus Jakarta Sans', system-ui, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg-dark);
      color: var(--text-light);
      font-family: var(--font-sans);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      text-align: center;
    }
    .error-card {
      background: var(--card-bg);
      border: 1px solid var(--border-dark);
      max-width: 520px;
      width: 100%;
      padding: 56px 36px;
      border-radius: 4px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
    .error-eyebrow {
      font-size: 0.76rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--gold-accent);
      margin-bottom: 12px;
      display: block;
      font-weight: 600;
    }
    .error-code {
      font-family: var(--font-serif);
      font-size: 4.8rem;
      line-height: 1;
      color: #FFFFFF;
      margin-bottom: 12px;
      font-weight: 400;
      letter-spacing: 2px;
    }
    .error-title {
      font-family: var(--font-serif);
      font-size: 1.5rem;
      color: #FFFFFF;
      margin-bottom: 14px;
      font-weight: 400;
    }
    .error-desc {
      font-size: 0.88rem;
      line-height: 1.6;
      color: var(--text-muted);
      margin-bottom: 32px;
    }
    .btn-return {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: #FFFFFF;
      color: #121110;
      padding: 13px 28px;
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 2px;
      transition: all .25s ease;
    }
    .btn-return:hover {
      background: var(--gold-hover);
      color: #121110;
      transform: translateY(-1px);
    }
    footer {
      margin-top: 36px;
      font-size: 0.75rem;
      color: rgba(255,255,255,0.3);
      letter-spacing: 0.05em;
    }
  </style>
</head>
<body>
  <div class="error-card">
    <span class="error-eyebrow">TERMINAL 1 &bull; UNEXPECTED INTERRUPTION</span>
    <div class="error-code">500</div>
    <h1 class="error-title">Our Brigade Encountered an Interruption</h1>
    <p class="error-desc">
      An unexpected internal condition occurred during this request. Our concierge and systems brigade have been alerted. Please try refreshing shortly.
    </p>
    <a href="<?= url('/') ?>" class="btn-return">
      &larr; Return to Dining Room
    </a>
  </div>
  <footer>
    &copy; <?= date('Y') ?> Terminal 1: The Restaurant. All rights reserved.
  </footer>
</body>
</html>
