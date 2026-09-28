<?php
// Load menu from DB grouped by category
$menuGrouped = [];
try {
    $menuGrouped = MenuItem::byCategory();
} catch(Exception $e) {
    // DB not yet connected — will show empty menu gracefully
}

// Load guestbook reviews and statistics
$reviewsList = [];
$reviewStats = ['total' => 0, 'compliments' => 0, 'complaints' => 0, 'avg_rating' => 5.0];
try {
    $reviewsList = Review::all(null, 'approved', 24);
    $reviewStats = Review::stats();
} catch(\Throwable $e) {}

// Flat list of all items for interactive cards
$allItems = [];
if (!empty($menuGrouped)) {
    foreach ($menuGrouped as $catName => $items) {
        foreach ($items as $it) {
            $it['cat_name'] = $catName;
            $allItems[] = $it;
        }
    }
}

// Map dish names to real photos in /images/
function getDishImage($name, $customUrl = null) {
    if (!empty($customUrl)) return $customUrl;
    $lower = strtolower($name);
    if (str_contains($lower, 'pasta') || str_contains($lower, 'spaghetti')) return asset('images/aveline_plate.png');
    if (str_contains($lower, 'dhonkami') || str_contains($lower, 'chicken')) return asset('images/dhonkami_chicken.jpg');
    if (str_contains($lower, 'starter') || str_contains($lower, 'platter') || str_contains($lower, 'tandoor') || str_contains($lower, 'kabab') || str_contains($lower, 'kebab')) return asset('images/starters_platter.jpg');
    if (str_contains($lower, 'jiaozi') || str_contains($lower, 'dumpling') || str_contains($lower, 'momo')) return asset('images/jiaozi_hero.jpg');
    if (str_contains($lower, 'bao') || str_contains($lower, 'dimsum') || str_contains($lower, 'dim sum')) return asset('images/bao_dimsum.jpg');
    if (str_contains($lower, 'polao') || str_contains($lower, 'biryani')) return asset('images/polao.jpg');
    if (str_contains($lower, 'fried rice') || str_contains($lower, 'rice')) return asset('images/fried_rice.jpg');
    if (str_contains($lower, 'noodle') || str_contains($lower, 'hakka') || str_contains($lower, 'chow')) return asset('images/noodles.jpg');
    return asset('images/aveline_plate.png');
}

