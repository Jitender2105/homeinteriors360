<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container profile-top-lead" data-reveal>
    <div class="profile-panel profile-intro-copy">
      <p class="eyebrow eyebrow-dark">Dedicated Designer Microsite</p>
      <h1><?= htmlspecialchars($designer['full_name']) ?></h1>
      <p class="muted"><?= htmlspecialchars($designer['profile_title'] ?? 'Interior Designer') ?></p>
      <p class="profile-summary"><?= htmlspecialchars($designer['bio'] ?? '') ?></p>
      <div class="admin-actions">
        <span class="badge"><?= (int)($designer['years_experience'] ?? 0) ?>+ Years</span>
        <span class="badge"><?= (int)($designer['total_projects'] ?? 0) ?>+ Projects</span>
      </div>
    </div>
    <div class="card top-lead-form">
      <h3 style="margin-top:0;">Start Your Project</h3>
      <?php $designerId = (int) $designer['id']; include __DIR__ . '/../partials/lead-form.php'; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container" data-reveal>
    <div class="section-head"><h2>Work Done</h2></div>
    <div class="slider">
      <?php foreach ($sections['projects'] as $p): ?>
        <article class="slide">
          <?php if (!empty($p['image_url'])): ?>
            <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" class="card-image" style="height:220px;">
          <?php endif; ?>
          <div style="padding:14px;">
            <h3><?= htmlspecialchars($p['project_title'] ?? 'Completed Project') ?></h3>
            <p class="muted"><?= htmlspecialchars($p['location'] ?? '') ?> <?= !empty($p['cost_range']) ? '• ' . htmlspecialchars($p['cost_range']) : '' ?> <?= !empty($p['work_type']) ? '• ' . htmlspecialchars($p['work_type']) : '' ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="section">
  <div class="container" data-reveal>
    <div class="section-head"><h2>Customer Testimonials</h2></div>
    <div class="slider"><?php foreach ($sections['testimonials'] as $t): ?><div class="slide"><div style="padding:14px;"><p class="muted">"<?= htmlspecialchars($t['testimonial_text']) ?>"</p><p><?= str_repeat('★', max(1, min(5, (int)($t['rating'] ?? 5)))) ?></p><p><strong><?= htmlspecialchars($t['customer_name']) ?></strong></p><p class="muted"><?= htmlspecialchars($t['customer_location'] ?? '') ?></p></div></div><?php endforeach; ?></div>
  </div>
</section>
<section class="section">
  <div class="container" data-reveal>
    <div class="section-head"><h2>Why Trust Us</h2></div>
    <div class="slider"><?php foreach ($sections['trust_points'] as $t): ?><div class="slide"><div style="padding:14px;"><h3 style="margin-top:0;"><?= htmlspecialchars($t['title']) ?></h3><p class="muted"><?= htmlspecialchars($t['description'] ?? '') ?></p></div></div><?php endforeach; ?></div>
  </div>
</section>
<section class="section">
  <div class="container" data-reveal>
    <div class="section-head"><h2>Our USP</h2></div>
    <div class="slider"><?php foreach ($sections['usps'] as $u): ?><div class="slide"><div style="padding:14px;"><h3 style="margin-top:0;"><?= htmlspecialchars($u['title']) ?></h3><p class="muted"><?= htmlspecialchars($u['description'] ?? '') ?></p></div></div><?php endforeach; ?></div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
