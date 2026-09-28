<!-- EXPERIENCES BEYOND THE TABLE (SLIDEBAR) -->
<section class="aveline-section" id="experiences">
  <div class="section-header-flex">
    <div>
      <span class="section-eyebrow">DINING EXPERIENCES</span>
      <h2 class="section-serif-title">EXPERIENCES BEYOND THE TABLE</h2>
      <p class="section-desc">
        From intimate course-by-course seasonal dinners to grand celebrations, every evening is orchestrated with genuine warmth and culinary precision.
      </p>
    </div>
    <div class="slider-controls">
      <button type="button" class="slider-arrow" onclick="slideTrack('experiencesTrack', -1)" aria-label="Previous Experience">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </button>
      <button type="button" class="slider-arrow" onclick="slideTrack('experiencesTrack', 1)" aria-label="Next Experience">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
      </button>
    </div>
  </div>

  <div class="slider-container">
    <div class="slider-track experiences-slider-track" id="experiencesTrack">
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
  </div>

  <div class="slider-indicators" id="experiencesIndicators">
    <span class="indicator-dot active" onclick="jumpSlide('experiencesTrack', 0)"></span>
    <span class="indicator-dot" onclick="jumpSlide('experiencesTrack', 1)"></span>
    <span class="indicator-dot" onclick="jumpSlide('experiencesTrack', 2)"></span>
  </div>
</section>
