<?php require __DIR__ . '/../partials/header.php'; ?>
<?php $money = static fn(mixed $value): string => '₹' . number_format((float)$value, 0); ?>
<main class="proposal-page">
  <section class="proposal-hero">
    <div class="container">
      <p class="eyebrow">HomeInteriors360 Proposal</p>
      <h1><?= htmlspecialchars((string)$quote['quote_number'], ENT_QUOTES, 'UTF-8') ?> for <?= htmlspecialchars((string)$quote['client_name'], ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars(trim((string)$quote['society_name'] . ', ' . (string)$quote['city'], ', '), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$quote['property_type'], ENT_QUOTES, 'UTF-8') ?> · Valid till <?= htmlspecialchars((string)$quote['valid_until'], ENT_QUOTES, 'UTF-8') ?></p>
      <div class="proposal-cta-row">
        <a class="btn-primary" href="/proposal/<?= htmlspecialchars((string)$quote['proposal_token'], ENT_QUOTES, 'UTF-8') ?>/pdf" target="_blank">Download PDF</a>
        <?php if (!$expired): ?><button class="btn-muted proposal-status" data-action="accept" type="button">Accept Proposal</button><button class="btn-muted proposal-status" data-action="request-revision" type="button">Request Revision</button><?php endif; ?>
        <a class="btn-muted" href="https://wa.me/919540573661" target="_blank">WhatsApp HomeInteriors360</a>
      </div>
      <?php if ($expired): ?><p class="form-message error">This proposal has expired. Please request a revised proposal.</p><?php endif; ?>
      <p id="proposalMsg" class="form-message"></p>
    </div>
  </section>
  <section class="section">
    <div class="container proposal-layout">
      <article class="proposal-card">
        <h2>Project summary</h2>
        <div class="proposal-summary-grid">
          <span>City<strong><?= htmlspecialchars((string)$quote['city'], ENT_QUOTES, 'UTF-8') ?></strong></span>
          <span>BHK<strong><?= htmlspecialchars((string)$quote['bhk'], ENT_QUOTES, 'UTF-8') ?></strong></span>
          <span>Scope<strong><?= htmlspecialchars((string)$quote['scope_type'], ENT_QUOTES, 'UTF-8') ?></strong></span>
          <span>Package<strong><?= htmlspecialchars((string)($quote['package_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></span>
          <span>Timeline<strong><?= htmlspecialchars((string)($quote['timeline_range'] ?? 'As per final scope'), ENT_QUOTES, 'UTF-8') ?></strong></span>
          <span>Quote value<strong><?= $money($quote['final_amount']) ?></strong></span>
        </div>
        <p><?= nl2br(htmlspecialchars((string)($quote['welcome_note'] ?: $quote['client_notes'] ?: 'Thank you for sharing your interior design requirement. This proposal includes selected scope, package, timeline, and payment details.'), ENT_QUOTES, 'UTF-8')) ?></p>
      </article>
      <aside class="proposal-card proposal-total">
        <h2><?= $money($quote['final_amount']) ?></h2>
        <dl><div><dt>Subtotal</dt><dd><?= $money($quote['subtotal']) ?></dd></div><div><dt>Design fee</dt><dd><?= $money($quote['design_fee']) ?></dd></div><div><dt>Project management</dt><dd><?= $money($quote['project_management_fee']) ?></dd></div><div><dt>GST</dt><dd><?= $money($quote['gst_amount']) ?></dd></div><div><dt>Discount</dt><dd><?= $money($quote['discount_amount']) ?></dd></div></dl>
      </aside>
    </div>
  </section>
  <section class="section"><div class="container proposal-card"><h2>Detailed quotation</h2><div class="table-shell proposal-table"><table><thead><tr><th>Room</th><th>Item</th><th>Description</th><th>Qty</th><th>Unit</th><th>Rate</th><th>Amount</th></tr></thead><tbody><?php foreach ($quote['items'] as $item): if (empty($item['include_in_proposal'])) continue; ?><tr><td><?= htmlspecialchars((string)$item['room_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$item['item_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$item['description'], ENT_QUOTES, 'UTF-8') ?></td><td><?= number_format((float)$item['quantity'], 2) ?></td><td><?= htmlspecialchars((string)$item['unit_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= $money($item['rate']) ?></td><td><?= $money($item['amount']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></section>
  <section class="section"><div class="container proposal-layout"><article class="proposal-card"><h2>Package details</h2><p><?= htmlspecialchars((string)($quote['package_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><ul><li><?= htmlspecialchars((string)($quote['material_grade'] ?? ''), ENT_QUOTES, 'UTF-8') ?> material grade</li><li><?= htmlspecialchars((string)($quote['hardware_level'] ?? ''), ENT_QUOTES, 'UTF-8') ?> hardware</li><li><?= htmlspecialchars((string)($quote['finish_level'] ?? ''), ENT_QUOTES, 'UTF-8') ?> finish</li><li><?= htmlspecialchars((string)($quote['warranty_years'] ?? 0), ENT_QUOTES, 'UTF-8') ?> year warranty</li></ul></article><article class="proposal-card"><h2>Payment schedule</h2><?php foreach ($quote['payment_schedule'] as $row): ?><p><strong><?= htmlspecialchars((string)$row['label'], ENT_QUOTES, 'UTF-8') ?></strong><br><?= number_format((float)($row['percentage'] ?? 0), 1) ?>% · <?= $money($row['amount'] ?? 0) ?></p><?php endforeach; ?></article></div></section>
  <section class="section"><div class="container proposal-layout"><article class="proposal-card"><h2>Inclusions</h2><p><?= nl2br(htmlspecialchars((string)($quote['inclusions'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p></article><article class="proposal-card"><h2>Exclusions</h2><p><?= nl2br(htmlspecialchars((string)($quote['exclusions'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p></article></div></section>
  <section class="section"><div class="container proposal-card"><h2>Terms, warranty and support</h2><p><?= nl2br(htmlspecialchars((string)($quote['terms'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p><p><?= nl2br(htmlspecialchars((string)($quote['warranty_text'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p></div></section>
</main>
<script>
document.querySelectorAll('.proposal-status').forEach(btn => btn.onclick = async () => {
  const note = btn.dataset.action === 'accept' ? 'Accepted from public proposal page' : prompt('What revision would you like to request?') || 'Revision requested from public proposal page';
  const response = await fetch('/api/proposals/<?= htmlspecialchars((string)$quote['proposal_token'], ENT_QUOTES, 'UTF-8') ?>/' + btn.dataset.action, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({notes: note})});
  const data = await response.json();
  const msg = document.getElementById('proposalMsg');
  msg.className = `form-message ${response.ok ? 'ok' : 'error'}`;
  msg.textContent = response.ok ? 'Thank you. Your response has been recorded.' : (data.error || 'Could not update proposal.');
});
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