$user = authUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Terminal 1 — A New Expression of Fine Dining | Cooch Behar</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>"/>
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    
    :root {
      --bg-dark: #0D0C0B;
      --bg-surface-dark: #151412;
      --bg-cream: #F8F6F2;
      --bg-cream-card: #FFFFFF;
      --bg-cream-subtle: #EFECE6;
      
      --text-dark: #141312;
      --text-dark-muted: #5C5852;
      --text-dark-dim: #8E8A83;
      
      --text-light: #F7F5F0;
      --text-light-muted: #A8A49C;
      --text-light-dim: #736E67;
      
      --gold-accent: #C8860A;
      --gold-hover: #A36B05;
      
      --border-light: rgba(0, 0, 0, 0.08);
      --border-dark: rgba(255, 255, 255, 0.1);
      
      --font-serif: 'Cormorant Garamond', Georgia, serif;
      --font-sans: 'Instrument Sans', -apple-system, sans-serif;
      --font-body: 'Plus Jakarta Sans', system-ui, sans-serif;
    }

    html {
      scroll-behavior: smooth;
      background: var(--bg-dark);
      color: var(--text-light);
    }
    
    body {
      font-family: var(--font-sans);
      background: var(--bg-dark);
      color: var(--text-light);
      max-width: 100%;
      overflow-x: clip;
      -webkit-font-smoothing: antialiased;
      line-height: 1.6;
    }

    /* ─── AVELINE FLOATING HEADER ─── */
    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 999;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 4%;
      background: rgba(13, 12, 11, 0.92);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border-dark);
      transition: all .25s ease;
    }

    .brand-logo {
      font-family: var(--font-serif);
      font-size: 1.35rem;
      font-weight: 500;
      letter-spacing: 0.16em;
      color: var(--text-light);
      text-decoration: none;
      text-transform: uppercase;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .nav-center-links {
      display: flex;
      align-items: center;
      gap: 28px;
      list-style: none;
    }
    .nav-center-links a {
      font-family: var(--font-sans);
      font-size: 0.74rem;
      font-weight: 500;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--text-light-muted);
      text-decoration: none;
      transition: color .2s ease;
      position: relative;
      white-space: nowrap;
    }
    .nav-center-links a:hover {
      color: #fff;
    }
    .nav-center-links a::after {
      content: '';
      position: absolute;
      bottom: -4px; left: 0; width: 0; height: 1px;
      background: #fff;
      transition: width .2s ease;
    }
    .nav-center-links a:hover::after {
      width: 100%;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-shrink: 0;
    }
    .btn-nav-auth {
      font-family: var(--font-sans);
      font-size: 0.74rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text-light-muted);
      text-decoration: none;
      transition: color .2s ease;
      white-space: nowrap;
    }
    .btn-nav-auth:hover {
      color: #fff;
    }
    .btn-aveline-cta {
      background: #FFFFFF;
      color: #121110;
      border: 1px solid #FFFFFF;
      padding: 9px 20px;
      font-family: var(--font-sans);
      font-size: 0.74rem;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 2px;
      transition: all .25s ease;
      white-space: nowrap;
      display: inline-block;
    }
    .btn-aveline-cta:hover {
      background: transparent;
      color: #FFFFFF;
    }

    /* ─── AVELINE ICONIC SPLIT HERO SECTION ─── */
    .aveline-hero {
      position: relative;
      min-height: calc(100vh - 65px);
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      padding-top: 65px;
      background: var(--bg-dark);
      overflow: hidden;
    }

    /* Left Parchment Panel */
    .hero-parchment-panel {
      background: var(--bg-cream);
      color: var(--text-dark);
      padding: 50px 7% 50px 5%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      z-index: 2;
      clip-path: polygon(0 0, 100% 0, 88% 100%, 0 100%);
    }

    .parchment-top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    .parchment-brand {
      font-family: var(--font-serif);
      font-size: 1.3rem;
      letter-spacing: 0.18em;
      color: var(--text-dark);
      font-weight: 500;
      text-transform: uppercase;
    }
    .parchment-meta {
      font-size: 0.72rem;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--text-dark-muted);
      font-weight: 500;
    }

    .hero-intro-text {
      max-width: 380px;
      font-family: var(--font-sans);
      font-size: 0.92rem;
      line-height: 1.65;
      color: var(--text-dark-muted);
      font-weight: 400;
      margin-bottom: 36px;
    }

    .hero-monument-title {
      max-width: 440px;
      font-family: var(--font-serif);
      font-size: clamp(2.6rem, 4.6vw, 4.2rem);
      font-weight: 400;
      line-height: 0.96;
      letter-spacing: -0.02em;
      color: var(--text-dark);
      text-transform: uppercase;
      margin-bottom: 26px;
    }
    .hero-monument-title span {
      display: block;
    }

    .hero-button-row {
      max-width: 440px;
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .btn-parchment-dark {
      background: #141312;
      color: #FFFFFF;
      border: 1px solid #141312;
      padding: 12px 26px;
      font-family: var(--font-sans);
      font-size: 0.74rem;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 2px;
      transition: all .2s ease;
      white-space: nowrap;
    }
    .btn-parchment-dark:hover {
      background: transparent;
      color: #141312;
    }
    .btn-parchment-outline {
      background: transparent;
      color: #141312;
      border: 1px solid rgba(20, 19, 18, 0.25);
      padding: 12px 24px;
      font-family: var(--font-sans);
      font-size: 0.74rem;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 2px;
      transition: all .2s ease;
      white-space: nowrap;
    }
    .btn-parchment-outline:hover {
      border-color: #141312;
    }

    /* Right Ambient Scene */
    .hero-ambiance-panel {
      position: relative;
      height: 100%;
      min-height: 520px;
      background: #000;
      overflow: hidden;
    }
    .hero-ambiance-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      filter: brightness(0.85);
      transition: transform 8s ease;
    }
    .aveline-hero:hover .hero-ambiance-img {
      transform: scale(1.04);
    }
    .ambiance-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to right, rgba(13, 12, 11, 0.4) 0%, transparent 60%);
    }

    /* Left-Biased Overlapping Circular Signature Plate */
    .hero-overlapping-plate {
      position: absolute;
      top: 58%;
      left: 45%;
      transform: translate(-50%, -50%);
      width: clamp(280px, 28vw, 420px);
      aspect-ratio: 1/1;
      z-index: 10;
      pointer-events: auto;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .hero-overlapping-plate img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 25px 40px rgba(0, 0, 0, 0.5));
      border-radius: 50%;
      cursor: pointer;
      display: block;
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s ease;
      animation: gentleFloatPlate 6s ease-in-out infinite;
    }
    .hero-overlapping-plate:hover img {
      transform: scale(1.03) rotate(-6deg);
      filter: drop-shadow(0 30px 48px rgba(0, 0, 0, 0.65));
    }
    @keyframes gentleFloatPlate {
      0%, 100% { transform: translateY(0px) rotate(-8deg); }
      50% { transform: translateY(-10px) rotate(-5deg); }
    }

    /* ─── AVELINE SECTION COMMONS ─── */
    .aveline-section {
      padding: 90px 6%;
      position: relative;
    }
    .section-eyebrow {
      font-family: var(--font-sans);
      font-size: 0.72rem;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--gold-accent);
      margin-bottom: 12px;
      display: block;
      font-weight: 500;
    }
    .section-serif-title {
      font-family: var(--font-serif);
      font-size: clamp(2.2rem, 3.8vw, 3.4rem);
      font-weight: 400;
      line-height: 1.05;
      color: var(--text-light);
      letter-spacing: -0.01em;
    }
    .section-serif-title.dark {
      color: var(--text-dark);
    }
    .section-desc {
      color: var(--text-light-muted);
      font-size: 0.94rem;
      max-width: 580px;
      line-height: 1.65;
      font-weight: 300;
      margin-top: 10px;
    }

    /* ─── EXPERIENCES BEYOND THE TABLE ─── */
    .experiences-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
      margin-top: 48px;
    }
    .experience-card {
      background: var(--bg-surface-dark);
      border: 1px solid var(--border-dark);
      padding: 36px 30px;
      border-radius: 2px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 340px;
      transition: border-color .25s ease, transform .25s ease;
    }
    .experience-card:hover {
      border-color: rgba(255, 255, 255, 0.28);
      transform: translateY(-3px);
    }
    .exp-num {
      font-family: var(--font-serif);
      font-size: 1.1rem;
      color: var(--text-light-dim);
      font-style: italic;
      margin-bottom: 24px;
    }
    .exp-title {
      font-family: var(--font-serif);
      font-size: 1.7rem;
      font-weight: 400;
      color: #FFFFFF;
      line-height: 1.15;
      margin-bottom: 12px;
      letter-spacing: 0.02em;
    }
    .exp-desc {
      color: var(--text-light-muted);
      font-size: 0.86rem;
      line-height: 1.6;
      font-weight: 300;
      margin-bottom: 28px;
    }
    .exp-footer {
      border-top: 1px solid var(--border-dark);
      padding-top: 18px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .exp-meta {
      font-size: 0.72rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text-light-dim);
    }
    .exp-cta-link {
      color: #FFFFFF;
      font-size: 0.75rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      text-decoration: none;
      font-weight: 500;
    }
    .exp-cta-link:hover {
      color: var(--gold-accent);
    }

    /* ─── SIGNATURE MENU & THE TASTE ─── */
    .menu-cream-wrap {
      background: var(--bg-cream);
      color: var(--text-dark);
    }
    .menu-category-filter {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-top: 36px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }
    .menu-cat-btn {
      background: transparent;
      border: 1px solid rgba(20, 19, 18, 0.18);
      color: var(--text-dark-muted);
      padding: 9px 20px;
      font-family: var(--font-sans);
      font-size: 0.74rem;
      font-weight: 500;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      transition: all .2s ease;
    }
    .menu-cat-btn:hover {
      border-color: #141312;
      color: #141312;
    }
    .menu-cat-btn.active {
      background: #141312;
      border-color: #141312;
      color: #FFFFFF;
    }

    .aveline-dish-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
      gap: 36px 30px;
    }
    .aveline-dish-card {
      background: var(--bg-cream-card);
      border: 1px solid rgba(0, 0, 0, 0.06);
      border-radius: 2px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
      transition: transform .25s ease, box-shadow .25s ease;
    }
    .aveline-dish-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    }
    .dish-photo-frame {
      position: relative;
      width: 100%;
      height: 220px;
      background: #141312;
      overflow: hidden;
    }
    .dish-photo-frame img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .4s ease;
    }
    .aveline-dish-card:hover .dish-photo-frame img {
      transform: scale(1.05);
    }
    .dish-diet-pill {
      position: absolute;
      top: 14px; right: 14px;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      padding: 4px 10px;
      font-size: 0.66rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      font-weight: 600;
      color: #141312;
      border-radius: 2px;
    }

    .dish-info-box {
      padding: 24px 22px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    .dish-title-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 12px;
      margin-bottom: 10px;
    }
    .dish-item-name {
      font-family: var(--font-serif);
      font-size: 1.35rem;
      font-weight: 500;
      color: var(--text-dark);
      line-height: 1.2;
    }
    .dish-item-price {
      font-family: var(--font-serif);
      font-size: 1.25rem;
      color: var(--gold-accent);
      font-weight: 600;
      white-space: nowrap;
    }
    .dish-ingredients-text {
      color: var(--text-dark-muted);
      font-size: 0.84rem;
      line-height: 1.55;
      font-weight: 400;
      margin-bottom: 20px;
      flex: 1;
    }
    .dish-card-footer {
      border-top: 1px solid rgba(0, 0, 0, 0.06);
      padding-top: 14px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-dish-inspect {
      background: transparent;
      border: none;
      color: var(--text-dark-dim);
      font-family: var(--font-sans);
      font-size: 0.72rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      font-weight: 600;
      cursor: pointer;
      transition: color .2s ease;
    }
    .btn-dish-inspect:hover {
      color: var(--text-dark);
    }
    .btn-dish-reserve {
      color: var(--text-dark);
      font-family: var(--font-sans);
      font-size: 0.72rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      text-decoration: none;
      font-weight: 600;
      border-bottom: 1px solid var(--text-dark);
      padding-bottom: 1px;
    }

    /* ─── PHILOSOPHY SPREAD ─── */
    .philosophy-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 50px;
      align-items: center;
    }
    .philosophy-quote {
      font-family: var(--font-serif);
      font-size: clamp(1.8rem, 3.2vw, 2.8rem);
      font-weight: 400;
      font-style: italic;
      line-height: 1.25;
      color: #FFFFFF;
      position: relative;
    }
    .philosophy-body {
      display: flex;
      flex-direction: column;
      gap: 16px;
      color: var(--text-light-muted);
      font-size: 0.92rem;
      line-height: 1.7;
      font-weight: 300;
    }

    /* ─── AVELINE RESERVATION SECTION ("BOOK YOUR EXPERIENCE") ─── */
    .reservation-section {
      background: var(--bg-surface-dark);
      border-top: 1px solid var(--border-dark);
      border-bottom: 1px solid var(--border-dark);
    }
    .reservation-container {
      max-width: 980px;
      margin: 0 auto;
    }
    .reservation-header {
      text-align: center;
      margin-bottom: 48px;
    }
    .reservation-header .section-serif-title {
      font-size: clamp(2.4rem, 4.5vw, 3.6rem);
      margin-bottom: 12px;
    }

    /* Party Size Selector */
    .party-chips-label {
      display: block;
      font-size: 0.72rem;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: var(--text-light-dim);
      margin-bottom: 12px;
      text-align: center;
    }
    .party-chips-row {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-bottom: 32px;
      flex-wrap: wrap;
    }
    .party-chip-btn {
      background: transparent;
      border: 1px solid var(--border-dark);
      color: var(--text-light-muted);
      padding: 10px 22px;
      font-family: var(--font-sans);
      font-size: 0.75rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      transition: all .2s ease;
    }
    .party-chip-btn:hover {
      border-color: #FFFFFF;
      color: #FFFFFF;
    }
    .party-chip-btn.active {
      background: #FFFFFF;
      border-color: #FFFFFF;
      color: #121110;
      font-weight: 600;
    }

    /* Form Fields */
    .res-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }
    .res-field {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .res-field label {
      font-family: var(--font-sans);
      font-size: 0.7rem;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--text-light-dim);
      font-weight: 500;
    }
    .res-field input, .res-field select, .res-field textarea {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-dark);
      color: #FFFFFF;
      padding: 12px 16px;
      font-family: var(--font-sans);
      font-size: 0.88rem;
      border-radius: 2px;
      outline: none;
      transition: border-color .2s ease;
    }
    .res-field input:focus, .res-field select:focus, .res-field textarea:focus {
      border-color: #FFFFFF;
    }
    .res-field select option {
      background: #141312;
      color: #FFFFFF;
    }

    .btn-submit-aveline {
      width: 100%;
      background: #FFFFFF;
      color: #121110;
      border: none;
      padding: 15px;
      font-family: var(--font-sans);
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      transition: all .25s ease;
      margin-top: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    .btn-submit-aveline:hover:not(:disabled) {
      background: var(--bg-cream-subtle);
      transform: translateY(-1px);
    }
    .btn-submit-aveline:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Guest Gate Card — STRICT COMPLIANCE WITH TEST SUITE */
    .aveline-guest-gate {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid var(--border-dark);
      padding: 36px 30px;
      text-align: center;
      border-radius: 2px;
      max-width: 600px;
      margin: 0 auto;
    }
    .aveline-guest-gate h4 {
      font-family: var(--font-serif);
      font-size: 1.6rem;
      font-weight: 400;
      color: #FFFFFF;
      margin-bottom: 8px;
    }
    .aveline-guest-gate p {
      color: var(--text-light-muted);
      font-size: 0.88rem;
      line-height: 1.6;
      margin-bottom: 24px;
    }
    .btn-gate-signin {
      display: inline-block;
      background: #FFFFFF;
      color: #121110;
      padding: 13px 32px;
      font-family: var(--font-sans);
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 2px;
      transition: all .2s ease;
    }
    .btn-gate-signin:hover {
      background: var(--bg-cream-subtle);
    }

    /* ─── MODAL SPECIFICATIONS ─── */
    .aveline-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(10px);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .aveline-modal-overlay.active {
      display: flex;
    }
    .aveline-modal-card {
      background: #181715;
      border: 1px solid var(--border-dark);
      max-width: 640px;
      width: 100%;
      border-radius: 2px;
      overflow: hidden;
      position: relative;
    }
    .aveline-modal-close {
      position: absolute;
      top: 16px; right: 16px;
      background: rgba(0, 0, 0, 0.5);
      border: 1px solid var(--border-dark);
      color: #FFFFFF;
      width: 32px; height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 10;
      font-size: 1rem;
    }
    .modal-grid {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
    }
    .modal-photo {
      height: 100%;
      min-height: 280px;
      background: #000;
    }
    .modal-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .modal-details {
      padding: 28px 24px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    /* ─── EDITORIAL FOOTER ─── */
    footer {
      background: #0A0908;
      border-top: 1px solid var(--border-dark);
      padding: 70px 6% 36px;
    }
    .footer-columns-grid {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1fr;
      gap: 40px;
      margin-bottom: 50px;
    }
    .footer-col h5 {
      font-family: var(--font-sans);
      font-size: 0.72rem;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: var(--text-light);
      margin-bottom: 18px;
      font-weight: 600;
    }
    .footer-col p {
      color: var(--text-light-muted);
      font-size: 0.86rem;
      line-height: 1.65;
    }
    .footer-nav-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .footer-nav-list a {
      color: var(--text-light-muted);
      text-decoration: none;
      font-size: 0.84rem;
      transition: color .2s ease;
    }
    .footer-nav-list a:hover {
      color: #FFFFFF;
    }
    .footer-bottom-strip {
      border-top: 1px solid var(--border-dark);
      padding-top: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.72rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--text-light-dim);
      flex-wrap: wrap;
      gap: 12px;
    }

    /* Toast */
    #aveline-toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: #FFFFFF;
      color: #121110;
      padding: 12px 24px;
      font-size: 0.8rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      font-weight: 600;
      border-radius: 2px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
      z-index: 9999;
      opacity: 0;
      transform: translateY(20px);
      transition: all .25s ease;
      pointer-events: none;
    }
    #aveline-toast.show {
      opacity: 1;
      transform: translateY(0);
    }

    /* ─── AVELINE GUESTBOOK & REFLECTIONS (COMPLIMENTS & COMPLAINTS) ─── */
    .guestbook-section {
      background: #0E0D0B;
      border-top: 1px solid var(--border-dark);
      border-bottom: 1px solid var(--border-dark);
      position: relative;
    }
    .guestbook-header-wrap {
      max-width: 900px;
      margin-bottom: 40px;
    }
    .guestbook-summary-bar {
      margin-top: 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
      background: #141310;
      border: 1px solid var(--border-dark);
      padding: 18px 26px;
      border-radius: 2px;
    }
    .guestbook-rating-badge {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .rating-stars-large {
      color: var(--gold-accent);
      font-size: 1.25rem;
      letter-spacing: 2px;
    }
    .rating-score {
      font-size: 0.88rem;
      color: var(--text-light-muted);
    }
    .rating-score strong {
      font-size: 1.18rem;
      color: #FFFFFF;
      font-family: var(--font-serif);
      font-weight: 600;
    }
    .guestbook-counts-group {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .count-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.8rem;
      color: var(--text-light-muted);
      padding: 6px 14px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.07);
    }
    .count-pill.pill-compliments .pill-number {
      color: #5eead4;
      font-weight: 700;
    }
    .count-pill.pill-complaints .pill-number {
      color: #fbbf24;
      font-weight: 700;
    }
    .pill-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
    }
    .pill-dot.green {
      background: #34d399;
      box-shadow: 0 0 6px #34d399;
    }
    .pill-dot.amber {
      background: #f59e0b;
      box-shadow: 0 0 6px #f59e0b;
    }
    .btn-write-review {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--gold-accent);
      color: #121110;
      border: 1px solid var(--gold-accent);
      padding: 10px 22px;
      font-family: var(--font-sans);
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      transition: all .2s ease;
    }
    .btn-write-review:hover {
      background: transparent;
      color: var(--gold-accent);
    }
    .guestbook-filter-tabs {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 24px;
      flex-wrap: wrap;
    }
    .gb-tab-btn {
      background: transparent;
      border: 1px solid var(--border-dark);
      color: var(--text-light-muted);
      padding: 8px 18px;
      font-family: var(--font-sans);
      font-size: 0.74rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      transition: all .2s ease;
    }
    .gb-tab-btn:hover {
      color: #FFFFFF;
      border-color: rgba(255, 255, 255, 0.3);
    }
    .gb-tab-btn.active {
      background: rgba(200, 134, 10, 0.12);
      border-color: var(--gold-accent);
      color: var(--gold-accent);
      font-weight: 600;
    }

    /* Guestbook Cards Grid */
    .guestbook-cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 24px;
      margin-top: 36px;
    }
    .guestbook-card {
      background: #141310;
      border: 1px solid rgba(255, 255, 255, 0.07);
      padding: 30px 26px;
      border-radius: 2px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      transition: all .25s ease;
    }
    .guestbook-card:hover {
      border-color: rgba(200, 134, 10, 0.35);
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4);
    }
    .guestbook-card.is-compliment {
      border-left: 2px solid #5eead4;
    }
    .guestbook-card.is-complaint {
      border-left: 2px solid #f59e0b;
    }
    .gb-card-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }
    .gb-type-tag {
      font-size: 0.68rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      font-weight: 600;
      padding: 3px 9px;
      border-radius: 2px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    .tag-compliment {
      background: rgba(45, 212, 191, 0.12);
      color: #5eead4;
      border: 1px solid rgba(45, 212, 191, 0.25);
    }
    .tag-complaint {
      background: rgba(245, 158, 11, 0.12);
      color: #f59e0b;
      border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .gb-card-stars {
      color: var(--gold-accent);
      font-size: 0.88rem;
      letter-spacing: 2px;
    }
    .gb-card-title {
      font-family: var(--font-serif);
      font-size: 1.25rem;
      color: #FFFFFF;
      font-weight: 500;
      line-height: 1.2;
      margin-bottom: 10px;
      letter-spacing: 0.01em;
    }
    .gb-card-quote {
      color: var(--text-light-muted);
      font-size: 0.88rem;
      line-height: 1.65;
      font-weight: 300;
      flex: 1;
      margin-bottom: 20px;
    }
    .gb-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
      padding-top: 14px;
      margin-top: auto;
    }
    .gb-author-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .gb-author-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: rgba(200, 134, 10, 0.15);
      border: 1px solid var(--border-gold);
      color: var(--gold-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.76rem;
      font-weight: 700;
    }
    .gb-author-name {
      font-size: 0.82rem;
      font-weight: 600;
      color: #FFFFFF;
      display: block;
    }
    .gb-meta-date {
      font-size: 0.72rem;
      color: var(--text-light-dim);
    }
    .gb-verified-badge {
      font-size: 0.65rem;
      color: var(--gold-accent);
      letter-spacing: 0.06em;
      text-transform: uppercase;
      font-weight: 600;
    }

    /* Management Response in card */
    .gb-admin-reply {
      margin-top: 14px;
      background: rgba(200, 134, 10, 0.05);
      border-left: 2px solid var(--gold-accent);
      padding: 10px 14px;
      border-radius: 0 2px 2px 0;
    }
    .reply-header {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
    }
    .reply-crest {
      font-size: 0.65rem;
      font-weight: 800;
      color: var(--gold-accent);
      border: 1px solid var(--border-gold);
      padding: 1px 4px;
      border-radius: 2px;
      letter-spacing: 0.5px;
    }
    .reply-header strong {
      font-size: 0.72rem;
      color: var(--gold-accent);
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .reply-text {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.82);
      line-height: 1.45;
      font-style: italic;
    }

    /* Review Modal Styling */
    #reviewModal {
      position: fixed;
      inset: 0;
      background: rgba(13, 12, 11, 0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      z-index: 10000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    #reviewModal.active {
      display: flex;
    }
    .review-modal-box {
      background: #171511;
      border: 1px solid rgba(200, 134, 10, 0.3);
      width: 100%;
      max-width: 580px;
      max-height: 92vh;
      overflow-y: auto;
      border-radius: 4px;
      padding: 36px 32px;
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8);
      position: relative;
    }
    .review-modal-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: none;
      border: none;
      color: var(--text-light-muted);
      font-size: 1.6rem;
      cursor: pointer;
      line-height: 1;
      transition: color .2s;
    }
    .review-modal-close:hover {
      color: #FFFFFF;
    }
    .type-switcher-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 22px;
    }
    .type-switch-btn {
      background: #12100C;
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-light-muted);
      padding: 12px;
      border-radius: 2px;
      font-family: var(--font-sans);
      font-size: 0.74rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      transition: all .2s ease;
    }
    .type-switch-btn:hover {
      border-color: rgba(255, 255, 255, 0.3);
      color: #fff;
    }
    .type-switch-btn.active-compliment {
      background: rgba(45, 212, 191, 0.12);
      border-color: #2dd4bf;
      color: #5eead4;
      box-shadow: 0 0 16px rgba(45, 212, 191, 0.15);
    }
    .type-switch-btn.active-complaint {
      background: rgba(245, 158, 11, 0.12);
      border-color: #f59e0b;
      color: #fbbf24;
      box-shadow: 0 0 16px rgba(245, 158, 11, 0.15);
    }
    .star-rating-picker {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 1.5rem;
      color: rgba(255, 255, 255, 0.2);
      cursor: pointer;
    }
    .star-rating-picker .star-item {
      transition: color .15s;
    }
    .star-rating-picker .star-item.selected,
    .star-rating-picker .star-item.hovered {
      color: var(--gold-accent);
    }
    .rating-label-hint {
      font-size: 0.74rem;
      color: var(--gold-accent);
      margin-left: 10px;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    /* ─── RESPONSIVE BREAKPOINTS ─── */
    @media (max-width: 1024px) {
      .aveline-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 65px;
        background: var(--bg-dark);
        min-height: auto;
      }
      .hero-parchment-panel {
        order: 1;
        clip-path: none;
        padding: 36px 6% 28px;
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 22px;
      }
      .hero-intro-text, .hero-monument-title, .hero-button-row {
        max-width: 100%;
      }
      .hero-overlapping-plate {
        order: 2;
        position: relative;
        top: auto;
        left: 0;
        right: auto;
        bottom: auto;
        transform: none;
        align-self: flex-start;
        margin: 28px 0 -115px 5%;
        width: clamp(220px, 58vw, 300px);
        z-index: 25;
        display: flex;
        justify-content: flex-start;
        align-items: center;
      }
      .hero-overlapping-plate:hover {
        transform: translateY(-4px) scale(1.02);
      }
      .hero-overlapping-plate img {
        width: 100%;
        height: 100%;
        max-width: 100%;
        filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.6));
        animation: gentleFloatMobile 5s ease-in-out infinite;
      }
      @keyframes gentleFloatMobile {
        0%, 100% { transform: translateY(0px) rotate(-8deg); }
        50% { transform: translateY(-8px) rotate(-5deg); }
      }
      .hero-ambiance-panel {
        order: 3;
        width: 100%;
        height: 360px;
        min-height: 360px;
        position: relative;
      }
      .experiences-grid {
        grid-template-columns: 1fr;
      }
      .philosophy-grid {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      .footer-columns-grid {
        grid-template-columns: 1fr 1fr;
      }
      .guestbook-cards-grid {
        grid-template-columns: 1fr;
      }
      .guestbook-summary-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
      }
    }

    @media (max-width: 768px) {
      nav {
        padding: 12px 4%;
      }
      .brand-logo {
        font-size: 1.2rem;
        letter-spacing: 0.14em;
      }
      .nav-center-links {
        display: none;
      }
      .nav-actions {
        gap: 12px;
      }
      .btn-nav-auth {
        font-size: 0.72rem;
      }
      .btn-aveline-cta {
        padding: 8px 16px;
        font-size: 0.7rem;
      }
      .hero-monument-title {
        font-size: clamp(2.3rem, 10vw, 3.6rem);
        line-height: 1.0;
        margin-bottom: 22px;
      }
      .hero-intro-text {
        font-size: 0.88rem;
        margin-bottom: 24px;
      }
      .res-form-grid {
        grid-template-columns: 1fr;
      }
      .footer-columns-grid {
        grid-template-columns: 1fr;
        gap: 28px;
      }
      .modal-grid {
        grid-template-columns: 1fr;
      }
      .modal-photo {
        height: 200px;
        min-height: auto;
      }
    }

    @media (max-width: 480px) {
      nav {
        padding: 10px 3.5%;
      }
      .brand-logo {
        font-size: 1.05rem;
        letter-spacing: 0.1em;
      }
      .nav-actions {
        gap: 8px;
      }
      .btn-nav-auth {
        font-size: 0.66rem;
      }
      .btn-aveline-cta {
        padding: 6px 12px;
        font-size: 0.66rem;
        letter-spacing: 0.08em;
      }
      .hero-parchment-panel {
        padding: 24px 5% 20px;
      }
      .parchment-top-bar {
        margin-bottom: 16px;
      }
      .hero-intro-text {
        font-size: 0.84rem;
        line-height: 1.55;
        margin-bottom: 18px;
      }
      .hero-monument-title {
        font-size: clamp(2.0rem, 9.2vw, 2.6rem);
        line-height: 1.02;
        margin-bottom: 18px;
      }
      .hero-button-row {
        display: flex;
        width: 100%;
        gap: 8px;
      }
      .btn-parchment-dark, .btn-parchment-outline {
        flex: 1;
        text-align: center;
        padding: 11px 8px;
        font-size: 0.68rem;
        letter-spacing: 0.06em;
      }
      .hero-overlapping-plate {
        align-self: flex-start;
        width: clamp(190px, 60vw, 240px);
        margin: 24px 0 -100px 5%;
      }
      .hero-ambiance-panel {
        height: 290px;
        min-height: 290px;
      }
      .party-chips-row {
        gap: 8px;
      }
      .party-chip-btn {
        padding: 8px 12px;
        font-size: 0.68rem;
      }
      .type-switcher-grid {
        grid-template-columns: 1fr;
      }
      .guestbook-filter-tabs {
        flex-direction: column;
        align-items: stretch;
      }
      .gb-tab-btn {
        text-align: center;
      }
      .review-modal-box {
        padding: 24px 18px;
      }
    }
  </style>
