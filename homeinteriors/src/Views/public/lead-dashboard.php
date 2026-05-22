<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section lead-shop-page" data-reveal>
  <div class="container">
    <div class="section-head section-head-wide">
      <p class="eyebrow eyebrow-dark">Buyer Dashboard</p>
      <h1>Purchased lead packages</h1>
      <p>Logged in as <?= htmlspecialchars((string)$buyer['name'], ENT_QUOTES, 'UTF-8') ?>. Download only the filters you have purchased.</p>
      <a class="btn-link" href="/api/buyer/logout">Logout</a>
    </div>
    <div class="lead-package-grid">
      <?php foreach ($purchases as $item): ?>
        <article class="lead-package-card">
          <p class="eyebrow eyebrow-dark"><?= htmlspecialchars((string)$item['payment_status'], ENT_QUOTES, 'UTF-8') ?></p>
          <h3><?= htmlspecialchars((string)$item['filter_name'], ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="lead-package-count"><strong><?= number_format((int)$item['leads_count']) ?></strong><span>purchased leads</span></div>
          <p class="muted">Purchased <?= htmlspecialchars((string)$item['purchase_date'], ENT_QUOTES, 'UTF-8') ?> · Paid ₹<?= number_format((float)$item['amount_total'], 0) ?></p>
          <a class="btn-primary" href="/lead-download/<?= (int)$item['id'] ?>">Download Excel</a>
        </article>
      <?php endforeach; ?>
      <?php if (!$purchases): ?><div class="lead-card"><h2>No purchases yet.</h2><p class="muted">Buy your first lead package from the marketplace.</p><a class="btn-link" href="/lead-marketplace">Browse Leads</a></div><?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
