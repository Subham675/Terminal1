<?php 
$pageTitle = 'Kitchen Menu'; 
$activePage = 'menu'; 
require APP_ROOT . '/app/views/layouts/admin_layout.php'; 

$totalItems = count($items);
$vegCount = 0;
$badgedCount = 0;
$availableCount = 0;

foreach ($items as $item) {
    if (!empty($item['is_veg'])) $vegCount++;
    if (!empty($item['badge'])) $badgedCount++;
    if (!empty($item['is_available'])) $availableCount++;
}
$catCount = count($categories);
?>

<?php $f = flash('menu'); if ($f): ?>
  <div class="flash flash-<?= $f['type'] ?>">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <?php if ($f['type'] === 'success'): ?>
        <polyline points="20 6 9 17 4 12"/>
      <?php else: ?>
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      <?php endif; ?>
    </svg>
    <span><?= e($f['message']) ?></span>
  </div>
<?php endif; ?>

<!-- TOP METRIC CARDS -->
<div class="kpi-grid" style="margin-bottom:24px;">
  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Kitchen Catalog</span>
      <div class="kpi-icon-wrap" style="color:var(--gold-lt);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2v20"/><path d="M21 15V2v0a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3v7"/><path d="M8 2v20"/><path d="M4 2v7a4 4 0 0 0 8 0V2"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;"><?= $totalItems ?> <span style="font-size:0.95rem;font-weight:400;color:var(--text-dim)">Dishes</span></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-green"><?= $availableCount ?> Active for Order</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Categories</span>
      <div class="kpi-icon-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;"><?= $catCount ?> <span style="font-size:0.95rem;font-weight:400;color:var(--text-dim)">Sections</span></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">Organized Menu Architecture</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Vegetarian Selection</span>
      <div class="kpi-icon-wrap" style="color:var(--success);background:var(--success-bg);border-color:rgba(63,185,80,0.25);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;color:var(--success);"><?= $vegCount ?> <span style="font-size:0.95rem;font-weight:400;color:var(--text-dim)">Pure Veg</span></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral"><?= $totalItems - $vegCount ?> Meat &amp; Seafood</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Signature Dishes</span>
      <div class="kpi-icon-wrap" style="color:var(--gold-lt);background:var(--gold-glow);border-color:var(--border-gold);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;color:var(--gold-lt);"><?= $badgedCount ?> <span style="font-size:0.95rem;font-weight:400;color:var(--text-dim)">Badged</span></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">Chef's Highlights</span>
    </div>
  </div>
</div>

