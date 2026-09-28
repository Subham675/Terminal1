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

    <!-- Segment Filter Tabs & Slidebar Controls -->
    <div class="guestbook-tabs-and-slider">
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

      <div class="slider-controls">
        <button type="button" class="slider-arrow" onclick="slideTrack('guestbookCardsGrid', -1)" aria-label="Previous Reflection">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button type="button" class="slider-arrow" onclick="slideTrack('guestbookCardsGrid', 1)" aria-label="Next Reflection">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Slidebar Track -->
  <div class="slider-container">
    <div class="guestbook-slider-track" id="guestbookCardsGrid">
      <?php if (empty($reviewsList)): ?>
        <div style="width: 100%; min-width: 280px; padding: 36px 20px; text-align: center; color: var(--text-light-muted); border: 1px dashed rgba(255,255,255,0.1); border-radius: 2px;">
          <p style="font-family: var(--font-serif); font-size: 1.25rem; color: #fff; margin-bottom: 6px;">Be the First to Inscribe Your Memory</p>
          <p style="font-size: 0.84rem; max-width: 480px; margin: 0 auto 16px;">Share your dining impressions, culinary favorites, or constructive feedback with our team.</p>
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
  </div>
</section>
