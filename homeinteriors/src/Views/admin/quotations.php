<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$money = static fn(mixed $value): string => '₹' . number_format((float)$value, 0);
$statusLabel = static fn(string $status): string => ucwords(str_replace('_', ' ', $status));
$portalBase = $portalBase ?? '/admin';
$isDesignerPortal = !empty($isDesignerPortal);
?>
<section class="section quotation-admin">
  <div class="container" data-reveal>
    <div class="admin-page-head">
      <div>
        <p class="eyebrow">Quotation Builder</p>
        <h1>All Quotations</h1>
        <p class="muted-line">Create, revise, send, and track itemised interior proposals.</p>
      </div>
      <a class="btn-primary" href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations/create">Create Quotation</a>
    </div>
    <nav class="quotation-subnav">
      <a href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations">All Quotations</a>
      <a href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations/create">Create Quotation</a>
      <?php if (!$isDesignerPortal): ?>
        <a href="/admin/proposal-templates">Proposal Templates</a>
        <a href="/admin/quotation-rate-card">Rate Card</a>
        <a href="/admin/quotation-packages">Package Master</a>
        <a href="/admin/quotation-settings">Settings</a>
      <?php else: ?>
        <a href="/designer/leads">My Leads</a>
      <?php endif; ?>
    </nav>

    <div class="stats-grid quote-stats">
      <article class="stat-card"><span>Total quotations</span><strong><?= (int)$stats['total_quotes'] ?></strong></article>
      <article class="stat-card"><span>Total quote value</span><strong><?= $money($stats['total_value']) ?></strong></article>
      <article class="stat-card"><span>Accepted value</span><strong><?= $money($stats['accepted_value']) ?></strong></article>
      <article class="stat-card"><span>Conversion rate</span><strong><?= number_format((float)$stats['conversion_rate'], 1) ?>%</strong></article>
    </div>

    <form class="admin-card quote-filter-bar" method="get">
      <input name="q" value="<?= htmlspecialchars((string)($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Search client, mobile, quote ID, society, designer">
      <select name="status"><option value="">All statuses</option><?php foreach (['draft','ready_for_review','sent_to_client','viewed_by_client','revision_requested','revised','accepted','rejected','expired','converted_to_project'] as $status): ?><option value="<?= $status ?>" <?= (($_GET['status'] ?? '') === $status) ? 'selected' : '' ?>><?= $statusLabel($status) ?></option><?php endforeach; ?></select>
      <select name="package_id"><option value="">All packages</option><?php foreach ($packages as $package): ?><option value="<?= (int)$package['id'] ?>" <?= (string)($_GET['package_id'] ?? '') === (string)$package['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$package['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
      <input name="city" value="<?= htmlspecialchars((string)($_GET['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="City">
      <button class="btn-primary" type="submit">Filter</button>
    </form>

    <div class="table-shell">
      <table>
        <thead><tr><th>Quote ID</th><th>Client</th><th>City / Society</th><th>Project</th><th>Designer</th><th>Amount</th><th>Package</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($quotations as $quote): ?>
            <tr>
              <td><strong><?= htmlspecialchars((string)$quote['quote_number'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Lead: <?= htmlspecialchars((string)($quote['lead_name'] ?? $quote['lead_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
              <td><?= htmlspecialchars((string)$quote['client_name'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars((string)$quote['client_phone'], ENT_QUOTES, 'UTF-8') ?></small></td>
              <td><?= htmlspecialchars((string)$quote['city'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars((string)($quote['society_name'] ?: $quote['locality']), ENT_QUOTES, 'UTF-8') ?></small></td>
              <td><?= htmlspecialchars((string)$quote['property_type'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars((string)$quote['bhk'], ENT_QUOTES, 'UTF-8') ?></small></td>
              <td><?= htmlspecialchars((string)($quote['designer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><strong><?= $money($quote['final_amount']) ?></strong></td>
              <td><?= htmlspecialchars((string)($quote['package_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="quote-status"><?= $statusLabel((string)$quote['status']) ?></span></td>
              <td><?= htmlspecialchars(date('d M Y', strtotime((string)$quote['updated_at'])), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="quote-action-cell">
                <a class="btn-link" href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations/<?= (int)$quote['id'] ?>">View</a>
                <a class="btn-link" href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations/<?= (int)$quote['id'] ?>/edit">Edit</a>
                <button class="btn-link quote-row-action" data-action="duplicate" data-id="<?= (int)$quote['id'] ?>" type="button">Duplicate</button>
                <button class="btn-link quote-row-action" data-action="revision" data-id="<?= (int)$quote['id'] ?>" type="button">Revision</button>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$quotations): ?><tr><td colspan="10">No quotations found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
document.querySelectorAll('.quote-row-action').forEach(button => button.addEventListener('click', async () => {
  const response = await fetch(`/api/quotations/${button.dataset.id}/${button.dataset.action}`, {method:'POST'});
  const data = await response.json();
  if (response.ok && data.redirect_url) location.href = data.redirect_url;
  else alert(data.error || 'Action failed');
}));
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
