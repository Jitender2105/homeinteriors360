<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1><?= htmlspecialchars((string)($content['admin.content.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="muted-line">Update homepage banners, SEO fields, services, testimonials, trust points, and brand entries directly via key-value records.</p>

    <form id="contentForm" class="admin-card">
      <input name="key_name" required placeholder="key_name" />
      <select name="content_type">
        <option value="text">text</option>
        <option value="json">json</option>
        <option value="html">html</option>
      </select>
      <textarea name="content_value" rows="6" required placeholder="content_value"></textarea>
      <button class="btn-primary" type="submit">Save</button>
      <p id="contentMsg" class="form-message"></p>
    </form>

    <div class="table-shell">
      <table>
        <thead>
          <tr><th>Key</th><th>Type</th><th>Updated</th><th>Value</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= htmlspecialchars((string)$item['key_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$item['content_type'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$item['updated_at'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="pre-cell"><?= htmlspecialchars((string)$item['content_value'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('contentForm');
  const msg = document.getElementById('contentMsg');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = Object.fromEntries(new FormData(form).entries());
    const response = await fetch('/api/admin/content', {
      method: 'PUT',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await response.json();
    if (response.ok) {
      msg.className = 'form-message ok';
      msg.textContent = 'Saved';
      setTimeout(() => window.location.reload(), 500);
      return;
    }
    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed';
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
