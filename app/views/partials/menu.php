<?php
// Ensure $categoriesList and $allItems are loaded
$categoriesList = [];
try {
    $categoriesList = MenuItem::categories();
} catch (\Throwable $e) {}

if (empty($allItems)) {
    try {
        $allItems = MenuItem::all();
    } catch (\Throwable $e) {}
}
?>
<!-- SIGNATURE MENU & THE TASTE (CREAM WRAP) -->
<section class="aveline-section menu-cream-wrap" id="menu">
  <div class="section-header-flex">
    <div>
      <span class="section-eyebrow" style="color:var(--gold-accent);">ARTISANAL DINING CATALOG</span>
      <h2 class="section-serif-title dark">EXPLORE THE COMPLETE GASTRONOMY</h2>
      <p class="section-desc" style="color:var(--text-dark-muted);">
        Every creation is crafted with authentic heritage spices, time-honored recipes, and pristine regional produce.
      </p>
    </div>
  </div>

  <!-- Real-Time Search Bar -->
  <div class="menu-search-wrapper">
    <div class="menu-search-inner">
      <div class="menu-search-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
      <input type="text" id="menuSearchInput" class="menu-search-input" placeholder="Search dishes, ingredients (e.g. chicken, biryani, paneer, roll, rice)..." autocomplete="off" />
      <button type="button" id="menuSearchClear" class="menu-search-clear" onclick="clearMenuSearch()" title="Clear search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    <div class="menu-search-meta">
      <span id="menuSearchCount"><?= count($allItems) ?> dishes available</span>
      <span style="font-size:0.72rem; color:var(--text-dark-dim);">Swipe / drag horizontally to browse</span>
    </div>
  </div>

  <!-- Category Filter Buttons (Horizontal Slidebar) -->
  <div class="menu-category-filter" id="menuCatFilter">
    <button class="menu-cat-btn active" data-cat="all">ALL SELECTIONS (<?= count($allItems) ?>)</button>
    <?php foreach ($categoriesList as $c): ?>
      <button class="menu-cat-btn" data-cat="<?= e($c['slug']) ?>">
        <?= strtoupper(e($c['name'])) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Dishes Slidebar Track -->
  <div class="slider-container">
    <div class="aveline-dish-slider-track" id="dishGrid">

    <?php foreach ($allItems as $item): 
        $catSlug = $item['category_slug'] ?? '';
        if (empty($catSlug)) {
            $catSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $item['category_name'] ?? 'other')));
        }
        $imgSrc = getDishImage($item['name'], $item['image_url'] ?? null);
        $priceFloat = (float)$item['price'];
        $priceDisplay = '₹' . (fmod($priceFloat, 1) === 0.0 ? number_format($priceFloat, 0) : number_format($priceFloat, 2));
        $desc = $item['description'] ?? '';
        $dietLabel = !empty($item['is_veg']) ? 'Pure Veg' : 'Non-Veg';
        $dietColor = !empty($item['is_veg']) ? '#166534' : '#B91C1C';
    ?>
    <div class="aveline-dish-card" 
         data-category="<?= e($catSlug) ?>" 
         data-name="<?= e($item['name']) ?>" 
         data-tags="<?= e($item['search_tags'] ?? '') ?>" 
         data-desc="<?= e($desc) ?>">
      <div class="dish-photo-frame">
        <img src="<?= e($imgSrc) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
        <?php if (!empty($item['badge'])): ?>
          <span class="menu-badge-pill"><?= e($item['badge']) ?></span>
        <?php endif; ?>
        <span class="dish-diet-pill" style="color:<?= $dietColor ?>;"><?= $dietLabel ?></span>
      </div>
      <div class="dish-info-box">
        <div class="dish-title-row">
          <h4 class="dish-item-name"><?= e($item['name']) ?></h4>
          <span class="dish-item-price"><?= $priceDisplay ?></span>
        </div>
        <?php if (!empty($desc)): ?>
          <p class="dish-ingredients-text"><?= e($desc) ?></p>
        <?php else: ?>
          <p class="dish-ingredients-text" style="font-style:italic; opacity:0.75;">Prepared freshly with authentic spices and artisanal culinary craft.</p>
        <?php endif; ?>
        <div class="dish-card-footer">
          <button type="button" class="btn-dish-inspect" 
                  onclick="openDishDetail(<?= htmlspecialchars(json_encode($item['name'])) ?>, <?= htmlspecialchars(json_encode($imgSrc)) ?>, <?= htmlspecialchars(json_encode($priceDisplay)) ?>, <?= htmlspecialchars(json_encode($desc ?: $item['name'] . ' crafted with premium spices.')) ?>)">
            STORY &amp; SPECS
          </button>
          <a href="#contact" class="btn-dish-reserve" onclick="preselectDish(<?= htmlspecialchars(json_encode($item['name'])) ?>)">BOOK TABLE</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- No Results Placeholder -->
    <div id="menuNoResults" style="display:none; padding:45px 20px; text-align:center; min-width:320px; width:100%;">
      <p style="font-family:var(--font-serif); font-size:1.4rem; color:var(--text-dark); margin-bottom:8px;">No culinary creations match your search</p>
      <p style="color:var(--text-dark-muted); font-size:0.86rem; margin-bottom:16px;">Try searching for a different dish name, main ingredient, or clear the search filter.</p>
      <button type="button" class="btn-dish-inspect" style="text-decoration:underline; font-weight:700; color:var(--text-dark);" onclick="clearMenuSearch()">RESET SEARCH</button>
    </div>

    </div>
  </div>
</section>
