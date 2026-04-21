<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1><?= htmlspecialchars((string)($content['admin.pros.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="cards-grid">
      <?php foreach ($pros as $pro): ?>
        <article class="listing-card">
          <img src="<?= htmlspecialchars((string)($pro['profile_pic'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?>" />
          <div>
            <h4><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></h4>
            <p><?= htmlspecialchars((string)($pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <label class="switch-row">
              <input class="verify-toggle" type="checkbox" data-id="<?= (int)$pro['id'] ?>" <?= (int)$pro['verification_status'] === 1 ? 'checked' : '' ?> />
              <span><?= htmlspecialchars((string)($content['directory.verified'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </label>
            <a class="btn-link" href="/professionals/<?= htmlspecialchars((string)$pro['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer">Open Profile</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script>
(() => {
  document.querySelectorAll('.verify-toggle').forEach((element) => {
    element.addEventListener('change', async () => {
      await fetch('/api/admin/pros/verify', {
        method: 'PUT',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pro_id: element.dataset.id, verification_status: element.checked })
      });
    });
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
