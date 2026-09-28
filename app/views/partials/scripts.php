<!-- Razorpay Script for deposit handling -->
<script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>

<script>
// ─── 0. HORIZONTAL SLIDEBAR CONTROLS ───
function slideTrack(trackId, direction) {
  const track = document.getElementById(trackId);
  if (!track) return;
  const visibleCard = Array.from(track.children).find(c => c.offsetParent !== null);
  const amount = visibleCard ? (visibleCard.offsetWidth + 20) : 320;
  track.scrollBy({ left: direction * amount, behavior: 'smooth' });
}

function jumpSlide(trackId, index) {
  const track = document.getElementById(trackId);
  if (!track) return;
  const visibleCards = Array.from(track.children).filter(c => c.offsetParent !== null);
  if (visibleCards[index]) {
    const card = visibleCards[index];
    track.scrollTo({
      left: card.offsetLeft - track.offsetLeft,
      behavior: 'smooth'
    });
  }
}

function setupTrackObserver(trackId, indicatorId) {
  const track = document.getElementById(trackId);
  const indicatorsWrap = document.getElementById(indicatorId);
  if (!track || !indicatorsWrap) return;
  const dots = indicatorsWrap.querySelectorAll('.indicator-dot');
  if (!dots.length) return;

  let isTicking = false;
  track.addEventListener('scroll', () => {
    if (!isTicking) {
      window.requestAnimationFrame(() => {
        const cards = Array.from(track.children).filter(c => c.offsetParent !== null);
        if (cards.length) {
          const trackLeft = track.getBoundingClientRect().left;
          let closestIdx = 0;
          let minDiff = Infinity;
          cards.forEach((card, idx) => {
            const diff = Math.abs(card.getBoundingClientRect().left - trackLeft);
            if (diff < minDiff) {
              minDiff = diff;
              closestIdx = idx;
            }
          });
          dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === closestIdx);
          });
        }
        isTicking = false;
      });
      isTicking = true;
    }
  }, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
  setupTrackObserver('experiencesTrack', 'experiencesIndicators');
  restoreBookingDraft();
  const bForm = document.getElementById('avelineBookingForm');
  if (bForm) {
    bForm.querySelectorAll('input, select, textarea').forEach(inp => {
      inp.addEventListener('input', saveBookingDraft);
      inp.addEventListener('change', saveBookingDraft);
    });
  }
});

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

    const menuTrack = document.getElementById('dishGrid');
    if (menuTrack) {
      menuTrack.scrollTo({ left: 0, behavior: 'smooth' });
    }
  });
});