</head>
<body>

<!-- Navigation Header -->
<nav>
  <a href="<?= url('/') ?>" class="brand-logo">TERMINAL 1</a>

  <ul class="nav-center-links">
    <li><a href="#about">About</a></li>
    <li><a href="#experiences">Experiences</a></li>
    <li><a href="#menu">Menu</a></li>
    <li><a href="#philosophy">Philosophy</a></li>
    <li><a href="#reviews">Reviews</a></li>
    <li><a href="#contact">Reservations</a></li>
  </ul>

  <div class="nav-actions">
    <?php if($user): ?>
      <a href="<?= url('/my-bookings') ?>" class="btn-nav-auth"><?= e(explode(' ', $user['name'])[0]) ?></a>
      <?php if(($user['role'] ?? '') === 'admin'): ?>
        <a href="<?= url('/admin/dashboard') ?>" class="btn-nav-auth" style="color:var(--gold-accent);">[Admin]</a>
      <?php endif; ?>
      <a href="<?= url('/auth/logout') ?>" class="btn-nav-auth" style="opacity:0.6;">Logout</a>
    <?php else: ?>
      <a href="<?= url('/auth/login') ?>" class="btn-nav-auth">Sign In</a>
    <?php endif; ?>
    <a href="#contact" class="btn-aveline-cta">BOOK A TABLE</a>
  </div>
