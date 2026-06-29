<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section"><div class="container" data-reveal>
  <div class="admin-page-head"><div><p class="eyebrow">Design ideas</p><h1>Quote Leads</h1><p class="muted-line">Manage quote requests captured from design idea pages.</p></div><a class="btn-link" href="/admin/design-ideas">Manage ideas</a></div>
  <div class="table-shell"><table><thead><tr><th>Customer</th><th>Context</th><th>Requirement</th><th>Message</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach ($leads as $lead): ?><tr><td><strong><?= htmlspecialchars((string)$lead['name'], ENT_QUOTES, 'UTF-8') ?></strong><br><?= htmlspecialchars((string)$lead['phone'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars((string)$lead['email'], ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars((string)($lead['idea_name'] ?: $lead['alias_title'] ?: 'Design ideas'), ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars((string)$lead['city'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$lead['budget'], ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars((string)$lead['requirement'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$lead['message'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(date('d M Y, h:i A', strtotime((string)$lead['created_at'])), ENT_QUOTES, 'UTF-8') ?></td><td><select class="design-lead-status" data-id="<?= (int)$lead['id'] ?>"><?php foreach (['new','contacted','qualified','closed'] as $status): ?><option value="<?= $status ?>" <?= $lead['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></td></tr><?php endforeach; ?><?php if (!$leads): ?><tr><td colspan="6">No design quote leads yet.</td></tr><?php endif; ?></tbody></table></div>
</div></section>
<script>
document.querySelectorAll('.design-lead-status').forEach(select => select.addEventListener('change', async () => {
  const res = await fetch(`/api/admin/design-idea-leads/${select.dataset.id}/status`, {method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify({status:select.value})});
  if (!res.ok) alert('Status update failed.');
}));
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
