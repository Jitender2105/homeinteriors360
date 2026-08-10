<?php
require __DIR__ . '/../partials/header.php';

$pageTitle = (string)($legalTitle ?? 'Company Information');
$lastUpdated = (string)($lastUpdated ?? '28 May 2026');
$sections = is_array($sections ?? null) ? $sections : [];
$contactEmail = (string)($contactEmail ?? 'jitender@homeinteriors360.com');
$contactPhone = (string)($contactPhone ?? '+91-9540573661');
?>

<section class="section legal-hero" data-reveal>
  <div class="container legal-shell">
    <div class="section-head section-head-wide">
      <p class="eyebrow">HomeInteriors360</p>
      <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
      <p>Last updated: <?= htmlspecialchars($lastUpdated, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div class="legal-card">
      <?php foreach ($sections as $section): ?>
        <article class="legal-section">
          <h2><?= htmlspecialchars((string)($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
          <?php foreach (($section['body'] ?? []) as $paragraph): ?>
            <?php if (is_array($paragraph)): ?>
              <ul class="legal-list">
                <?php foreach ($paragraph as $item): ?>
                  <li><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p><?= htmlspecialchars((string)$paragraph, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          <?php endforeach; ?>
        </article>
      <?php endforeach; ?>

      <article class="legal-section legal-contact-box">
        <h2>Contact for Support</h2>
        <p>For questions about purchases, delivery, refunds, account access, or these policies, contact HomeInteriors360 support.</p>
        <ul class="legal-list">
          <li>Email: <?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?></li>
          <li>Phone: <?= htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8') ?></li>
          <li>Service area: Delhi NCR, Gurugram, and nearby markets served through HomeInteriors360.com</li>
        </ul>
      </article>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