</nav>

<!-- AVELINE ICONIC SPLIT HERO SECTION -->
<section class="aveline-hero" id="about">
  <!-- Left Parchment Panel -->
  <div class="hero-parchment-panel">
    <div>
      <div class="parchment-top-bar">
        <div class="parchment-brand">TERMINAL 1</div>
        <div class="parchment-meta">COOCH BEHAR &bull; EST. 2024</div>
      </div>

      <p class="hero-intro-text">
        An exquisite journey of flavor, where refined ingredients, thoughtful technique, and timeless elegance come together in perfect harmony.
      </p>
    </div>

    <div>
      <h1 class="hero-monument-title">
        <span>A NEW</span>
        <span>EXPRESSION</span>
        <span>OF FINE DINING</span>
      </h1>

      <div class="hero-button-row">
        <a href="#menu" class="btn-parchment-dark">EXPLORE ALL MENU</a>
        <a href="#contact" class="btn-parchment-outline">BOOK A TABLE</a>
      </div>
    </div>
  </div>

  <!-- Right Ambient Interior Scene -->
  <div class="hero-ambiance-panel">
    <img src="<?= asset('images/aveline_interior.jpg') ?>" alt="Terminal 1 Fine Dining Ambiance" class="hero-ambiance-img">
    <div class="ambiance-overlay"></div>
  </div>

  <!-- Center Overlapping Signature Ceramic Plate -->
  <div class="hero-overlapping-plate">
    <img src="<?= asset('images/aveline_plate.png') ?>" alt="Terminal 1 Signature Culinary Craft" onclick="openSignatureDishModal()">
  </div>
</section>

<!-- EXPERIENCES BEYOND THE TABLE -->
<section class="aveline-section" id="experiences">
  <span class="section-eyebrow">DINING EXPERIENCES</span>
  <h2 class="section-serif-title">EXPERIENCES BEYOND THE TABLE</h2>
  <p class="section-desc">
    From intimate course-by-course seasonal dinners to grand celebrations, every evening is orchestrated with genuine warmth and culinary precision.
  </p>

  <div class="experiences-grid">
    <!-- Card 1 -->
    <div class="experience-card">
      <div>
        <div class="exp-num">(01)</div>
        <h3 class="exp-title">CHEF'S TABLE TASTING</h3>
        <p class="exp-desc">
          An intimate seasonal menu presented course by course, with thoughtful pairings and a limited number of seats per service.
        </p>
      </div>
      <div class="exp-footer">
        <span class="exp-meta">DINNER &bull; FROM ₹850</span>
        <a href="#contact" class="exp-cta-link">RESERVE &rarr;</a>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="experience-card">
      <div>
        <div class="exp-num">(02)</div>
        <h3 class="exp-title">HERITAGE SUPPER LOUNGE</h3>
        <p class="exp-desc">
          Late-evening culinary dishes, clay-oven roasts, and relaxed conversation in a setting designed for memorable nights.
        </p>
      </div>
      <div class="exp-footer">
        <span class="exp-meta">EVENING &bull; BESPOKE</span>
        <a href="#contact" class="exp-cta-link">RESERVE &rarr;</a>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="experience-card">
      <div>
        <div class="exp-num">(03)</div>
        <h3 class="exp-title">PRIVATE DINING SUITE</h3>
        <p class="exp-desc">
          A sophisticated setting for celebrations and gatherings, combining discreet service and carefully considered seasonal menus.
        </p>
      </div>
      <div class="exp-footer">
        <span class="exp-meta">PRIVATE &bull; COURTYARD</span>
        <a href="#contact" class="exp-cta-link">RESERVE &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- SIGNATURE MENU & THE TASTE (CREAM WRAP) -->
