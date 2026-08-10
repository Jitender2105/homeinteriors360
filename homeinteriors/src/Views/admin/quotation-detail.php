<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$money = static fn(mixed $value): string => '₹' . number_format((float)$value, 0);
$statusLabel = static fn(string $status): string => ucwords(str_replace('_', ' ', $status));
$proposalUrl = absoluteUrl('/proposal/' . (string)$quote['proposal_token'] . '/pdf');
$portalBase = $portalBase ?? '/admin';
$isDesignerPortal = !empty($isDesignerPortal);
?>
<section class="section quotation-admin">
  <div class="container" data-reveal>
    <div class="admin-page-head">
      <div>
        <p class="eyebrow">Quotation Builder</p>
        <h1><?= htmlspecialchars((string)$quote['quote_number'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="muted-line"><?= htmlspecialchars((string)$quote['client_name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$quote['city'], ENT_QUOTES, 'UTF-8') ?> · <?= $statusLabel((string)$quote['status']) ?></p>
      </div>
      <div class="admin-links">
        <a class="btn-link" href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations/<?= (int)$quote['id'] ?>/edit">Edit</a>
        <a class="btn-primary" href="/api/quotations/<?= (int)$quote['id'] ?>/pdf" target="_blank">Download PDF</a>
      </div>
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

    <div class="quote-summary-grid">
      <article class="admin-card">
        <h2>Client and project</h2>
        <dl class="quote-detail-list">
          <div><dt>Client</dt><dd><?= htmlspecialchars((string)$quote['client_name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Mobile</dt><dd><?= htmlspecialchars((string)$quote['client_phone'], ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Location</dt><dd><?= htmlspecialchars(trim((string)$quote['society_name'] . ', ' . (string)$quote['locality'] . ', ' . (string)$quote['city'], ', '), ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Project</dt><dd><?= htmlspecialchars((string)$quote['property_type'] . ' · ' . (string)$quote['bhk'] . ' · ' . (string)$quote['scope_type'], ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Package</dt><dd><?= htmlspecialchars((string)($quote['package_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Designer</dt><dd><?= htmlspecialchars((string)($quote['designer_name'] ?? 'Not assigned'), ENT_QUOTES, 'UTF-8') ?></dd></div>
        </dl>
      </article>
      <aside class="quote-total-panel static">
        <span>Final quote value</span>
        <strong><?= $money($quote['final_amount']) ?></strong>
        <dl>
          <div><dt>Subtotal</dt><dd><?= $money($quote['subtotal']) ?></dd></div>
          <div><dt>Design fee</dt><dd><?= $money($quote['design_fee']) ?></dd></div>
          <div><dt>PM fee</dt><dd><?= $money($quote['project_management_fee']) ?></dd></div>
          <div><dt>GST</dt><dd><?= $money($quote['gst_amount']) ?></dd></div>
          <div><dt>Discount</dt><dd><?= $money($quote['discount_amount']) ?></dd></div>
          <div><dt>Vendor cost</dt><dd><?= $money($quote['vendor_cost']) ?></dd></div>
          <div><dt>Margin</dt><dd><?= $money($quote['margin_amount']) ?> (<?= number_format((float)$quote['margin_percentage'], 1) ?>%)</dd></div>
          <div><dt>Commission</dt><dd><?= $money($quote['platform_commission']) ?></dd></div>
        </dl>
      </aside>
    </div>

    <div class="admin-card">
      <div class="admin-section-title"><h2>Proposal PDF sharing</h2><button class="btn-muted" type="button" id="copyProposal">Copy PDF link</button></div>
      <input readonly value="<?= htmlspecialchars($proposalUrl, ENT_QUOTES, 'UTF-8') ?>">
      <div class="admin-links">
        <a class="btn-primary" target="_blank" href="<?= htmlspecialchars($proposalUrl, ENT_QUOTES, 'UTF-8') ?>">Create proposal PDF</a>
        <button class="btn-muted quote-action" data-action="send" type="button">Mark sent</button>
        <a class="btn-muted" target="_blank" href="https://wa.me/?text=<?= urlencode(str_replace(['[Client Name]','[Project Location]','[Proposal Link]','[Final Amount]','[Validity Date]'], [(string)$quote['client_name'], (string)($quote['society_name'] ?: $quote['city']), $proposalUrl, number_format((float)$quote['final_amount'], 0), (string)$quote['valid_until']], (string)($settings['default_whatsapp_message'] ?? 'Hello [Client Name], your proposal is ready: [Proposal Link]'))) ?>">WhatsApp share</a>
      </div>
      <p id="quoteDetailMsg" class="form-message"></p>
    </div>

    <div class="table-shell">
      <table>
        <thead><tr><th>Room</th><th>Category</th><th>Item</th><th>Material</th><th>Qty</th><th>Unit</th><th>Rate</th><th>Amount</th><th>Proposal</th></tr></thead>
        <tbody><?php foreach ($quote['items'] as $item): ?><tr>
          <td><?= htmlspecialchars((string)$item['room_name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars((string)$item['category'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><strong><?= htmlspecialchars((string)$item['item_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small><?= htmlspecialchars((string)$item['description'], ENT_QUOTES, 'UTF-8') ?></small><?= !empty($item['is_manual_override']) ? '<br><span class="quote-status">Manual Override</span>' : '' ?></td>
          <td><?= htmlspecialchars(trim((string)$item['material'] . ' ' . (string)$item['finish'] . ' ' . (string)$item['brand']), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= number_format((float)$item['quantity'], 2) ?></td>
          <td><?= htmlspecialchars((string)$item['unit_type'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= $money($item['rate']) ?></td>
          <td><strong><?= $money($item['amount']) ?></strong></td>
          <td><?= !empty($item['include_in_proposal']) ? 'Included' : 'Hidden' ?></td>
        </tr><?php endforeach; ?></tbody>
      </table>
    </div>

    <div class="quote-summary-grid">
      <article class="admin-card"><h2>Payment schedule</h2><?php foreach ($quote['payment_schedule'] as $row): ?><p><strong><?= htmlspecialchars((string)$row['label'], ENT_QUOTES, 'UTF-8') ?></strong> · <?= number_format((float)($row['percentage'] ?? 0), 1) ?>% · <?= $money($row['amount'] ?? 0) ?></p><?php endforeach; ?></article>
      <article class="admin-card"><h2>Activity log</h2><?php foreach ($quote['activity'] as $log): ?><p><strong><?= htmlspecialchars((string)$log['action'], ENT_QUOTES, 'UTF-8') ?></strong> · <?= htmlspecialchars((string)$log['created_at'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars((string)$log['notes'], ENT_QUOTES, 'UTF-8') ?></small></p><?php endforeach; ?></article>
    </div>
  </div>
</section>
<script>
(() => {
  const msg = document.getElementById('quoteDetailMsg');
  document.getElementById('copyProposal').onclick = async () => { await navigator.clipboard.writeText(<?= json_encode($proposalUrl) ?>); msg.textContent = 'Proposal PDF link copied.'; msg.className = 'form-message ok'; };
  document.querySelectorAll('.quote-action').forEach(btn => btn.onclick = async () => {
    const response = await fetch('/api/quotations/<?= (int)$quote['id'] ?>/' + btn.dataset.action, {method:'POST'});
    const data = await response.json();
    msg.className = `form-message ${response.ok ? 'ok' : 'error'}`;
    msg.textContent = response.ok ? 'Status updated.' : (data.error || 'Action failed');
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
