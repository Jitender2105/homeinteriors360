<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section container">
  <h2>Portfolio</h2>
  <div class="grid grid-3">
    <?php foreach ($designs as $d): ?>
      <article class="card">
        <?php if (!empty($d['featured_image'])): ?><img src="<?= htmlspecialchars($d['featured_image']) ?>" alt="" style="width:100%;height:220px;object-fit:cover;border-radius:10px;"><?php endif; ?>
        <h3 style="font-family:Georgia,serif;"><?= htmlspecialchars($d['title']) ?></h3>
        <p class="muted"><?= htmlspecialchars($d['description'] ?? '') ?></p>
        <p class="muted" style="font-size:12px;"><?= htmlspecialchars($d['work_type'] ?? '') ?> <?= !empty($d['locality']) ? '• ' . htmlspecialchars($d['locality']) : '' ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
