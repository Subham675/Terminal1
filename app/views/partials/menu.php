<!-- SIGNATURE MENU & THE TASTE (CREAM WRAP) -->
<section class="aveline-section menu-cream-wrap" id="menu">
  <div class="section-header-flex">
    <div>
      <span class="section-eyebrow" style="color:var(--gold-accent);">FEATURED MENU</span>
      <h2 class="section-serif-title dark">A CURATED EXPRESSION OF THE SEASON</h2>
      <p class="section-desc" style="color:var(--text-dark-muted);">
        Discover a selection of signature dishes thoughtfully composed to reflect authentic flavors, pristine spices, and artisanal craft.
      </p>
    </div>
    <div class="slider-controls">
      <button type="button" class="slider-arrow dark" onclick="slideTrack('dishGrid', -1)" aria-label="Previous Dishes">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </button>
      <button type="button" class="slider-arrow dark" onclick="slideTrack('dishGrid', 1)" aria-label="Next Dishes">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
      </button>
    </div>
  </div>

  <!-- Filter Buttons -->
  <div class="menu-category-filter">
    <button class="menu-cat-btn active" data-cat="all">ALL SELECTIONS</button>
    <button class="menu-cat-btn" data-cat="signature">SIGNATURE CUTS</button>
    <button class="menu-cat-btn" data-cat="roasts">CLAY OVEN ROASTS</button>
    <button class="menu-cat-btn" data-cat="asian">ASIAN &amp; DUMPLINGS</button>
    <button class="menu-cat-btn" data-cat="rice">RICE &amp; POLAO</button>
  </div>

  <!-- Dishes Slidebar Track -->
  <div class="slider-container">
    <div class="aveline-dish-slider-track" id="dishGrid">

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
  </div>
</section>
