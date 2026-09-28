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
        <input type="hidden" name="name" id="fname" value="<?= e($user['name'] ?? '') ?>">
        <input type="hidden" name="email" id="femail" value="<?= e($user['email'] ?? '') ?>">

        <!-- Visible & Keyboard-Accessible Party Size Control -->
        <div class="res-field" style="margin-bottom: 20px;">
          <label for="fguests">PARTY SIZE (GUESTS)</label>
          <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <input type="number" id="fguests" name="guests" min="1" max="30" value="2" required
                   style="width: 110px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-dark); color: #fff; padding: 10px 14px; font-family: var(--font-sans); font-size: 0.95rem; border-radius: 2px;"
                   oninput="handleGuestInput(this.value)" aria-label="Number of Guests">
            <span style="font-size: 0.82rem; color: var(--text-light-muted);">
              Diners &bull; Type any party size or tap quick-select chips above
            </span>
          </div>
          <span class="res-field-error" id="err-fguests" role="alert" style="display:none; color:#f87171; font-size:0.75rem; margin-top:4px;"></span>
        </div>

        <div class="res-form-grid">
          <div class="res-field">
            <label for="fdate">PREFERRED DATE</label>
            <input type="date" id="fdate" name="booking_date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" onchange="checkSlotAvailability(this.value)">
            <span class="res-field-error" id="err-fdate" role="alert" style="display:none; color:#f87171; font-size:0.75rem; margin-top:4px;"></span>
          </div>
          <div class="res-field">
            <label for="ftime">DINING TIME SLOT</label>
            <select id="ftime" name="booking_time" required>
              <option value="12:30">12:30 PM &mdash; Lunch Service</option>
              <option value="13:30">01:30 PM &mdash; Afternoon Service</option>
              <option value="19:00" selected>07:00 PM &mdash; Evening Service</option>
              <option value="20:30">08:30 PM &mdash; Prime Sitting</option>
              <option value="21:45">09:45 PM &mdash; Late Supper</option>
            </select>
            <span class="res-field-error" id="err-ftime" role="alert" style="display:none; color:#f87171; font-size:0.75rem; margin-top:4px;"></span>
          </div>
        </div>

        <div class="res-form-grid">
          <div class="res-field">
            <label for="fphone">CONTACT NUMBER</label>
            <input type="tel" id="fphone" name="phone" placeholder="+91 98765 43210" required>
            <span class="res-field-error" id="err-fphone" role="alert" style="display:none; color:#f87171; font-size:0.75rem; margin-top:4px;"></span>
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
            <span class="res-field-error" id="err-foccasion" role="alert" style="display:none; color:#f87171; font-size:0.75rem; margin-top:4px;"></span>
          </div>
        </div>

        <div class="res-field" style="margin-bottom: 24px;">
          <label for="fmsg">SPECIAL REQUESTS / DIETARY NOTES</label>
          <textarea id="fmsg" name="special_requests" rows="2" placeholder="Tell us about allergies, preferred courses, or seating preferences..."></textarea>
          <span class="res-field-error" id="err-fmsg" role="alert" style="display:none; color:#f87171; font-size:0.75rem; margin-top:4px;"></span>
        </div>

        <div id="resFormAlert" role="alert" style="display:none; padding:12px 16px; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; font-size:0.84rem; margin-bottom:16px; border-radius:2px;"></div>

        <button type="submit" id="btnAvelineSubmit" class="btn-submit-aveline">
          <span id="btnSubmitTxt">REQUEST A TABLE</span>
          <span id="btnSubmitSpinner" style="display:none;">&bull;</span>
        </button>
      </form>
    <?php endif; ?>
  </div>
</section>
