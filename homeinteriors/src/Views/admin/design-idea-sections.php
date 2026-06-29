<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section"><div class="container" data-reveal>
  <div class="admin-page-head"><div><p class="eyebrow">Design ideas</p><h1>Design Page Sections</h1><p class="muted-line">Manage dynamic blocks such as rooms, colours, styles, units, trending ideas, and planning tools.</p></div><a class="btn-link" href="/design-ideas" target="_blank">View page</a></div>
  <form id="designSectionForm" class="admin-card property-admin-form">
    <input type="hidden" name="id">
    <div class="budget-grid"><input name="section_key" placeholder="Section key *" required><input name="title" placeholder="Section title *" required></div>
    <textarea name="subtitle" rows="2" placeholder="Section subtitle"></textarea>
    <div class="budget-grid">
      <select name="section_type">
        <?php foreach (['category_grid','color_grid','style_grid','unit_grid','trending','lead_form','tool_cards','content','hero_tiles'] as $type): ?><option value="<?= $type ?>"><?= ucwords(str_replace('_', ' ', $type)) ?></option><?php endforeach; ?>
      </select>
      <input name="sort_order" type="number" placeholder="Sort order">
    </div>
    <textarea name="items_json" rows="12" placeholder='Items JSON, e.g. [{"title":"Kitchen","href":"/design-ideas/kitchen-designs","image":"https://...","label":"Know more"}]'></textarea>
    <div class="admin-check-row"><label><input name="is_active" type="checkbox" value="1" checked> Published</label></div>
    <div class="admin-links"><button class="btn-primary" type="submit">Save section</button><button class="btn-muted" type="button" id="designSectionReset">Reset</button></div>
    <p class="form-message" id="designSectionMsg"></p>
  </form>
  <div class="table-shell"><table><thead><tr><th>Section</th><th>Type</th><th>Items</th><th>Sort</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($sections as $section): ?><tr data-section='<?= htmlspecialchars(json_encode($section, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'><td><strong><?= htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small><?= htmlspecialchars((string)$section['section_key'], ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars((string)$section['section_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= count((array)$section['items_json']) ?></td><td><?= (int)$section['sort_order'] ?></td><td><?= !empty($section['is_active']) ? 'Published' : 'Draft' ?></td><td><button class="btn-link edit-design-section" type="button">Edit</button><button class="btn-link delete-design-section" type="button" data-id="<?= (int)$section['id'] ?>">Delete</button></td></tr><?php endforeach; ?><?php if (!$sections): ?><tr><td colspan="6">No sections yet.</td></tr><?php endif; ?></tbody></table></div>
</div></section>
<script>
(() => {
  const form = document.getElementById('designSectionForm');
  const msg = document.getElementById('designSectionMsg');
  function fill(item) {
    Object.entries(item).forEach(([key, value]) => {
      const input = form.elements[key];
      if (!input) return;
      if (input.type === 'checkbox') input.checked = Number(value) === 1;
      else input.value = key === 'items_json' ? JSON.stringify(value || [], null, 2) : (value ?? '');
    });
  }
  document.querySelectorAll('.edit-design-section').forEach((btn) => btn.onclick = () => { fill(JSON.parse(btn.closest('tr').dataset.section || '{}')); window.scrollTo({top:0, behavior:'smooth'}); });
  document.querySelectorAll('.delete-design-section').forEach((btn) => btn.onclick = async () => { if (!confirm('Delete this design idea section?')) return; const res = await fetch(`/api/admin/design-idea-sections/${btn.dataset.id}`, { method:'DELETE' }); if (res.ok) location.reload(); });
  document.getElementById('designSectionReset').onclick = () => { form.reset(); form.elements.id.value = ''; form.elements.is_active.checked = true; };
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const fd = new FormData(form);
    const id = fd.get('id');
    if (id) fd.set('_method', 'PUT');
    const res = await fetch(id ? `/api/admin/design-idea-sections/${id}` : '/api/admin/design-idea-sections', { method:'POST', body:fd });
    const data = await res.json();
    msg.className = `form-message ${res.ok ? 'ok' : 'error'}`;
    msg.textContent = res.ok ? 'Saved successfully.' : (data.error || 'Save failed.');
    if (res.ok) setTimeout(() => location.reload(), 500);
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
