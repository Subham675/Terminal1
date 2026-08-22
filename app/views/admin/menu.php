<?php $pageTitle='Menu Management'; $activePage='menu'; require APP_ROOT.'/app/views/layouts/admin_layout.php'; ?>
<?php $f=flash('menu'); if($f): ?>
  <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
<?php endif; ?>

<div style="margin-bottom:20px">
  <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">+ Add Menu Item</button>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">All Menu Items (<?= count($items) ?>)</div></div>
  <table>
    <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Badge</th><th>Veg</th><th>Available</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($items as $item): ?>
    <tr>
      <td><?= e($item['name']) ?></td>
      <td><?= e($item['category_name'] ?? '—') ?></td>
      <td>₹<?= number_format($item['price'],2) ?></td>
      <td><?= $item['badge'] ? '<span class="badge badge-confirmed">'.e($item['badge']).'</span>' : '—' ?></td>
      <td><?= $item['is_veg']?'<span style="color:#2da44e">🥦</span>':'<span style="color:#cf222e">🍗</span>' ?></td>
      <td><?= $item['is_available']?'<span style="color:#2da44e">Yes</span>':'<span style="color:#cf222e">No</span>' ?></td>
      <td style="display:flex;gap:6px">
        <button class="btn btn-sm btn-primary" onclick='openEdit(<?= json_encode($item) ?>)'>Edit</button>
        <form method="POST" action="/admin/menu/delete" onsubmit="return confirm('Delete item?')">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="id" value="<?= $item['id'] ?>">
          <button class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <div class="modal-title">Add Menu Item</div>
    <form method="POST" action="/admin/menu/create">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-grid">
        <div class="form-group">
          <label>Name *</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Price (₹) *</label>
          <input type="number" name="price" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Category</label>
          <select name="category_id" class="form-control">
            <option value="">Select category</option>
            <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Badge (e.g. New, Signature)</label>
          <input type="text" name="badge" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="2"></textarea>
      </div>
      <div style="display:flex;gap:20px;margin-bottom:16px">
        <label style="color:rgba(255,255,255,.6);font-size:.85rem;display:flex;align-items:center;gap:6px">
          <input type="checkbox" name="is_veg"> Vegetarian
        </label>
        <label style="color:rgba(255,255,255,.6);font-size:.85rem;display:flex;align-items:center;gap:6px">
          <input type="checkbox" name="is_available" checked> Available
        </label>
      </div>
      <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-primary">Add Item</button>
        <button type="button" class="btn" style="border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5)" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
  <div class="modal-box">
    <div class="modal-title">Edit Menu Item</div>
    <form method="POST" action="/admin/menu/update">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="id" id="edit_id">
      <div class="form-grid">
        <div class="form-group">
          <label>Name *</label>
          <input type="text" name="name" id="edit_name" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Price (₹) *</label>
          <input type="number" name="price" id="edit_price" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Category</label>
          <select name="category_id" id="edit_category" class="form-control">
            <option value="">Select category</option>
            <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Badge</label>
          <input type="text" name="badge" id="edit_badge" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
      </div>
      <div style="display:flex;gap:20px;margin-bottom:16px">
        <label style="color:rgba(255,255,255,.6);font-size:.85rem;display:flex;align-items:center;gap:6px">
          <input type="checkbox" name="is_veg" id="edit_is_veg"> Vegetarian
        </label>
        <label style="color:rgba(255,255,255,.6);font-size:.85rem;display:flex;align-items:center;gap:6px">
          <input type="checkbox" name="is_available" id="edit_is_available"> Available
        </label>
      </div>
      <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn" style="border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5)" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(item){
  document.getElementById('edit_id').value = item.id;
  document.getElementById('edit_name').value = item.name;
  document.getElementById('edit_price').value = item.price;
  document.getElementById('edit_category').value = item.category_id || '';
  document.getElementById('edit_badge').value = item.badge || '';
  document.getElementById('edit_description').value = item.description || '';
  document.getElementById('edit_is_veg').checked = item.is_veg === true || item.is_veg === 't';
  document.getElementById('edit_is_available').checked = item.is_available === true || item.is_available === 't';
  document.getElementById('editModal').classList.add('open');
}
</script>

<?php require APP_ROOT.'/app/views/layouts/admin_footer.php'; ?>
