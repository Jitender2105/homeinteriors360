<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <div class="section-head">
      <p class="eyebrow">Journal</p>
      <h2>Editorial notes, trend pieces, and practical interior guidance.</h2>
      <p>We’ve given the journal the same lighter spacing and visual calm as the rest of the site.</p>
    </div>
    <div class="grid grid-3">
    <?php foreach ($articles as $a): ?>
      <article class="card">
        <?php if (!empty($a['featured_image'])): ?><img src="<?= htmlspecialchars($a['featured_image']) ?>" alt="" class="card-image" style="height:200px;"><?php endif; ?>
        <p class="muted" style="font-size:12px;"><?= !empty($a['published_at']) ? date('F j, Y', strtotime($a['published_at'])) : '' ?></p>
        <h3><?= htmlspecialchars($a['title']) ?></h3>
        <p class="muted"><?= htmlspecialchars($a['excerpt'] ?? '') ?></p>
      </article>
    <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