<section class="aveline-section menu-cream-wrap" id="menu">
  <span class="section-eyebrow" style="color:var(--gold-accent);">FEATURED MENU</span>
  <h2 class="section-serif-title dark">A CURATED EXPRESSION OF THE SEASON</h2>
  <p class="section-desc" style="color:var(--text-dark-muted);">
    Discover a selection of signature dishes thoughtfully composed to reflect authentic flavors, pristine spices, and artisanal craft.
  </p>

  <!-- Filter Buttons -->
  <div class="menu-category-filter">
    <button class="menu-cat-btn active" data-cat="all">ALL SELECTIONS</button>
    <button class="menu-cat-btn" data-cat="signature">SIGNATURE CUTS</button>
    <button class="menu-cat-btn" data-cat="roasts">CLAY OVEN ROASTS</button>
    <button class="menu-cat-btn" data-cat="asian">ASIAN &amp; DUMPLINGS</button>
    <button class="menu-cat-btn" data-cat="rice">RICE &amp; POLAO</button>
  </div>

  <!-- Dishes Grid -->
  <div class="aveline-dish-grid" id="dishGrid">

    <!-- Item 1: Artisanal Tagliolini with Herb Crumb -->
    <div class="aveline-dish-card" data-category="signature">
      <div class="dish-photo-frame">
        <img src="<?= asset('images/aveline_plate.png') ?>" alt="Artisanal Handcrafted Tagliolini" loading="lazy">
        <span class="dish-diet-pill">Pure Veg</span>
      </div>
      <div class="dish-info-box">
        <div class="dish-title-row">
          <h4 class="dish-item-name">Artisanal Swirl Tagliolini</h4>
          <span class="dish-item-price">₹380</span>
        </div>
        <p class="dish-ingredients-text">
          Fresh handcrafted pasta twirl, roasted herb crumb, cold-pressed olive oil, basil essence, aged mountain cheese.
        </p>
        <div class="dish-card-footer">
          <button type="button" class="btn-dish-inspect" onclick="openDishDetail('Artisanal Swirl Tagliolini', '<?= asset('images/aveline_plate.png') ?>', '₹380', 'Fresh handcrafted pasta twirl, roasted herb crumb, cold-pressed olive oil, fresh basil essence, and aged mountain cheese.')">STORY &amp; SPECS</button>
          <a href="#contact" class="btn-dish-reserve" onclick="preselectDish('Artisanal Swirl Tagliolini')">BOOK TABLE</a>
        </div>
      </div>
    </div>

    <!-- Item 2: Dhonkami Chicken 4.0 -->
    <div class="aveline-dish-card" data-category="signature">
      <div class="dish-photo-frame">
        <img src="<?= asset('images/dhonkami_chicken.jpg') ?>" alt="Dhonkami Chicken 4.0" loading="lazy">
        <span class="dish-diet-pill" style="color:#B91C1C;">Non-Veg</span>
      </div>
      <div class="dish-info-box">
        <div class="dish-title-row">
          <h4 class="dish-item-name">Dhonkami Chicken 4.0</h4>
          <span class="dish-item-price">₹850</span>
        </div>
        <p class="dish-ingredients-text">
          Whole prime cuts, stone-ground cumin, yellow mustard marinade, hung curd, served with 2 Butter Naan + 1 Kulcha.
        </p>
        <div class="dish-card-footer">
          <button type="button" class="btn-dish-inspect" onclick="openDishDetail('Dhonkami Chicken 4.0', '<?= asset('images/dhonkami_chicken.jpg') ?>', '₹850', 'Whole prime cuts, stone-ground cumin, yellow mustard marinade, hung curd, served with 2 Butter Naan + 1 Kulcha.')">STORY &amp; SPECS</button>
          <a href="#contact" class="btn-dish-reserve" onclick="preselectDish('Dhonkami Chicken 4.0')">BOOK TABLE</a>
        </div>
      </div>
    </div>

    <!-- Item 3: Clay Oven Starters Platter -->
    <div class="aveline-dish-card" data-category="roasts">
      <div class="dish-photo-frame">
        <img src="<?= asset('images/starters_platter.jpg') ?>" alt="Tandoori Starters Platter" loading="lazy">
        <span class="dish-diet-pill" style="color:#B91C1C;">Non-Veg</span>
      </div>
      <div class="dish-info-box">
        <div class="dish-title-row">
          <h4 class="dish-item-name">Tandoori Starters Platter</h4>
          <span class="dish-item-price">₹690</span>
        </div>
        <p class="dish-ingredients-text">
          Charcoal-grilled kebabs, tender tandoori cuts, charred farm bell peppers, fresh garden mint botanical chutney.
        </p>
        <div class="dish-card-footer">
          <button type="button" class="btn-dish-inspect" onclick="openDishDetail('Tandoori Starters Platter', '<?= asset('images/starters_platter.jpg') ?>', '₹690', 'Charcoal-grilled kebabs, tender tandoori cuts, charred farm bell peppers, fresh garden mint botanical chutney.')">STORY &amp; SPECS</button>
          <a href="#contact" class="btn-dish-reserve" onclick="preselectDish('Tandoori Starters Platter')">BOOK TABLE</a>
        </div>
      </div>
    </div>

    <!-- Item 4: Artisanal Jiaozi Dumplings -->
    <div class="aveline-dish-card" data-category="asian">
      <div class="dish-photo-frame">
        <img src="<?= asset('images/jiaozi_hero.jpg') ?>" alt="Hand-Pleated Jiaozi Dumplings" loading="lazy">
        <span class="dish-diet-pill" style="color:#B91C1C;">Non-Veg</span>
      </div>
      <div class="dish-info-box">
        <div class="dish-title-row">
          <h4 class="dish-item-name">Artisanal Jiaozi Dumplings</h4>
          <span class="dish-item-price">₹380</span>
        </div>
        <p class="dish-ingredients-text">
          Hand-pleated 0.8mm translucent wrap, savory poultry &amp; shiitake, toasted sesame seeds, stone-ground chili infusion.
        </p>
        <div class="dish-card-footer">
          <button type="button" class="btn-dish-inspect" onclick="openDishDetail('Artisanal Jiaozi Dumplings', '<?= asset('images/jiaozi_hero.jpg') ?>', '₹380', 'Hand-pleated 0.8mm translucent wrap, savory poultry & shiitake, toasted sesame seeds, stone-ground chili infusion.')">STORY &amp; SPECS</button>
          <a href="#contact" class="btn-dish-reserve" onclick="preselectDish('Artisanal Jiaozi Dumplings')">BOOK TABLE</a>
        </div>
      </div>
    </div>

    <!-- Item 5: Truffle Pork & Steamed Bao -->
    <div class="aveline-dish-card" data-category="asian">
      <div class="dish-photo-frame">
        <img src="<?= asset('images/bao_dimsum.jpg') ?>" alt="Truffle Steamed Bao Baskets" loading="lazy">
        <span class="dish-diet-pill" style="color:#B91C1C;">Non-Veg</span>
      </div>
      <div class="dish-info-box">
        <div class="dish-title-row">
          <h4 class="dish-item-name">Truffle Dim Sum &amp; Bao</h4>
          <span class="dish-item-price">₹460</span>
        </div>
        <p class="dish-ingredients-text">
          Steamed bamboo basket, fluffy bao buns, savory filling, scallions, translucent har gow, ginger dipping sauce.
        </p>
        <div class="dish-card-footer">
          <button type="button" class="btn-dish-inspect" onclick="openDishDetail('Truffle Dim Sum & Bao', '<?= asset('images/bao_dimsum.jpg') ?>', '₹460', 'Steamed bamboo basket, fluffy bao buns, savory filling, scallions, translucent har gow, ginger dipping sauce.')">STORY &amp; SPECS</button>
          <a href="#contact" class="btn-dish-reserve" onclick="preselectDish('Truffle Dim Sum & Bao')">BOOK TABLE</a>
        </div>
      </div>
    </div>

    <!-- Item 6: Royal Kashmiri Polao -->
    <div class="aveline-dish-card" data-category="rice">
      <div class="dish-photo-frame">
        <img src="<?= asset('images/polao.jpg') ?>" alt="Royal Kashmiri Polao" loading="lazy">
        <span class="dish-diet-pill">Pure Veg</span>
      </div>
      <div class="dish-info-box">
        <div class="dish-title-row">
          <h4 class="dish-item-name">Royal Kashmiri Polao</h4>
          <span class="dish-item-price">₹260</span>
        </div>
        <p class="dish-ingredients-text">
          Aged long-grain basmati, pure mountain saffron infusion, dried mountain berries, golden raisins, ghee-roasted cashews.
        </p>
        <div class="dish-card-footer">
          <button type="button" class="btn-dish-inspect" onclick="openDishDetail('Royal Kashmiri Polao', '<?= asset('images/polao.jpg') ?>', '₹260', 'Aged long-grain basmati, pure mountain saffron infusion, dried mountain berries, golden raisins, ghee-roasted cashews.')">STORY &amp; SPECS</button>
          <a href="#contact" class="btn-dish-reserve" onclick="preselectDish('Royal Kashmiri Polao')">BOOK TABLE</a>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- PHILOSOPHY SECTION -->
<section class="aveline-section" id="philosophy">
  <div class="philosophy-grid">
    <div>
      <span class="section-eyebrow">OUR PHILOSOPHY</span>
      <blockquote class="philosophy-quote">
        “Seasonal produce, careful sourcing, and generous hospitality shape every meal served at Terminal 1.”
      </blockquote>
    </div>

    <div class="philosophy-body">
      <p>
        Located adjacent to the historic Rajbari in Cooch Behar, Terminal 1 was conceived as an intersection of royal culinary heritage and modern gastronomic technique.
      </p>
      <p>
        Our culinary brigade works closely with regional organic growers, Himalayan spice gatherers, and artisanal clay-oven craftsmen to compose courses that are authentic, nuanced, and memorable.
      </p>
      <div style="margin-top: 10px;">
        <a href="#contact" class="btn-aveline-cta" style="display:inline-block;">RESERVE AN EVENING</a>
      </div>
    </div>
  </div>
</section>

