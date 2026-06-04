<?php
require __DIR__ . '/../partials/header.php';

$landingTitle = (string)($landingTitle ?? 'Interior Design Leads');
$landingSubtitle = (string)($landingSubtitle ?? 'Buy verified interior design leads with city, budget, and work-type filters.');
$primaryCta = (string)($primaryCta ?? 'Buy Interior Leads');
$primaryHref = (string)($primaryHref ?? '/lead-marketplace');
$secondaryCta = (string)($secondaryCta ?? 'See Pricing');
$secondaryHref = (string)($secondaryHref ?? '/pricing-details');
$keywordChips = is_array($keywordChips ?? null) ? $keywordChips : [];
$sections = is_array($landingSections ?? null) ? $landingSections : [];
$faqs = is_array($faqs ?? null) ? $faqs : [];
$cityLinks = is_array($cityLinks ?? null) ? $cityLinks : [];
?>

<section class="seo-hero" data-reveal>
  <div class="container seo-hero-grid">
    <div class="seo-hero-copy">
      <p class="eyebrow eyebrow-dark">Verified lead marketplace</p>
      <h1><?= htmlspecialchars($landingTitle, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="hero-subtitle"><?= htmlspecialchars($landingSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
      <?php if ($keywordChips !== []): ?>
        <div class="story-badges">
          <?php foreach ($keywordChips as $chip): ?>
            <span class="chip"><?= htmlspecialchars((string)$chip, ENT_QUOTES, 'UTF-8') ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="seo-cta-row">
        <a class="btn-primary" href="<?= htmlspecialchars($primaryHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($primaryCta, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="btn-link" href="<?= htmlspecialchars($secondaryHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($secondaryCta, ENT_QUOTES, 'UTF-8') ?></a>
      </div>
    </div>
    <aside class="seo-proof-panel">
      <strong>First 10 leads free</strong>
      <span>for eligible first-time buyers</span>
      <ul class="benefit-list">
        <li>City, locality, budget, and work-type filters</li>
        <li>Razorpay checkout with buyer dashboard access</li>
        <li>Slab pricing: INR 100, INR 80, and INR 60 per paid lead</li>
      </ul>
    </aside>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container seo-section-grid">
    <?php foreach ($sections as $section): ?>
      <article class="seo-copy-block">
        <h2><?= htmlspecialchars((string)($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <?php foreach (($section['body'] ?? []) as $paragraph): ?>
          <p><?= htmlspecialchars((string)$paragraph, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($cityLinks !== []): ?>
  <section class="section seo-city-band" data-reveal>
    <div class="container">
      <div class="section-head">
        <h2>City-wise Interior Design Leads</h2>
        <p>Build topical relevance with location-specific lead pages and filter your marketplace by active service areas.</p>
      </div>
      <div class="seo-link-grid">
        <?php foreach ($cityLinks as $link): ?>
          <a href="<?= htmlspecialchars((string)$link['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$link['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php if ($faqs !== []): ?>
  <section class="section" data-reveal>
    <div class="container">
      <div class="section-head">
        <h2>FAQs</h2>
        <p>Answers for interior designers, architects, studios, and contractors buying leads from HomeInteriors360.</p>
      </div>
      <div class="seo-faq-list">
        <?php foreach ($faqs as $faq): ?>
          <details>
            <summary><?= htmlspecialchars((string)($faq['question'] ?? ''), ENT_QUOTES, 'UTF-8') ?></summary>
            <p><?= htmlspecialchars((string)($faq['answer'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
