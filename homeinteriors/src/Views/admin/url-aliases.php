<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section"><div class="container" data-reveal>
  <div class="admin-page-head"><div><p class="eyebrow">Global aliases</p><h1>Website URL Alias Manager</h1><p class="muted-line">Manage every public URL from one place: meta title, meta description, H1, rich content, image, canonical and robots.</p></div><a class="btn-link" href="/sitemap.xml" target="_blank">View sitemap</a></div>
  <form id="urlAliasForm" class="admin-card property-admin-form" enctype="multipart/form-data">
    <input type="hidden" name="id">
    <input type="hidden" name="current_image_url">
    <div class="budget-grid"><input name="path" placeholder="/design-ideas/kitchen-designs *" required><input name="h1" placeholder="H1"></div>
    <div class="budget-grid"><input name="page_type" placeholder="Page type"><input name="source" placeholder="Source" value="manual"></div>
    <div class="budget-grid"><input name="entity_table" placeholder="Entity table"><input name="entity_id" type="number" placeholder="Entity ID"></div>
    <input name="meta_title" placeholder="Meta title">
    <textarea name="meta_description" rows="2" placeholder="Meta description"></textarea>
    <div class="alias-editor-toolbar">
      <button type="button" data-command="bold"><strong>B</strong></button>
      <button type="button" data-command="italic"><em>I</em></button>
      <button type="button" data-command="insertUnorderedList">• List</button>
      <button type="button" data-command="formatBlock" data-value="h2">H2</button>
      <button type="button" data-command="formatBlock" data-value="p">P</button>
    </div>
    <div id="urlAliasEditor" class="alias-rich-editor" contenteditable="true" aria-label="Rich page content"></div>
    <textarea name="content_html" id="urlAliasContent" rows="8" hidden></textarea>
    <div class="budget-grid"><input name="canonical_url" placeholder="Canonical URL/path"><input name="robots" placeholder="robots, e.g. index,follow"></div>
    <div class="budget-grid"><input name="image_url" placeholder="Image URL"><input name="image_file" type="file" accept="image/*"></div>
    <div class="admin-check-row"><label><input name="is_active" type="checkbox" value="1" checked> Active / indexable</label></div>
    <div class="admin-links"><button class="btn-primary" type="submit">Save URL alias</button><button class="btn-muted" type="button" id="urlAliasReset">Reset</button></div>
    <p class="form-message" id="urlAliasMsg"></p>
  </form>
  <div class="table-shell"><table><thead><tr><th>URL</th><th>SEO</th><th>Source</th><th>Image</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($aliases as $alias): ?><tr data-alias='<?= htmlspecialchars(json_encode($alias, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'><td><strong><?= htmlspecialchars((string)$alias['path'], ENT_QUOTES, 'UTF-8') ?></strong><br><small><?= htmlspecialchars((string)$alias['page_type'], ENT_QUOTES, 'UTF-8') ?> <?= !empty($alias['entity_table']) ? '· ' . htmlspecialchars((string)$alias['entity_table'], ENT_QUOTES, 'UTF-8') . '#' . (int)$alias['entity_id'] : '' ?></small></td><td><?= htmlspecialchars((string)($alias['meta_title'] ?: $alias['h1']), ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars(mb_strimwidth((string)$alias['meta_description'], 0, 100, '...'), ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars((string)$alias['source'], ENT_QUOTES, 'UTF-8') ?></td><td><?php if (!empty($alias['image_url'])): ?><img class="alias-thumb" src="<?= htmlspecialchars((string)$alias['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php endif; ?></td><td><?= !empty($alias['is_active']) ? 'Active' : 'Draft' ?></td><td><button class="btn-link edit-url-alias" type="button">Edit</button><button class="btn-link delete-url-alias" type="button" data-id="<?= (int)$alias['id'] ?>">Delete</button></td></tr><?php endforeach; ?><?php if (!$aliases): ?><tr><td colspan="6">No URL aliases yet.</td></tr><?php endif; ?></tbody></table></div>
</div></section>
<script>
(() => {
  const form = document.getElementById('urlAliasForm');
  const editor = document.getElementById('urlAliasEditor');
  const hidden = document.getElementById('urlAliasContent');
  const msg = document.getElementById('urlAliasMsg');
  document.querySelectorAll('.alias-editor-toolbar button').forEach((button) => button.addEventListener('click', () => {
    document.execCommand(button.dataset.command, false, button.dataset.value || null);
    editor.focus();
  }));
  function fill(item) {
    Object.entries(item).forEach(([key, value]) => {
      const input = form.elements[key];
      if (!input) return;
      if (input.type === 'checkbox') input.checked = Number(value) === 1;
      else input.value = value ?? '';
    });
    form.elements.current_image_url.value = item.image_url || '';
    editor.innerHTML = item.content_html || '';
  }
  document.querySelectorAll('.edit-url-alias').forEach((btn) => btn.onclick = () => { fill(JSON.parse(btn.closest('tr').dataset.alias || '{}')); window.scrollTo({top:0, behavior:'smooth'}); });
  document.querySelectorAll('.delete-url-alias').forEach((btn) => btn.onclick = async () => { if (!confirm('Delete this URL alias?')) return; const res = await fetch(`/api/admin/url-aliases/${btn.dataset.id}`, { method:'DELETE' }); if (res.ok) location.reload(); });
  document.getElementById('urlAliasReset').onclick = () => { form.reset(); form.elements.id.value = ''; editor.innerHTML = ''; form.elements.is_active.checked = true; };
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    hidden.value = editor.innerHTML;
    const fd = new FormData(form);
    const id = fd.get('id');
    if (id) fd.set('_method', 'PUT');
    const res = await fetch(id ? `/api/admin/url-aliases/${id}` : '/api/admin/url-aliases', { method:'POST', body:fd });
    const data = await res.json();
    msg.className = `form-message ${res.ok ? 'ok' : 'error'}`;
    msg.textContent = res.ok ? 'Saved successfully.' : (data.error || 'Save failed.');
    if (res.ok) setTimeout(() => location.reload(), 600);
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