<!-- AVELINE GUESTBOOK & REFLECTIONS SECTION (COMPLIMENTS & COMPLAINTS) -->
<section class="aveline-section guestbook-section" id="reviews">
  <div class="guestbook-header-wrap">
    <span class="section-eyebrow">AUTHENTIC GUEST EXPERIENCES</span>
    <h2 class="section-serif-title">THE GUESTBOOK &mdash; PRAISE &amp; CRITIQUES</h2>
    <p class="section-desc">
      A transparent ledger of our culinary craft. We honor every compliment that inspires our brigade, and take accountability for every critique to refine our craftsmanship.
    </p>

    <!-- Metrics Bar & Submission Trigger -->
    <div class="guestbook-summary-bar">
      <div class="guestbook-rating-badge">
        <div class="rating-stars-large">
          <?php 
          $starsCount = (int)round($reviewStats['avg_rating'] ?? 5);
          echo str_repeat('★', $starsCount) . '<span style="opacity:0.25;">' . str_repeat('★', max(0, 5 - $starsCount)) . '</span>';
          ?>
        </div>
        <div class="rating-score">
          <strong id="gbOverallScore"><?= number_format($reviewStats['avg_rating'] ?? 5.0, 1) ?></strong> <span>/ 5.0 Rating</span>
        </div>
      </div>

      <div class="guestbook-counts-group">
        <div class="count-pill pill-compliments">
          <span class="pill-dot green"></span>
          <span class="pill-number" id="gbComplimentsCount"><?= $reviewStats['compliments'] ?></span>
          <span class="pill-label">Compliments</span>
        </div>
        <div class="count-pill pill-complaints">
          <span class="pill-dot amber"></span>
          <span class="pill-number" id="gbComplaintsCount"><?= $reviewStats['complaints'] ?></span>
          <span class="pill-label">Critiques &amp; Feedback</span>
        </div>
      </div>

      <div>
        <button type="button" class="btn-write-review" onclick="openReviewModal()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          LEAVE A REFLECTION
        </button>
      </div>
    </div>

    <!-- Segment Filter Tabs -->
    <div class="guestbook-filter-tabs">
      <button type="button" class="gb-tab-btn active" id="gbTabAll" onclick="filterGuestbook('all', this)">
        All Reflections (<?= count($reviewsList) ?>)
      </button>
      <button type="button" class="gb-tab-btn" id="gbTabCompliment" onclick="filterGuestbook('compliment', this)">
        ★ Compliments &amp; Praise (<?= $reviewStats['compliments'] ?>)
      </button>
      <button type="button" class="gb-tab-btn" id="gbTabComplaint" onclick="filterGuestbook('complaint', this)">
        ⚠ Critiques &amp; Concerns (<?= $reviewStats['complaints'] ?>)
      </button>
    </div>
  </div>

  <!-- Cards Grid -->
  <div class="guestbook-cards-grid" id="guestbookCardsGrid">
    <?php if (empty($reviewsList)): ?>
      <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; color: var(--text-light-muted); border: 1px dashed rgba(255,255,255,0.1); border-radius: 2px;">
        <p style="font-family: var(--font-serif); font-size: 1.4rem; color: #fff; margin-bottom: 8px;">Be the First to Inscribe Your Memory</p>
        <p style="font-size: 0.88rem; max-width: 480px; margin: 0 auto 20px;">Share your dining impressions, culinary favorites, or constructive feedback with our team.</p>
        <button type="button" class="btn-write-review" onclick="openReviewModal()">Write a Reflection</button>
      </div>
    <?php else: ?>
      <?php foreach ($reviewsList as $rev): ?>
        <div class="guestbook-card <?= $rev['type'] === 'complaint' ? 'is-complaint' : 'is-compliment' ?>" data-type="<?= e($rev['type']) ?>">
          <div class="gb-card-top">
            <?php if ($rev['type'] === 'compliment'): ?>
              <span class="gb-type-tag tag-compliment">★ Compliment</span>
            <?php else: ?>
              <span class="gb-type-tag tag-complaint">⚠ Critique / Feedback</span>
            <?php endif; ?>
            <div class="gb-card-stars">
              <?= str_repeat('★', (int)$rev['rating']) ?><span style="opacity:0.25;"><?= str_repeat('★', 5 - (int)$rev['rating']) ?></span>
            </div>
          </div>

          <h3 class="gb-card-title"><?= e($rev['title'] ?: ($rev['type'] === 'compliment' ? 'Exquisite Dining Experience' : 'Dining Reflection & Service Notes')) ?></h3>

          <p class="gb-card-quote">“<?= nl2br(e($rev['content'])) ?>”</p>

          <?php if (!empty($rev['admin_reply'])): ?>
            <div class="gb-admin-reply">
              <div class="reply-header">
                <span class="reply-crest">T1</span>
                <strong>Management Response</strong>
              </div>
              <p class="reply-text"><?= nl2br(e($rev['admin_reply'])) ?></p>
            </div>
          <?php endif; ?>

          <div class="gb-card-footer">
            <div class="gb-author-info">
              <div class="gb-author-avatar">
                <?= strtoupper(substr($rev['name'] ?? 'G', 0, 1)) ?>
              </div>
              <div>
                <span class="gb-author-name"><?= e($rev['name']) ?></span>
                <span class="gb-meta-date"><?= date('F Y', strtotime($rev['created_at'])) ?></span>
              </div>
            </div>
            <?php if (!empty($rev['user_id'])): ?>
              <span class="gb-verified-badge">&#10003; Verified Diner</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- AVELINE RESERVATION SECTION ("BOOK YOUR EXPERIENCE") -->
<section class="aveline-section reservation-section" id="contact">
  <div class="reservation-container">
    <div class="reservation-header">
      <span class="section-eyebrow">RESERVATIONS</span>
      <h2 class="section-serif-title">BOOK YOUR EXPERIENCE</h2>
      <p class="section-desc" style="margin: 0 auto;">
        Choose your preferred date, time, and table setting. Our concierge team will confirm availability and any bespoke requests directly.
      </p>
    </div>

    <!-- Party Size Selector -->
    <span class="party-chips-label">SELECT NUMBER OF GUESTS</span>
    <div class="party-chips-row">
      <button type="button" class="party-chip-btn active" onclick="setPartySize(this, 2)">2 Diners &bull; Intimate</button>
      <button type="button" class="party-chip-btn" onclick="setPartySize(this, 4)">4 Diners &bull; Bistro</button>
      <button type="button" class="party-chip-btn" onclick="setPartySize(this, 6)">6 Diners &bull; Lounge</button>
      <button type="button" class="party-chip-btn" onclick="setPartySize(this, 8)">8+ Diners &bull; Banquet</button>
    </div>

    <?php if(!$user): ?>
      <!-- Guest Login Gate — STRICTLY COMPLIES WITH test_reserve_login_gate.php -->
      <div class="aveline-guest-gate">
        <h4>PLEASE SIGN IN TO PROCEED</h4>
        <p>
          To ensure personal concierge attention and avoid double bookings, dining reservations require an authenticated diner account.
        </p>
        <a href="<?= url('/auth/login?redirect=' . urlencode('/#contact')) ?>" class="btn-gate-signin">
          Sign In to Reserve a Table
        </a>
        <div style="font-size:0.78rem; color:var(--text-light-dim); margin-top:16px;">
          First visit to Terminal 1? <a href="<?= url('/auth/register?redirect=' . urlencode('/#contact')) ?>" style="color:#FFFFFF; text-decoration:underline;">Create an account</a>
        </div>
      </div>

    <?php else: ?>
      <!-- Authenticated Reservation Form -->
      <form id="avelineBookingForm" onsubmit="handleAvelineBooking(event)">
        <input type="hidden" name="csrf_token" id="fcsrf" value="<?= csrfToken() ?>">
        <input type="hidden" name="guests" id="fguests" value="2">
        <input type="hidden" name="name" id="fname" value="<?= e($user['name']) ?>">
        <input type="hidden" name="email" id="femail" value="<?= e($user['email']) ?>">

        <div class="res-form-grid">
          <div class="res-field">
            <label for="fdate">PREFERRED DATE</label>
            <input type="date" id="fdate" name="date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="res-field">
            <label for="ftime">DINING TIME SLOT</label>
            <select id="ftime" name="time" required>
              <option value="12:30">12:30 PM &mdash; Lunch Service</option>
              <option value="13:30">01:30 PM &mdash; Afternoon Service</option>
              <option value="19:00" selected>07:00 PM &mdash; Evening Service</option>
              <option value="20:30">08:30 PM &mdash; Prime Sitting</option>
              <option value="21:45">09:45 PM &mdash; Late Supper</option>
            </select>
          </div>
        </div>

        <div class="res-form-grid">
          <div class="res-field">
            <label for="fphone">CONTACT NUMBER</label>
            <input type="tel" id="fphone" name="phone" placeholder="+91 98765 43210" required>
          </div>
          <div class="res-field">
            <label for="foccasion">OCCASION / FORMAT</label>
            <select id="foccasion" name="occasion">
              <option value="Chef Table Tasting">Chef's Table Tasting</option>
              <option value="Casual Fine Dining" selected>Casual Fine Dining</option>
              <option value="Birthday Celebration">Birthday Celebration</option>
              <option value="Anniversary">Anniversary</option>
              <option value="Private Gathering">Private Gathering</option>
            </select>
          </div>
        </div>

        <div class="res-field" style="margin-bottom: 24px;">
          <label for="fmsg">SPECIAL REQUESTS / DIETARY NOTES</label>
          <textarea id="fmsg" name="special_requests" rows="2" placeholder="Tell us about allergies, preferred courses, or seating preferences..."></textarea>
        </div>

        <button type="submit" id="btnAvelineSubmit" class="btn-submit-aveline">
          <span id="btnSubmitTxt">REQUEST A TABLE</span>
          <span id="btnSubmitSpinner" style="display:none;">&bull;</span>
        </button>
      </form>
    <?php endif; ?>
  </div>
</section>

