<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1><?= htmlspecialchars((string)($content['admin.leads.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="table-shell">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>City</th>
            <th>Society / Area</th>
            <th>Budget</th>
            <th>Requirement</th>
            <th>Source</th>
            <th>Pro</th>
            <th>Status</th>
            <th>Estimate</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leads as $lead): ?>
            <tr>
              <td><?= htmlspecialchars((string)$lead['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$lead['phone'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$lead['city'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($lead['society_area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($lead['budget'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$lead['requirement'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$lead['source'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($lead['pro_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <select class="lead-status" data-id="<?= (int)$lead['id'] ?>">
                  <?php foreach (['new', 'contacted', 'converted'] as $s): ?>
                    <option value="<?= $s ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><?= $lead['estimate'] !== null ? '₹' . number_format((float)$lead['estimate'], 0) : '' ?></td>
              <td><?= htmlspecialchars((string)$lead['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
(() => {
  document.querySelectorAll('.lead-status').forEach((element) => {
    element.addEventListener('change', async () => {
      await fetch('/api/admin/leads/status', {
        method: 'PUT',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lead_id: element.dataset.id, status: element.value })
      });
    });
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
