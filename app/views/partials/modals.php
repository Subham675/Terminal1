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