<!-- DISH STORY MODAL -->
<div class="aveline-modal-overlay" id="avelineModal" onclick="closeAvelineModal(event)">
  <div class="aveline-modal-card" onclick="event.stopPropagation()">
    <button type="button" class="aveline-modal-close" onclick="closeAvelineModal()">&times;</button>
    <div class="modal-grid">
      <div class="modal-photo">
        <img id="mImg" src="<?= asset('images/aveline_plate.png') ?>" alt="Dish Detail">
      </div>
      <div class="modal-details">
        <div>
          <span class="section-eyebrow" style="margin-bottom:6px;">CULINARY STORY</span>
          <h3 style="font-family:var(--font-serif); font-size:1.6rem; color:#FFFFFF;" id="mTitle">Artisanal Swirl Tagliolini</h3>
          <div style="font-family:var(--font-serif); font-size:1.2rem; color:var(--gold-accent); margin-top:4px;" id="mPrice">₹380</div>
          <p style="color:var(--text-light-muted); font-size:0.86rem; line-height:1.6; margin-top:14px;" id="mDesc">
            Fresh handcrafted pasta twirl, roasted herb crumb, cold-pressed olive oil, fresh basil essence, and aged mountain cheese.
          </p>
        </div>

        <div style="margin-top:24px;">
          <a href="#contact" class="btn-aveline-cta" style="display:block; text-align:center;" onclick="closeAvelineModal(); preselectDish(document.getElementById('mTitle').textContent)">
            RESERVE TABLE FOR THIS DISH
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- EDITORIAL FOOTER -->
<footer>
  <div class="footer-columns-grid">
    <div class="footer-col">
      <a href="<?= url('/') ?>" class="brand-logo" style="display:block; margin-bottom:14px;">TERMINAL 1</a>
      <p>
        An exquisite journey of flavor, where refined ingredients, thoughtful technique, and timeless elegance come together in perfect harmony.
      </p>
    </div>

    <div class="footer-col">
      <h5>ADDRESS</h5>
      <p>
        Near Rajbari Palace Complex<br>
        Cooch Behar, West Bengal &mdash; 736101<br>
        India
      </p>
    </div>

    <div class="footer-col">
      <h5>OPENING HOURS</h5>
      <p>
        Monday to Sunday<br>
        Lunch: 12:00 PM &mdash; 04:00 PM<br>
        Dinner: 06:30 PM &mdash; 11:00 PM
      </p>
    </div>

    <div class="footer-col">
      <h5>NAVIGATION</h5>
      <ul class="footer-nav-list">
        <li><a href="#reviews">Guestbook &amp; Reviews</a></li>
        <li><a href="<?= url('/menu') ?>">Full Digital Menu</a></li>
        <li><a href="<?= url('/my-bookings') ?>">Track Live Allocation</a></li>
        <li><a href="<?= url('/legal/privacy') ?>">Privacy Policy</a></li>
        <li><a href="<?= url('/legal/terms') ?>">Terms of Dining</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom-strip">
    <div>&copy; <?= date('Y') ?> TERMINAL 1. ALL RIGHTS RESERVED.</div>
    <div>AVELINE EDITORIAL RESTAURANT SYSTEM</div>
  </div>
</footer>

<!-- REVIEW SUBMISSION MODAL (COMPLIMENTS & COMPLAINTS) -->
<div id="reviewModal" onclick="closeReviewModal(event)">
  <div class="review-modal-box" onclick="event.stopPropagation()">
    <button type="button" class="review-modal-close" onclick="closeReviewModal()">&times;</button>
    
    <div style="margin-bottom: 20px;">
      <span class="section-eyebrow" style="color:var(--gold-accent); margin-bottom:6px;">GUESTBOOK REFLECTION</span>
      <h3 style="font-family:var(--font-serif); font-size:1.8rem; font-weight:400; color:#FFFFFF; margin-bottom:8px;">INSCRIBE YOUR THOUGHTS</h3>
      <p style="font-size:0.84rem; color:var(--text-light-muted); line-height:1.55;">
        Whether celebrating our culinary craft with a compliment or guiding us with a candid critique, your reflection directly reaches our brigade.
      </p>
    </div>

    <form id="reviewSubmitForm" onsubmit="handleReviewSubmit(event)">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="type" id="formReviewType" value="compliment">
      <input type="hidden" name="rating" id="formReviewRating" value="5">

      <!-- Segmented Type Selector -->
      <label style="display:block; font-size:0.72rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--text-light-dim); margin-bottom:8px; font-weight:600;">
        CHOOSE NATURE OF REFLECTION
      </label>
      <div class="type-switcher-grid">
        <button type="button" id="btnTypeCompliment" class="type-switch-btn active-compliment" onclick="setReviewType('compliment')">
          <span style="font-size:1.15rem;">★</span>
          <span>Compliment / Praise</span>
        </button>
        <button type="button" id="btnTypeComplaint" class="type-switch-btn" onclick="setReviewType('complaint')">
          <span style="font-size:1.15rem;">⚠</span>
          <span>Critique / Concern</span>
        </button>
      </div>

      <!-- Dynamic Prompt Hint -->
      <div id="reviewPromptHint" style="background:rgba(45,212,191,0.08); border-left:2px solid #5eead4; padding:8px 12px; font-size:0.78rem; color:#5eead4; margin-bottom:18px; border-radius:0 2px 2px 0;">
        Share what delighted your palate, from dish execution to hospitable table service.
      </div>

      <!-- Star Rating Picker -->
      <div style="margin-bottom: 18px;">
        <label style="display:block; font-size:0.72rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--text-light-dim); margin-bottom:8px; font-weight:600;">
          OVERALL DINING RATING
        </label>
        <div style="display:flex; align-items:center;">
          <div class="star-rating-picker" id="starPicker">
            <span class="star-item selected" data-value="1" onmouseover="hoverStars(1)" onmouseout="resetStars()" onclick="selectRating(1)">★</span>
            <span class="star-item selected" data-value="2" onmouseover="hoverStars(2)" onmouseout="resetStars()" onclick="selectRating(2)">★</span>
            <span class="star-item selected" data-value="3" onmouseover="hoverStars(3)" onmouseout="resetStars()" onclick="selectRating(3)">★</span>
            <span class="star-item selected" data-value="4" onmouseover="hoverStars(4)" onmouseout="resetStars()" onclick="selectRating(4)">★</span>
            <span class="star-item selected" data-value="5" onmouseover="hoverStars(5)" onmouseout="resetStars()" onclick="selectRating(5)">★</span>
          </div>
          <span class="rating-label-hint" id="ratingLabelHint">5 &mdash; Exceptional</span>
        </div>
      </div>

      <!-- Guest Name & Email Grid -->
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-light-dim); margin-bottom:6px; font-weight:600;">
            YOUR NAME *
          </label>
          <input type="text" name="name" required value="<?= e($user['name'] ?? '') ?>" placeholder="e.g. Ananya Sen" style="width:100%; background:#100F0D; border:1px solid rgba(255,255,255,0.12); color:#fff; padding:10px 12px; font-size:0.84rem; border-radius:2px; outline:none;">
        </div>

        <div>
          <label style="display:block; font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-light-dim); margin-bottom:6px; font-weight:600;">
            EMAIL <span style="font-weight:400; opacity:0.6;">(CONFIDENTIAL)</span>
          </label>
          <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" placeholder="ananya@example.com" style="width:100%; background:#100F0D; border:1px solid rgba(255,255,255,0.12); color:#fff; padding:10px 12px; font-size:0.84rem; border-radius:2px; outline:none;">
        </div>
      </div>

      <!-- Title & Dining Date Grid -->
      <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-light-dim); margin-bottom:6px; font-weight:600;">
            HEADLINE / SUBJECT
          </label>
          <input type="text" name="title" id="reviewHeadline" placeholder="e.g. Saffron Polao &amp; Ambience" style="width:100%; background:#100F0D; border:1px solid rgba(255,255,255,0.12); color:#fff; padding:10px 12px; font-size:0.84rem; border-radius:2px; outline:none;">
        </div>

        <div>
          <label style="display:block; font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-light-dim); margin-bottom:6px; font-weight:600;">
            DATE OF VISIT
          </label>
          <input type="date" name="visit_date" max="<?= date('Y-m-d') ?>" style="width:100%; background:#100F0D; border:1px solid rgba(255,255,255,0.12); color:#fff; padding:10px 12px; font-size:0.84rem; border-radius:2px; outline:none;">
        </div>
      </div>

      <!-- Reflection Content -->
      <div style="margin-bottom: 20px;">
        <label style="display:block; font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-light-dim); margin-bottom:6px; font-weight:600;">
          YOUR REFLECTION / REMARKS *
        </label>
        <textarea name="content" id="reviewTextarea" rows="4" required placeholder="Describe your experience in detail..." style="width:100%; background:#100F0D; border:1px solid rgba(255,255,255,0.12); color:#fff; padding:12px; font-family:inherit; font-size:0.85rem; line-height:1.5; border-radius:2px; resize:vertical; outline:none;"></textarea>
      </div>

      <!-- Action Buttons -->
      <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <span style="font-size:0.72rem; color:var(--text-light-dim);">
          Your entry is verified and recorded to the public ledger.
        </span>

        <div style="display:flex; align-items:center; gap:10px;">
          <button type="button" class="btn-parchment-outline" onclick="closeReviewModal()" style="padding:10px 18px; font-size:0.72rem; color:#FFFFFF; border-color:rgba(255,255,255,0.2);">
            CANCEL
          </button>
          <button type="submit" id="btnSubmitReview" class="btn-write-review" style="padding:11px 22px;">
            <span id="btnReviewSpinner" style="display:none; width:12px; height:12px; border:2px solid #121110; border-top-color:transparent; border-radius:50%; animation:spin 0.8s linear infinite; margin-right:6px;"></span>
            <span id="btnReviewText">PUBLISH REFLECTION</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<div id="aveline-toast"></div>

<!-- Razorpay Script for deposit handling -->
<script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>

<script>
// ─── 1. DISH DETAIL MODAL ───
function openSignatureDishModal() {
  openDishDetail(
    'Artisanal Swirl Tagliolini',
    '<?= asset('images/aveline_plate.png') ?>',
    '₹380',
    'Fresh handcrafted pasta twirl, roasted herb crumb, cold-pressed olive oil, fresh basil essence, and aged mountain cheese.'
  );
}

function openDishDetail(name, img, price, desc) {
  document.getElementById('mTitle').textContent = name;
  document.getElementById('mImg').src = img;
  document.getElementById('mPrice').textContent = price;
  document.getElementById('mDesc').textContent = desc;
  document.getElementById('avelineModal').classList.add('active');
}

