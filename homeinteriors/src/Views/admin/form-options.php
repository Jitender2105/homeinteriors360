<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section container">
  <h2>Form Options Management</h2>
  <p class="muted">Use API endpoint <code>/api/admin/form-options</code> for add/update/delete.</p>
  <div class="card">
    <pre style="white-space:pre-wrap;overflow:auto;"><?= htmlspecialchars(json_encode($options, JSON_PRETTY_PRINT)) ?></pre>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
