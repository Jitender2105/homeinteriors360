<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section"><div class="container" data-reveal>
  <div class="admin-page-head"><div><p class="eyebrow">Real estate marketplace</p><h1>Property Enquiries</h1><p class="muted-line">Track buyer and tenant interest submitted from project pages.</p></div><a class="btn-link" href="/admin/property-projects">Manage projects</a></div>
  <div class="table-shell"><table><thead><tr><th>Customer</th><th>Project</th><th>Requirement</th><th>Message</th><th>Date</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($enquiries as $item): ?><tr>
    <td><strong><?= htmlspecialchars((string)$item['name'], ENT_QUOTES, 'UTF-8') ?></strong><br><?= htmlspecialchars((string)$item['phone'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars((string)$item['email'], ENT_QUOTES, 'UTF-8') ?></small></td>
    <td><?= htmlspecialchars((string)$item['project_name'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars(trim((string)$item['locality'] . ', ' . (string)$item['city'], ', '), ENT_QUOTES, 'UTF-8') ?><?= !empty($item['unit_name']) ? ' · ' . htmlspecialchars((string)$item['unit_name'], ENT_QUOTES, 'UTF-8') : '' ?></small></td>
    <td><?= htmlspecialchars(ucfirst((string)$item['requirement']), ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars((string)$item['message'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime((string)$item['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
    <td><select class="property-enquiry-status" data-id="<?= (int)$item['id'] ?>"><?php foreach (['new','contacted','qualified','closed'] as $status): ?><option value="<?= $status ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></td>
  </tr><?php endforeach; ?>
  <?php if (!$enquiries): ?><tr><td colspan="6">No property enquiries yet.</td></tr><?php endif; ?>
  </tbody></table></div>
</div></section>
<script>
document.querySelectorAll('.property-enquiry-status').forEach(select => select.addEventListener('change', async () => {
  const response = await fetch(`/api/admin/property-enquiries/${select.dataset.id}/status`, {method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify({status:select.value})});
  if (!response.ok) alert('Status update failed.');
}));
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
