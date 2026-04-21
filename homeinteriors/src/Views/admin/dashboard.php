<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1><?= htmlspecialchars((string)($content['admin.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="stats-grid">
      <article class="stat-card"><span>Total Leads</span><strong><?= (int)($counts['leads'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>New Leads</span><strong><?= (int)($counts['new_leads'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>Active Professionals</span><strong><?= (int)($counts['pros'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>Verified Professionals</span><strong><?= (int)($counts['verified_pros'] ?? 0) ?></strong></article>
    </div>
    <div class="admin-links">
      <a class="btn-link" href="/admin/content"><?= htmlspecialchars((string)($content['admin.content.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
      <a class="btn-link" href="/admin/leads"><?= htmlspecialchars((string)($content['admin.leads.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
      <a class="btn-link" href="/admin/pros"><?= htmlspecialchars((string)($content['admin.pros.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
      <a class="btn-link" href="/api/auth/logout">Logout</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
