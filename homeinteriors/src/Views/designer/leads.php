<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section quotation-admin">
  <div class="container" data-reveal>
    <div class="admin-page-head"><div><p class="eyebrow">Interior designer portal</p><h1>My Leads</h1><p class="muted-line">Only leads assigned to your designer profile are shown here.</p></div><a class="btn-link" href="/designer/quotations">My Quotations</a></div>
    <nav class="quotation-subnav"><a href="/designer">Dashboard</a><a href="/designer/leads">My Leads</a><a href="/designer/quotations">My Quotations</a><a href="/designer/quotations/create">Create Quotation</a><a href="/api/auth/logout">Logout</a></nav>
    <div class="table-shell">
      <table><thead><tr><th>Name</th><th>Phone</th><th>City</th><th>Society / Area</th><th>Budget</th><th>Requirement</th><th>Status</th><th>Date</th><th>Action</th></tr></thead><tbody>
      <?php foreach ($leads as $lead): ?><tr>
        <td><?= htmlspecialchars((string)$lead['name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)$lead['phone'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)$lead['city'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)($lead['society_area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)($lead['budget'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)$lead['requirement'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)$lead['status'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)$lead['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><a class="btn-link" href="/designer/quotations/create?lead_id=<?= (int)$lead['id'] ?>">Create Quotation</a></td>
      </tr><?php endforeach; ?>
      <?php if (!$leads): ?><tr><td colspan="9">No assigned leads yet.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
