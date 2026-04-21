<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section container">
  <h2>Journal</h2>
  <div class="grid grid-3">
    <?php foreach ($articles as $a): ?>
      <article class="card">
        <?php if (!empty($a['featured_image'])): ?><img src="<?= htmlspecialchars($a['featured_image']) ?>" alt="" style="width:100%;height:200px;object-fit:cover;border-radius:10px;"><?php endif; ?>
        <p class="muted" style="font-size:12px;"><?= !empty($a['published_at']) ? date('F j, Y', strtotime($a['published_at'])) : '' ?></p>
        <h3 style="font-family:Georgia,serif;"><?= htmlspecialchars($a['title']) ?></h3>
        <p class="muted"><?= htmlspecialchars($a['excerpt'] ?? '') ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
