<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section"><div class="container" data-reveal>
  <div class="admin-page-head"><div><p class="eyebrow">Design ideas</p><h1>Design Idea Backend</h1><p class="muted-line">Manage idea cards, dimensions, locations, colours, galleries, budgets, and SEO.</p></div><a class="btn-link" href="/design-ideas" target="_blank">View design ideas</a></div>
  <form id="designIdeaForm" class="admin-card property-admin-form">
    <input type="hidden" name="id">
    <div class="budget-grid"><input name="name" placeholder="Idea name *" required><input name="slug" placeholder="URL slug *" required></div>
    <div class="budget-grid"><input name="type" placeholder="Type, e.g. Kitchen *" required><input name="color" placeholder="Colour"></div>
    <div class="budget-grid"><input name="style" placeholder="Style"><input name="layout" placeholder="Layout"></div>
    <div class="budget-grid"><input name="location" placeholder="Location"><input name="city" placeholder="City"></div>
    <div class="budget-grid"><input name="state" placeholder="State"><input name="image_url" placeholder="Main image URL"></div>
    <div class="budget-grid"><input name="length_ft" type="number" step="0.01" placeholder="Length ft"><input name="breadth_ft" type="number" step="0.01" placeholder="Breadth ft"><input name="height_ft" type="number" step="0.01" placeholder="Height ft"></div>
    <div class="budget-grid"><input name="budget_min" type="number" placeholder="Budget min"><input name="budget_max" type="number" placeholder="Budget max"></div>
    <textarea name="short_description" rows="2" placeholder="Short description"></textarea>
    <textarea name="description" rows="4" placeholder="Full description"></textarea>
    <textarea name="gallery_json" rows="2" placeholder="Gallery image URLs, comma separated"></textarea>
    <textarea name="tags_json" rows="2" placeholder="Tags, comma separated"></textarea>
    <input name="meta_title" placeholder="Meta title">
    <textarea name="meta_description" rows="2" placeholder="Meta description"></textarea>
    <div class="admin-check-row"><label><input name="is_featured" type="checkbox" value="1"> Featured</label><label><input name="is_active" type="checkbox" value="1" checked> Published</label></div>
    <div class="admin-links"><button class="btn-primary" type="submit">Save idea</button><button class="btn-muted" type="button" id="designIdeaReset">Reset</button></div>
    <p class="form-message" id="designIdeaMsg"></p>
  </form>
  <div class="table-shell"><table><thead><tr><th>Name</th><th>Type</th><th>Location</th><th>Colour</th><th>Dimensions</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($ideas as $idea): ?><tr data-idea='<?= htmlspecialchars(json_encode($idea, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'><td><?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$idea['type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(trim((string)$idea['location'] . ', ' . (string)$idea['city'], ', '), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$idea['color'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (float)$idea['length_ft'] ?> x <?= (float)$idea['breadth_ft'] ?> x <?= (float)$idea['height_ft'] ?> ft</td><td><?= !empty($idea['is_active']) ? 'Published' : 'Draft' ?></td><td><button class="btn-link edit-design-idea" type="button">Edit</button><button class="btn-link delete-design-idea" type="button" data-id="<?= (int)$idea['id'] ?>">Delete</button></td></tr><?php endforeach; ?></tbody></table></div>
</div></section>
<script>
(() => {
  const form = document.getElementById('designIdeaForm');
  const msg = document.getElementById('designIdeaMsg');
  const csv = (value) => Array.isArray(value) ? value.join(', ') : String(value || '');
  function fill(item) {
    Object.entries(item).forEach(([key, value]) => {
      const input = form.elements[key];
      if (!input) return;
      if (input.type === 'checkbox') input.checked = Number(value) === 1;
      else input.value = ['gallery_json','tags_json'].includes(key) ? csv(value) : (value ?? '');
    });
  }
  document.querySelectorAll('.edit-design-idea').forEach(btn => btn.onclick = () => { fill(JSON.parse(btn.closest('tr').dataset.idea || '{}')); window.scrollTo({top:0, behavior:'smooth'}); });
  document.querySelectorAll('.delete-design-idea').forEach(btn => btn.onclick = async () => { if (!confirm('Delete this design idea?')) return; const res = await fetch(`/api/admin/design-ideas/${btn.dataset.id}`, {method:'DELETE'}); if (res.ok) location.reload(); });
  document.getElementById('designIdeaReset').onclick = () => { form.reset(); form.elements.id.value = ''; form.elements.is_active.checked = true; };
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const fd = new FormData(form);
    const id = fd.get('id');
    if (id) fd.set('_method', 'PUT');
    const res = await fetch(id ? `/api/admin/design-ideas/${id}` : '/api/admin/design-ideas', {method:'POST', body:fd});
    const data = await res.json();
    msg.className = `form-message ${res.ok ? 'ok' : 'error'}`;
    msg.textContent = res.ok ? 'Saved successfully.' : (data.error || 'Save failed.');
    if (res.ok) setTimeout(() => location.reload(), 500);
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