// ─── 3. PARTY SIZE SELECTION & INPUT ───
function setPartySize(btn, guests) {
  document.querySelectorAll('.party-chip-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  const input = document.getElementById('fguests');
  if (input) {
    input.value = guests;
    saveBookingDraft();
  }
}

function handleGuestInput(val) {
  const g = parseInt(val) || 0;
  document.querySelectorAll('.party-chip-btn').forEach(b => {
    const text = b.textContent;
    if (g >= 8 && text.includes('8+')) {
      b.classList.add('active');
    } else if (text.startsWith(g + ' ')) {
      b.classList.add('active');
    } else {
      b.classList.remove('active');
    }
  });
  saveBookingDraft();
}

// ─── 3b. SLOT AVAILABILITY CHECK ───
async function checkSlotAvailability(date) {
  if (!date) return;
  const select = document.getElementById('ftime');
  if (!select) return;

  try {
    const res = await fetch('<?= url('/bookings/slots?date=') ?>' + encodeURIComponent(date));
    const data = await res.json();
    if (data && data.slots) {
      let currentSelected = select.value;
      let selectedStillValid = false;

      Array.from(select.options).forEach(opt => {
        const slot = data.slots.find(s => s.time === opt.value);
        if (slot) {
          const baseLabel = slot.label;
          if (slot.full) {
            opt.disabled = true;
            opt.textContent = baseLabel + ' — FULL';
          } else {
            opt.disabled = false;
            opt.textContent = baseLabel + ' (' + slot.available + ' left)';
            if (opt.value === currentSelected) selectedStillValid = true;
          }
        }
      });

      if (!selectedStillValid) {
        const firstAvail = Array.from(select.options).find(opt => !opt.disabled);
        if (firstAvail) select.value = firstAvail.value;
      }
    }
  } catch(e) {
    console.warn('Slot check failed:', e);
  }
  saveBookingDraft();
}

// ─── 3c. BOOKING DRAFT PERSISTENCE (SESSIONSTORAGE) ───
function saveBookingDraft() {
  const form = document.getElementById('avelineBookingForm');
  if (!form) return;
  const draft = {
    date: document.getElementById('fdate')?.value || '',
    time: document.getElementById('ftime')?.value || '',
    phone: document.getElementById('fphone')?.value || '',
    occasion: document.getElementById('foccasion')?.value || '',
    guests: document.getElementById('fguests')?.value || '2',
    msg: document.getElementById('fmsg')?.value || ''
  };
  sessionStorage.setItem('aveline_booking_draft', JSON.stringify(draft));
}

function restoreBookingDraft() {
  const raw = sessionStorage.getItem('aveline_booking_draft');
  if (!raw) return;
  try {
    const draft = JSON.parse(raw);
    if (draft.date && document.getElementById('fdate')) document.getElementById('fdate').value = draft.date;
    if (draft.phone && document.getElementById('fphone')) document.getElementById('fphone').value = draft.phone;
    if (draft.occasion && document.getElementById('foccasion')) document.getElementById('foccasion').value = draft.occasion;
    if (draft.msg && document.getElementById('fmsg')) document.getElementById('fmsg').value = draft.msg;
    if (draft.guests && document.getElementById('fguests')) {
      document.getElementById('fguests').value = draft.guests;
      handleGuestInput(draft.guests);
    }
    if (draft.date) checkSlotAvailability(draft.date).then(() => {
      if (draft.time && document.getElementById('ftime')) document.getElementById('ftime').value = draft.time;
    });
  } catch(e) {}
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
  const formAlert = document.getElementById('resFormAlert');

  // Clear previous inline errors
  document.querySelectorAll('.res-field-error').forEach(el => {
    el.style.display = 'none';
    el.textContent = '';
  });
  if (formAlert) {
    formAlert.style.display = 'none';
    formAlert.textContent = '';
  }

  // Client validation
  const phone = document.getElementById('fphone')?.value.trim();
  const errPhone = document.getElementById('err-fphone');
  if (!phone || phone.replace(/\D/g, '').length < 10) {
    if (errPhone) {
      errPhone.textContent = 'Please enter a valid 10-digit contact telephone number.';
      errPhone.style.display = 'block';
    }
    document.getElementById('fphone')?.focus();
    return;
  }

  const guests = parseInt(document.getElementById('fguests')?.value) || 0;
  const errGuests = document.getElementById('err-fguests');
  if (guests < 1 || guests > 30) {
    if (errGuests) {
      errGuests.textContent = 'Party size must be between 1 and 30 guests.';
      errGuests.style.display = 'block';
    }
    document.getElementById('fguests')?.focus();
    return;
  }

  if (btn.disabled) return;
  btn.disabled = true;
  txt.textContent = 'REQUESTING TABLE...';
  spn.style.display = 'inline';

  let isSuccess = false;

  try {
    saveBookingDraft();
    const formData = new FormData(document.getElementById('avelineBookingForm'));
    const res = await fetch('<?= url('/bookings') ?>', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    });

    const data = await res.json();
    if (data.require_login) {
      if (formAlert) {
        formAlert.textContent = data.message || 'Please sign in to reserve your table.';
        formAlert.style.display = 'block';
      }
      showAvelineToast(data.message || 'Please sign in to reserve your table.');
      setTimeout(() => {
        window.location.href = data.redirect || '<?= url('/auth/login?redirect=' . urlencode('/#contact')) ?>';
      }, 800);
      return;
    }

    if (data.success) {
      isSuccess = true;
      sessionStorage.removeItem('aveline_booking_draft');
      txt.textContent = 'RESERVATION CONFIRMED! REDIRECTING...';
      showAvelineToast('TABLE REQUEST RECEIVED! ' + data.message);
      const targetUrl = (data.id && data.tracking_token)
        ? '<?= url('/bookings/view?id=') ?>' + data.id + '&token=' + encodeURIComponent(data.tracking_token)
        : (data.tracking_token ? '<?= url('/track/') ?>' + data.tracking_token : '<?= url('/my-bookings') ?>');
      // Instant redirect to avoid duplicate submissions
      window.location.href = targetUrl;
      return;
    } else {
      const msg = data.message || data.error || 'Booking could not be finalized.';
      if (formAlert) {
        formAlert.textContent = msg;
        formAlert.style.display = 'block';
      }
      showAvelineToast('ERROR: ' + msg);
    }
  } catch(err) {
    console.error('Reservation submission error:', err);
    if (formAlert) {
      formAlert.textContent = 'Network error while requesting table. Please check your connection.';
      formAlert.style.display = 'block';
    }
    showAvelineToast('Network error. Please try again.');
  } finally {
    if (!isSuccess) {
      btn.disabled = false;
      txt.textContent = 'REQUEST A TABLE';
      spn.style.display = 'none';
    }
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

let currentGbFilter = 'all';

function filterGuestbook(type, btn) {
  currentGbFilter = type;
  document.querySelectorAll('.gb-tab-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');

  const cards = document.querySelectorAll('.guestbook-card');
  cards.forEach(c => {
    const matchesType = (type === 'all' || c.getAttribute('data-type') === type);
    c.style.display = matchesType ? 'flex' : 'none';
  });

  const grid = document.getElementById('guestbookCardsGrid');
  if (grid) {
    grid.scrollTo({ left: 0, behavior: 'smooth' });
  }
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
        grid.scrollTo({ left: 0, behavior: 'smooth' });

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

// ─── 7. MOBILE NAVIGATION DRAWER TOGGLE ───
function toggleMobileNav() {
  const drawer = document.getElementById('mobileNavDrawer');
  const btn = document.getElementById('mobileNavToggle');
  if (!drawer || !btn) return;
  drawer.classList.toggle('open');
  btn.classList.toggle('open');
}

function closeMobileNav() {
  const drawer = document.getElementById('mobileNavDrawer');
  const btn = document.getElementById('mobileNavToggle');
  if (drawer) drawer.classList.remove('open');
  if (btn) btn.classList.remove('open');
}

document.addEventListener('click', (e) => {
  const drawer = document.getElementById('mobileNavDrawer');
  const btn = document.getElementById('mobileNavToggle');
  if (drawer && drawer.classList.contains('open')) {
    if (!drawer.contains(e.target) && !btn.contains(e.target)) {
      closeMobileNav();
    }
  }
});
</script>