function closeAvelineModal(e) {
  if (e && e.target !== e.currentTarget && !e.target.classList.contains('aveline-modal-close')) return;
  document.getElementById('avelineModal').classList.remove('active');
}

function preselectDish(dishName) {
  const msg = document.getElementById('fmsg');
  if (msg) {
    if (!msg.value.includes(dishName)) {
      msg.value = (msg.value ? msg.value + '; ' : '') + 'Request dish: ' + dishName;
    }
  }
}

// ─── 2. CATEGORY FILTERING ───
document.querySelectorAll('.menu-cat-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.menu-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const cat = btn.getAttribute('data-cat');
    const cards = document.querySelectorAll('.aveline-dish-card');

    cards.forEach(card => {
      const cardCat = card.getAttribute('data-category');
      if (cat === 'all' || cardCat === cat) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  });
});

// ─── 3. PARTY SIZE SELECTION ───
function setPartySize(btn, guests) {
  document.querySelectorAll('.party-chip-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const input = document.getElementById('fguests');
  if (input) input.value = guests;
}

// ─── 4. TOAST NOTIFICATION ───
function showAvelineToast(msg) {
  const t = document.getElementById('aveline-toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 4000);
}

// ─── 5. RESERVATION FORM SUBMISSION ───
async function handleAvelineBooking(e) {
  e.preventDefault();
  const btn = document.getElementById('btnAvelineSubmit');
  const txt = document.getElementById('btnSubmitTxt');
  const spn = document.getElementById('btnSubmitSpinner');

  if (btn.disabled) return;
  btn.disabled = true;
  txt.textContent = 'REQUESTING TABLE...';
  spn.style.display = 'inline';

  try {
    const formData = new FormData(document.getElementById('avelineBookingForm'));
    const res = await fetch('<?= url('/book') ?>', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    });

    const data = await res.json();
    if (data.success) {
      showAvelineToast('TABLE REQUEST RECEIVED! ' + data.message);
      if (data.tracking_token) {
        setTimeout(() => {
          window.location.href = '<?= url('/track/') ?>' + data.tracking_token;
        }, 1500);
      }
    } else {
      showAvelineToast('ERROR: ' + (data.message || 'Booking could not be finalized.'));
    }
  } catch(err) {
    showAvelineToast('Network error. Please try again.');
  } finally {
    btn.disabled = false;
    txt.textContent = 'REQUEST A TABLE';
    spn.style.display = 'none';
  }
}

// ─── 6. GUESTBOOK REFLECTIONS & REVIEWS ───
let currentSelectedRating = 5;
const ratingLabels = {
  1: '1 — Critical / Highly Dissatisfied',
  2: '2 — Disappointing / Needs Improvement',
  3: '3 — Average / Mixed Thoughts',
  4: '4 — Very Good / Enjoyable',
  5: '5 — Exceptional / Exquisite'
};

function openReviewModal() {
  document.getElementById('reviewModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeReviewModal(e) {
  if (e && e.target !== e.currentTarget && !e.target.classList.contains('review-modal-close')) return;
  document.getElementById('reviewModal').classList.remove('active');
  document.body.style.overflow = '';
}

function setReviewType(type) {
  document.getElementById('formReviewType').value = type;
  const btnComp = document.getElementById('btnTypeCompliment');
  const btnCrit = document.getElementById('btnTypeComplaint');
  const hint = document.getElementById('reviewPromptHint');
  const headline = document.getElementById('reviewHeadline');
  const textarea = document.getElementById('reviewTextarea');

  if (type === 'compliment') {
    btnComp.classList.add('active-compliment');
    btnCrit.classList.remove('active-complaint');
    hint.style.background = 'rgba(45,212,191,0.08)';
    hint.style.borderLeftColor = '#5eead4';
    hint.style.color = '#5eead4';
    hint.textContent = 'Share what delighted your palate, from dish execution to hospitable table service.';
    headline.placeholder = 'e.g. Saffron Polao & Ambience Perfection';
    textarea.placeholder = 'Describe the flavours, courses, or moments that made your visit memorable...';
    if (currentSelectedRating < 4) {
      selectRating(5);
    }
  } else {
    btnCrit.classList.add('active-complaint');
    btnComp.classList.remove('active-compliment');
    hint.style.background = 'rgba(245,158,11,0.08)';
    hint.style.borderLeftColor = '#f59e0b';
    hint.style.color = '#fbbf24';
    hint.textContent = 'Tell us candidly where our service, food, or timing fell short so management can take corrective action.';
    headline.placeholder = 'e.g. Starter Pacing on Saturday Evening';
    textarea.placeholder = 'Please share specific details regarding table service, timing, or flavors...';
    if (currentSelectedRating > 3) {
      selectRating(3);
    }
  }
}

function hoverStars(val) {
  const stars = document.querySelectorAll('#starPicker .star-item');
  stars.forEach((s, i) => {
    if (i < val) {
      s.classList.add('hovered');
    } else {
      s.classList.remove('hovered');
    }
  });
  document.getElementById('ratingLabelHint').textContent = ratingLabels[val] || '';
}

function resetStars() {
  const stars = document.querySelectorAll('#starPicker .star-item');
  stars.forEach(s => s.classList.remove('hovered'));
  selectRating(currentSelectedRating);
}

function selectRating(val) {
  currentSelectedRating = val;
  document.getElementById('formReviewRating').value = val;
  const stars = document.querySelectorAll('#starPicker .star-item');
  stars.forEach((s, i) => {
    if (i < val) {
      s.classList.add('selected');
    } else {
      s.classList.remove('selected');
    }
  });
  document.getElementById('ratingLabelHint').textContent = ratingLabels[val] || '';
}

function filterGuestbook(type, btn) {
  document.querySelectorAll('.gb-tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const cards = document.querySelectorAll('.guestbook-card');
  cards.forEach(c => {
    if (type === 'all' || c.getAttribute('data-type') === type) {
      c.style.display = 'flex';
    } else {
      c.style.display = 'none';
    }
  });
}

async function handleReviewSubmit(e) {
  e.preventDefault();
  const form = document.getElementById('reviewSubmitForm');
  const btn = document.getElementById('btnSubmitReview');
  const txt = document.getElementById('btnReviewText');
  const spn = document.getElementById('btnReviewSpinner');

  if (btn.disabled) return;
  btn.disabled = true;
  txt.textContent = 'RECORDING REFLECTION...';
  spn.style.display = 'inline-block';

  try {
    const formData = new FormData(form);
    const res = await fetch('<?= url('/reviews') ?>', {
      method: 'POST',
      headers: { 
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData
    });

    const data = await res.json();
    if (data.success) {
      showAvelineToast(data.message);
      closeReviewModal();
      form.reset();
      setReviewType('compliment');
      selectRating(5);

      // Prepend newly created card to the grid
      if (data.review) {
        const rev = data.review;
        const grid = document.getElementById('guestbookCardsGrid');
        const card = document.createElement('div');
        const isCompliment = rev.type === 'compliment';
        card.className = 'guestbook-card ' + (isCompliment ? 'is-compliment' : 'is-complaint');
        card.setAttribute('data-type', rev.type);

        const starsStr = '★'.repeat(parseInt(rev.rating)) + '<span style="opacity:0.25;">' + '★'.repeat(Math.max(0, 5 - parseInt(rev.rating))) + '</span>';
        const tagBadge = isCompliment
          ? '<span class="gb-type-tag tag-compliment">★ Compliment</span>'
          : '<span class="gb-type-tag tag-complaint">⚠ Critique / Feedback</span>';

        card.innerHTML = `
          <div class="gb-card-top">
            ${tagBadge}
            <div class="gb-card-stars">${starsStr}</div>
          </div>
          <h3 class="gb-card-title">${escapeHtml(rev.title || (isCompliment ? 'Exquisite Dining Experience' : 'Dining Reflection & Service Notes'))}</h3>
          <p class="gb-card-quote">“${escapeHtml(rev.content)}”</p>
          <div class="gb-card-footer">
            <div class="gb-author-info">
              <div class="gb-author-avatar">${escapeHtml((rev.name || 'G').charAt(0).toUpperCase())}</div>
              <div>
                <span class="gb-author-name">${escapeHtml(rev.name)}</span>
                <span class="gb-meta-date">Just Now</span>
              </div>
            </div>
            <span class="gb-verified-badge">&#10003; Verified Diner</span>
          </div>
        `;

        // Remove empty state message if present
        const emptyState = grid.querySelector('div[style*="border: 1px dashed"]');
        if (emptyState) emptyState.remove();

        grid.prepend(card);

        // Update counts
        if (data.stats) {
          const compEl = document.getElementById('gbComplimentsCount');
          const critEl = document.getElementById('gbComplaintsCount');
          const scoreEl = document.getElementById('gbOverallScore');
          if (compEl) compEl.textContent = data.stats.compliments;
          if (critEl) critEl.textContent = data.stats.complaints;
          if (scoreEl) scoreEl.textContent = parseFloat(data.stats.avg_rating).toFixed(1);

          const tabAll = document.getElementById('gbTabAll');
          const tabComp = document.getElementById('gbTabCompliment');
          const tabCrit = document.getElementById('gbTabComplaint');
          if (tabAll) tabAll.textContent = `All Reflections (${grid.querySelectorAll('.guestbook-card').length})`;
          if (tabComp) tabComp.textContent = `★ Compliments & Praise (${data.stats.compliments})`;
          if (tabCrit) tabCrit.textContent = `⚠ Critiques & Concerns (${data.stats.complaints})`;
        }
      }
    } else {
      const errMsg = data.errors ? data.errors.join(' ') : (data.message || 'Submission could not be completed.');
      showAvelineToast('ERROR: ' + errMsg);
    }
  } catch(err) {
    showAvelineToast('Network error while recording reflection.');
  } finally {
    btn.disabled = false;
    txt.textContent = 'PUBLISH REFLECTION';
    spn.style.display = 'none';
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
</script>
</body>
</html>
