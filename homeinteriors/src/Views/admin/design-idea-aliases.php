<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section"><div class="container" data-reveal>
  <div class="admin-page-head"><div><p class="eyebrow">Design idea aliases</p><h1>Aliased Page Content Backend</h1><p class="muted-line">Manage SEO pages like /design-ideas/kitchen-designs with filters, copy, hero image, and meta data.</p></div><a class="btn-link" href="/admin/design-ideas">Manage ideas</a></div>
  <form id="designAliasForm" class="admin-card property-admin-form">
    <input type="hidden" name="id">
    <div class="budget-grid"><input name="title" placeholder="Page title *" required><input name="slug" placeholder="Alias slug *" required></div>
    <textarea name="subtitle" rows="2" placeholder="Subtitle"></textarea>
    <input name="hero_image" placeholder="Hero image URL">
    <div class="budget-grid"><input name="filter_type" placeholder="Filter type"><input name="filter_color" placeholder="Filter colour"><input name="filter_city" placeholder="Filter city"></div>
    <div class="budget-grid"><input name="filter_state" placeholder="Filter state"><input name="filter_style" placeholder="Filter style"><input name="filter_layout" placeholder="Filter layout"></div>
    <textarea name="intro_content" rows="4" placeholder="Intro content"></textarea>
    <textarea name="outro_content" rows="4" placeholder="Outro SEO content"></textarea>
    <input name="meta_title" placeholder="Meta title">
    <textarea name="meta_description" rows="2" placeholder="Meta description"></textarea>
    <div class="admin-check-row"><label><input name="is_active" type="checkbox" value="1" checked> Published</label></div>
    <div class="admin-links"><button class="btn-primary" type="submit">Save alias page</button><button class="btn-muted" type="button" id="designAliasReset">Reset</button></div>
    <p class="form-message" id="designAliasMsg"></p>
  </form>
  <div class="table-shell"><table><thead><tr><th>Page</th><th>Slug</th><th>Filters</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($aliases as $alias): ?><tr data-alias='<?= htmlspecialchars(json_encode($alias, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'><td><?= htmlspecialchars((string)$alias['title'], ENT_QUOTES, 'UTF-8') ?></td><td>/design-ideas/<?= htmlspecialchars((string)$alias['slug'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(trim(implode(' / ', array_filter([(string)$alias['filter_type'], (string)$alias['filter_color'], (string)$alias['filter_city'], (string)$alias['filter_style'], (string)$alias['filter_layout']])), ' /'), ENT_QUOTES, 'UTF-8') ?></td><td><?= !empty($alias['is_active']) ? 'Published' : 'Draft' ?></td><td><button class="btn-link edit-design-alias" type="button">Edit</button><button class="btn-link delete-design-alias" type="button" data-id="<?= (int)$alias['id'] ?>">Delete</button></td></tr><?php endforeach; ?></tbody></table></div>
</div></section>
<script>
(() => {
  const form = document.getElementById('designAliasForm');
  const msg = document.getElementById('designAliasMsg');
  function fill(item) { Object.entries(item).forEach(([key, value]) => { const input = form.elements[key]; if (!input) return; if (input.type === 'checkbox') input.checked = Number(value) === 1; else input.value = value ?? ''; }); }
  document.querySelectorAll('.edit-design-alias').forEach(btn => btn.onclick = () => { fill(JSON.parse(btn.closest('tr').dataset.alias || '{}')); window.scrollTo({top:0, behavior:'smooth'}); });
  document.querySelectorAll('.delete-design-alias').forEach(btn => btn.onclick = async () => { if (!confirm('Delete this alias page?')) return; const res = await fetch(`/api/admin/design-idea-aliases/${btn.dataset.id}`, {method:'DELETE'}); if (res.ok) location.reload(); });
  document.getElementById('designAliasReset').onclick = () => { form.reset(); form.elements.id.value = ''; form.elements.is_active.checked = true; };
  form.addEventListener('submit', async (event) => { event.preventDefault(); const fd = new FormData(form); const id = fd.get('id'); if (id) fd.set('_method','PUT'); const res = await fetch(id ? `/api/admin/design-idea-aliases/${id}` : '/api/admin/design-idea-aliases', {method:'POST', body:fd}); const data = await res.json(); msg.className = `form-message ${res.ok ? 'ok':'error'}`; msg.textContent = res.ok ? 'Saved successfully.' : (data.error || 'Save failed.'); if (res.ok) setTimeout(() => location.reload(), 500); });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