<!-- MENU ITEMS CARD -->
<div class="card">
  <div class="card-header" style="gap:16px;">
    <div>
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2v20"/><path d="M21 15V2v0a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3v7"/><path d="M8 2v20"/><path d="M4 2v7a4 4 0 0 0 8 0V2"/></svg>
        Master Culinary Catalog
      </div>
      <div style="font-size:0.78rem;color:var(--text-dim);margin-top:3px;">
        Curate culinary dishes, price points, dietary indicators, and active storefront availability.
      </div>
    </div>

    <!-- Controls -->
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <div style="position:relative;">
        <input type="text" id="menuSearch" placeholder="Search dishes or ingredients…" oninput="filterMenu()" style="background:rgba(255,255,255,0.04);border:1px solid var(--border);color:#fff;padding:8px 12px 8px 32px;border-radius:6px;font-size:0.82rem;width:220px;outline:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-dim)" stroke-width="2" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
      </div>

      <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Culinary Dish
      </button>
    </div>
  </div>

  <!-- Category Pills Filter -->
  <div style="padding:12px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <span style="font-size:0.72rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);font-weight:700;margin-right:4px;">Filter:</span>
    <button class="menu-cat-pill active" data-cat="all" onclick="setMenuCategory('all', this)">All Categories</button>
    <?php foreach($categories as $c): ?>
      <button class="menu-cat-pill" data-cat="<?= $c['id'] ?>" onclick="setMenuCategory('<?= $c['id'] ?>', this)"><?= e($c['name']) ?></button>
    <?php endforeach; ?>
  </div>

  <style>
    .menu-cat-pill{background:rgba(255,255,255,0.03);border:1px solid var(--border);color:var(--text-muted);font-size:0.75rem;font-weight:600;padding:5px 12px;border-radius:20px;cursor:pointer;transition:all .15s;white-space:nowrap;font-family:var(--font-body)}
    .menu-cat-pill:hover{color:#fff;border-color:var(--border-gold)}
    .menu-cat-pill.active{background:rgba(200,134,10,0.18);color:var(--gold-lt);border-color:var(--border-gold)}
    .dish-thumb{width:48px;height:48px;border-radius:6px;object-fit:cover;border:1px solid var(--border);background:#161411;display:flex;align-items:center;justify-content:center;color:var(--text-dim);flex-shrink:0}
    .badge-diet-veg{display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:4px;font-size:0.7rem;font-weight:700;background:rgba(63,185,80,0.12);color:#3fb950;border:1px solid rgba(63,185,80,0.3)}
    .badge-diet-nonveg{display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:4px;font-size:0.7rem;font-weight:700;background:rgba(248,81,73,0.12);color:#f85149;border:1px solid rgba(248,81,73,0.3)}
    .badge-signature{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:4px;font-size:0.7rem;font-weight:700;background:rgba(200,134,10,0.15);color:var(--gold-lt);border:1px solid var(--border-gold)}
  </style>

  <div class="table-responsive">
    <table id="menuTable">
      <thead>
        <tr>
          <th>Dish</th>
          <th>Category</th>
          <th>Price</th>
          <th>Dietary</th>
          <th>Highlights</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if(empty($items)): ?>
        <tr>
          <td colspan="7" style="padding:48px 20px;text-align:center;color:var(--text-dim);">
            No menu items defined yet. Click "Add Culinary Dish" above to create your first item.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach($items as $item): ?>
          <?php 
            $catId = (string)($item['category_id'] ?? '');
            $searchHaystack = strtolower(($item['name']??'') . ' ' . ($item['description']??'') . ' ' . ($item['category_name']??'') . ' ' . ($item['badge']??''));
          ?>
          <tr class="menu-row" data-cat="<?= $catId ?>" data-search="<?= e($searchHaystack) ?>">
            <!-- Dish -->
            <td>
              <div style="display:flex;align-items:center;gap:14px;">
                <?php if(!empty($item['image_url'])): ?>
                  <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="dish-thumb">
                <?php else: ?>
                  <div class="dish-thumb">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 2v20"/><path d="M21 15V2v0a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3v7"/><path d="M8 2v20"/><path d="M4 2v7a4 4 0 0 0 8 0V2"/></svg>
                  </div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:600;color:#fff;font-size:0.9rem;"><?= e($item['name']) ?></div>
                  <?php if(!empty($item['description'])): ?>
                    <div style="font-size:0.75rem;color:var(--text-dim);max-width:320px;line-height:1.3;margin-top:2px;" title="<?= e($item['description']) ?>">
                      <?= e(mb_strimwidth($item['description'], 0, 75, '…')) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <!-- Category -->
            <td>
              <span style="font-size:0.78rem;color:var(--text-muted);background:rgba(255,255,255,0.04);padding:4px 10px;border-radius:4px;border:1px solid var(--border);">
                <?= e($item['category_name'] ?? 'General') ?>
              </span>
            </td>

            <!-- Price -->
            <td>
              <div style="font-family:var(--font-display);font-size:1.05rem;font-weight:700;color:var(--gold-lt);">
                ₹<?= number_format((float)$item['price'], 2) ?>
              </div>
            </td>

            <!-- Dietary -->
            <td>
              <?php if(!empty($item['is_veg'])): ?>
                <span class="badge-diet-veg">
                  <span style="width:6px;height:6px;border-radius:50%;background:#3fb950;display:inline-block;"></span> Veg
                </span>
              <?php else: ?>
                <span class="badge-diet-nonveg">
                  <span style="width:6px;height:6px;border-radius:50%;background:#f85149;display:inline-block;"></span> Non-Veg
                </span>
              <?php endif; ?>
            </td>

            <!-- Badge / Highlights -->
            <td>
              <?php if(!empty($item['badge'])): ?>
                <span class="badge-signature">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  <?= e($item['badge']) ?>
                </span>
              <?php else: ?>
                <span style="color:var(--text-dim);font-size:0.75rem;">—</span>
              <?php endif; ?>
            </td>

            <!-- Availability -->
            <td>
              <?php if(!empty($item['is_available'])): ?>
                <span class="badge badge-confirmed" style="font-size:0.7rem;">Active</span>
              <?php else: ?>
                <span class="badge badge-cancelled" style="font-size:0.7rem;">Paused</span>
              <?php endif; ?>
            </td>

            <!-- Actions -->
            <td>
              <div style="display:flex;align-items:center;gap:6px;">
                <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,0.06);border:1px solid var(--border);color:var(--text);" onclick='openEdit(<?= json_encode($item) ?>)'>
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  Edit
                </button>
                <form method="POST" action="<?= url('/admin/menu/delete') ?>"
                      data-confirm="Permanently remove &quot;<?= addslashes(e($item['name'])) ?>&quot; from kitchen menu?"
                      data-title="Remove Menu Item">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" style="padding:5px 9px;" title="Delete item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD CULINARY ITEM MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px;">
      <div>
        <div class="modal-title" style="margin-bottom:2px;">Add Culinary Dish</div>
        <div style="font-size:0.75rem;color:var(--text-dim);">Publish a new signature dish to the live customer storefront.</div>
      </div>
      <button type="button" onclick="document.getElementById('addModal').classList.remove('open')" style="background:none;border:none;color:var(--text-dim);font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
    </div>

    <form method="POST" action="<?= url('/admin/menu/create') ?>" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      
      <div class="form-grid">
        <div class="form-group">
          <label>Dish Name *</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Truffle Glazed Salmon" required>
        </div>
        <div class="form-group">
          <label>Price (₹) *</label>
          <input type="number" name="price" step="0.01" class="form-control" placeholder="950.00" required>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label>Menu Category</label>
          <select name="category_id" class="form-control">
            <option value="">Select menu section</option>
            <?php foreach($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Chef's Badge</label>
          <input type="text" name="badge" class="form-control" placeholder="e.g. Chef's Pick, Signature">
        </div>
      </div>

      <div class="form-group">
        <label>Dish Photography (JPG, PNG, WebP · Max 3MB)</label>
        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
      </div>

      <div class="form-group">
        <label>Culinary Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Artisanal preparation notes, tasting notes, and key ingredients…"></textarea>
      </div>

      <div style="display:flex;align-items:center;gap:24px;padding:12px 14px;background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:6px;margin-bottom:20px;">
        <label style="color:#fff;font-size:0.84rem;display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="is_veg" style="accent-color:var(--success);width:16px;height:16px;"> 
          Pure Vegetarian
        </label>
        <label style="color:#fff;font-size:0.84rem;display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="is_available" checked style="accent-color:var(--gold);width:16px;height:16px;"> 
          Active &amp; Available
        </label>
      </div>

      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button type="button" class="btn" style="background:none;border:1px solid var(--border);color:var(--text-muted);" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Dish</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT CULINARY ITEM MODAL -->
<div class="modal" id="editModal">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px;">
      <div>
        <div class="modal-title" style="margin-bottom:2px;">Edit Culinary Dish</div>
        <div style="font-size:0.75rem;color:var(--text-dim);">Update recipe details, pricing, or status in real time.</div>
      </div>
      <button type="button" onclick="document.getElementById('editModal').classList.remove('open')" style="background:none;border:none;color:var(--text-dim);font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
    </div>

    <form method="POST" action="<?= url('/admin/menu/update') ?>" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="id" id="edit_id">

      <div class="form-grid">
        <div class="form-group">
          <label>Dish Name *</label>
          <input type="text" name="name" id="edit_name" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Price (₹) *</label>
          <input type="number" name="price" id="edit_price" step="0.01" class="form-control" required>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label>Menu Category</label>
          <select name="category_id" id="edit_category" class="form-control">
            <option value="">Select menu section</option>
            <?php foreach($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Chef's Badge</label>
          <input type="text" name="badge" id="edit_badge" class="form-control">
        </div>
      </div>

      <div class="form-group">
        <label>Replace Dish Photography (Optional)</label>
        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
      </div>

      <div class="form-group">
        <label>Culinary Description</label>
        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
      </div>

      <div style="display:flex;align-items:center;gap:24px;padding:12px 14px;background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:6px;margin-bottom:20px;">
        <label style="color:#fff;font-size:0.84rem;display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="is_veg" id="edit_is_veg" style="accent-color:var(--success);width:16px;height:16px;"> 
          Pure Vegetarian
        </label>
        <label style="color:#fff;font-size:0.84rem;display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="is_available" id="edit_is_available" style="accent-color:var(--gold);width:16px;height:16px;"> 
          Active &amp; Available
        </label>
      </div>

      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button type="button" class="btn" style="background:none;border:1px solid var(--border);color:var(--text-muted);" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
let currentCategory = 'all';

function setMenuCategory(catId, btn) {
  currentCategory = catId;
  document.querySelectorAll('.menu-cat-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  filterMenu();
}

function filterMenu() {
  const query = (document.getElementById('menuSearch').value || '').trim().toLowerCase();
  const rows = document.querySelectorAll('.menu-row');

  rows.forEach(row => {
    const rowCat = row.getAttribute('data-cat') || '';
    const rowSearch = row.getAttribute('data-search') || '';

    const matchesCat = (currentCategory === 'all' || rowCat === currentCategory);
    const matchesSearch = (!query || rowSearch.includes(query));

    if (matchesCat && matchesSearch) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

function openEdit(item) {
  document.getElementById('edit_id').value = item.id;
  document.getElementById('edit_name').value = item.name;
  document.getElementById('edit_price').value = item.price;
  document.getElementById('edit_category').value = item.category_id || '';
  document.getElementById('edit_badge').value = item.badge || '';
  document.getElementById('edit_description').value = item.description || '';
  document.getElementById('edit_is_veg').checked = (item.is_veg === true || item.is_veg === 1 || item.is_veg === '1' || item.is_veg === 't');
  document.getElementById('edit_is_available').checked = (item.is_available === true || item.is_available === 1 || item.is_available === '1' || item.is_available === 't');
  document.getElementById('editModal').classList.add('open');
}
</script>

<?php require APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
