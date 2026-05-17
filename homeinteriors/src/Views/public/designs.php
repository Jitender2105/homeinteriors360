<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <div class="section-head">
      <p class="eyebrow">Portfolio</p>
      <h2>Executed projects with a quieter, more premium presentation.</h2>
      <p>Each card shows the material mood, location, and scope without crowding the page.</p>
    </div>
    <div class="grid grid-3">
    <?php foreach ($designs as $d): ?>
      <article class="card">
        <?php if (!empty($d['featured_image'])): ?><img src="<?= htmlspecialchars($d['featured_image']) ?>" alt="" class="card-image" style="height:220px;"><?php endif; ?>
        <h3><?= htmlspecialchars($d['title']) ?></h3>
        <p class="muted"><?= htmlspecialchars($d['description'] ?? '') ?></p>
        <p class="muted" style="font-size:12px;"><?= htmlspecialchars($d['work_type'] ?? '') ?> <?= !empty($d['locality']) ? '• ' . htmlspecialchars($d['locality']) : '' ?></p>
      </article>
    <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
