<?php
require __DIR__ . '/../partials/header.php';

$heroAssets = is_array($payload['hero_assets'] ?? null) ? $payload['hero_assets'] : [];
$heroBg = $heroAssets[0] ?? '';
$heroBg2 = $heroAssets[1] ?? $heroBg;
$topPros = is_array($payload['top_pros'] ?? null) ? $payload['top_pros'] : [];
$featuredProjects = is_array($payload['featured_projects'] ?? null) ? $payload['featured_projects'] : [];
$services = is_array($payload['services'] ?? null) ? $payload['services'] : [];
$testimonials = is_array($payload['testimonials'] ?? null) ? $payload['testimonials'] : [];
$brands = is_array($payload['brands'] ?? null) ? $payload['brands'] : [];
$trustPoints = is_array($payload['trust_points'] ?? null) ? $payload['trust_points'] : [];
$uspPoints = is_array($payload['usp_points'] ?? null) ? $payload['usp_points'] : [];
$defaultImage = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s';
$safeImage = static function (string $url, string $fallback): string {
  $url = trim($url);
  if ($url === '' || !preg_match('~^https?://~i', $url)) {
    return $fallback;
  }
  if (str_contains($url, '1616594039964-3dbbb0bd2e8f')) {
    return $fallback;
  }
  return $url;
};
$trustVisuals = [
  ['title' => 'Verified Professionals', 'image' => $defaultImage],
  ['title' => 'Transparent Pricing', 'image' => $defaultImage],
  ['title' => 'Project Oversight', 'image' => $defaultImage],
  ['title' => 'Reliable Handover', 'image' => $defaultImage],
];
$uspVisuals = [
  ['title' => 'Lead Marketplace', 'image' => $defaultImage],
  ['title' => 'Profile Management', 'image' => $defaultImage],
  ['title' => 'Brand Visibility', 'image' => $defaultImage],
  ['title' => 'Growth Support', 'image' => $defaultImage],
];
$serviceFallbacks = [
  'kitchen' => $defaultImage,
  'wardrobe' => $defaultImage,
  'full_home' => $defaultImage,
];
$testimonialFallbacks = [
  'Priya S' => $defaultImage,
  'Vikas A' => $defaultImage,
  'Karan M' => $defaultImage,
];
$brandFallbacks = [
  'Hafele' => $defaultImage,
  'Hettich' => $defaultImage,
  'Asian Paints' => $defaultImage,
  'Kajaria' => $defaultImage,
];
$cities = is_array($payload['city_options'] ?? null) ? $payload['city_options'] : [];
$requirements = is_array($payload['requirement_options'] ?? null) ? $payload['requirement_options'] : [];
$processSteps = [
  ['title' => 'Briefing', 'text' => 'We start with the way you live, what your home needs, and the feeling you want the finished space to carry.'],
  ['title' => 'Design', 'text' => 'Layouts, materials, and proportion studies are shaped into a calm, precise design direction.'],
  ['title' => 'Build', 'text' => 'Execution stays under one roof with detailed site coordination, vendor alignment, and quality control.'],
  ['title' => 'Handover', 'text' => 'The final reveal is clean, styled, and ready to live in with the finishing details already solved.'],
];
$trustCopy = [
  'Every profile is reviewed before it appears in the network.',
  'Clear scopes and expectations from the first conversation.',
  'Dedicated coordination across design, execution, and handover.',
  'The final delivery stays clean, documented, and ready to live in.',
];
$uspCopy = [
  'Qualified homeowner leads routed by city, locality, and budget.',
  'Professional profiles and portfolios kept current from one backend.',
  'Strong placement for premium and active professionals.',
  'A managed system for reputation, content, and sales readiness.',
];
$normalizeCopy = static function (string $value, string $fallback, array $blocked): string {
  $candidate = trim($value);
  if ($candidate === '') {
    return $fallback;
  }
  $normalized = strtolower(preg_replace('/\s+/', ' ', $candidate));
  foreach ($blocked as $phrase) {
    if (str_contains($normalized, strtolower($phrase))) {
      return $fallback;
    }
  }
  return $candidate;
};
?>

<section class="hero" style="--hero-bg:url('<?= htmlspecialchars($heroBg, ENT_QUOTES, 'UTF-8') ?>');--hero-bg2:url('<?= htmlspecialchars($heroBg2, ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container hero-grid">
    <div class="hero-copy" data-reveal>
      <p class="eyebrow"><?= htmlspecialchars((string)($content['home.hero.eyebrow'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
      <h1><?= htmlspecialchars((string)($content['home.hero.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="hero-subtitle"><?= htmlspecialchars((string)($content['home.hero.subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div class="lead-card" data-reveal>
      <h2><?= htmlspecialchars((string)($content['home.lead.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <form id="heroLeadForm" class="stack-form hero-lead-form">
        <div class="hero-lead-grid">
          <label>
            <span><?= htmlspecialchars((string)($content['home.lead.step1_label'] ?? 'City'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="city" required>
              <option value=""><?= htmlspecialchars((string)($content['home.lead.step1_label'] ?? 'City'), ENT_QUOTES, 'UTF-8') ?></option>
              <?php foreach ($cities as $city): ?>
                <option value="<?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            <span><?= htmlspecialchars((string)($content['home.lead.step2_label'] ?? 'Requirement'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="requirement" required>
              <option value=""><?= htmlspecialchars((string)($content['home.lead.step2_label'] ?? 'Requirement'), ENT_QUOTES, 'UTF-8') ?></option>
              <?php foreach ($requirements as $req): ?>
                <option value="<?= htmlspecialchars((string)$req, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$req, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            <span><?= htmlspecialchars((string)($content['ui.name'] ?? 'Name'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? 'Name'), ENT_QUOTES, 'UTF-8') ?>" />
          </label>

          <label>
            <span><?= htmlspecialchars((string)($content['ui.phone'] ?? 'Phone'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="tel" name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? 'Phone'), ENT_QUOTES, 'UTF-8') ?>" />
          </label>

          <label>
            <span><?= htmlspecialchars((string)($content['ui.society_area'] ?? 'Society / Area'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="society_area" placeholder="<?= htmlspecialchars((string)($content['ui.society_area'] ?? 'Society / Area'), ENT_QUOTES, 'UTF-8') ?>" />
          </label>

          <label>
            <span><?= htmlspecialchars((string)($content['ui.budget'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?></span>
            <input type="text" name="budget" placeholder="<?= htmlspecialchars((string)($content['ui.budget'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?>" />
          </label>
        </div>

        <button type="submit" class="btn-primary hero-lead-submit"><?= htmlspecialchars((string)($content['home.lead.submit'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
        <p class="form-message" id="heroLeadMessage"></p>
      </form>
    </div>
  </div>
</section>

<section class="section section-tight" data-reveal>
  <div class="container story-layout">
    <article class="story-panel story-essay">
      <p class="eyebrow">Our Story</p>
      <h2>Quiet luxury, executed with discipline.</h2>
      <p><?= htmlspecialchars((string)($content['home.hero.subtitle'] ?? 'From modular kitchens to complete home interiors, compare top-rated professionals and start with confidence.'), ENT_QUOTES, 'UTF-8') ?></p>
      <p>HomeInteriors360 connects homeowners with verified architects, interior designers, and contractors, while keeping the process calm, premium, and transparent from first brief to handover.</p>
      <div class="story-badges">
        <span class="chip">Premium interiors</span>
        <span class="chip">Verified pros</span>
        <span class="chip">Managed execution</span>
      </div>
    </article>
    <div class="story-visual-grid">
      <div class="story-visual story-visual-large">
        <img src="<?= htmlspecialchars($heroBg ?: 'https://images.unsplash.com/photo-1616594039964-3dbbb0bd2e8f?auto=format&fit=crop&w=1200&q=80', ENT_QUOTES, 'UTF-8') ?>" alt="Interior inspiration" />
      </div>
      <div class="story-visual story-visual-small">
        <img src="<?= htmlspecialchars($heroBg2 ?: 'https://images.unsplash.com/photo-1600607688969-a5bfcd646154?auto=format&fit=crop&w=1200&q=80', ENT_QUOTES, 'UTF-8') ?>" alt="Interior detail" />
      </div>
      <div class="story-metric">
        <strong><?= count($topPros) ?></strong>
        <span>featured professionals in the network</span>
      </div>
    </div>
  </div>
</section>

<section class="section section-tight" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2>Our Process</h2>
      <p>A refined journey from first conversation to final handover.</p>
    </div>
    <div class="process-grid process-grid-home">
      <?php foreach ($processSteps as $index => $step): ?>
        <article class="process-card process-card-large">
          <strong>0<?= $index + 1 ?></strong>
          <h3><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars($step['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2>Executed Projects</h2>
      <p>Recent handovers that show how the work feels when the details are done right.</p>
    </div>
    <div class="cards-grid featured-project-grid">
      <?php foreach ($featuredProjects as $project): ?>
        <?php $projectImage = $project['media_json'][0] ?? ''; ?>
        <article class="portfolio-card project-card">
          <?php if ($projectImage): ?>
            <img src="<?= htmlspecialchars((string)$projectImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <?php endif; ?>
          <div>
            <p class="eyebrow eyebrow-dark"><?= htmlspecialchars((string)($project['pro_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <h3><?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)($project['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($project['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p>₹<?= number_format((float)($project['total_cost'] ?? 0), 0) ?></p>
            <a class="btn-link" href="/portfolio/<?= htmlspecialchars((string)$project['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['profile.project_details'] ?? 'View Project Details'), ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2><?= htmlspecialchars((string)($content['home.aggregators.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= htmlspecialchars((string)($content['home.aggregators.subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="h-scroll">
      <?php foreach ($topPros as $pro): ?>
        <article class="pro-card">
          <img src="<?= htmlspecialchars((string)($pro['profile_pic'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <div class="pro-meta">
            <h3><?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)($pro['specialization'] ?? $pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p>★ <?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></p>
            <a class="btn-link" href="/professionals/<?= htmlspecialchars((string)($pro['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['directory.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2><?= htmlspecialchars((string)($content['home.services.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    </div>
    <div class="service-grid modern-media-grid">
      <?php foreach ($services as $service): ?>
        <article class="service-card media-card">
          <div class="media-card-image-wrap">
            <?php
              $serviceKey = strtolower((string)($service['key'] ?? $service['title'] ?? ''));
              $serviceImage = $safeImage((string)($service['image'] ?? ''), (string)($serviceFallbacks[$serviceKey] ?? reset($serviceFallbacks)));
            ?>
            <img class="media-card-image" src="<?= htmlspecialchars($serviceImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= htmlspecialchars((string)($serviceFallbacks[$serviceKey] ?? reset($serviceFallbacks)), ENT_QUOTES, 'UTF-8') ?>';" />
            <div class="media-card-overlay">
              <p class="media-card-kicker"><?= htmlspecialchars((string)($content['home.services.title'] ?? 'Services'), ENT_QUOTES, 'UTF-8') ?></p>
              <h3><?= htmlspecialchars((string)($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            </div>
          </div>
          <div class="service-copy">
            <h3><?= htmlspecialchars((string)($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)($service['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <h2><?= htmlspecialchars((string)($content['home.testimonials.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="testimonials-grid">
      <?php foreach ($testimonials as $testimonial): ?>
        <article class="quote-card quote-card-modern">
          <div class="quote-visual">
            <?php
              $testimonialName = (string)($testimonial['name'] ?? '');
              $testimonialFallback = (string)($testimonialFallbacks[$testimonialName] ?? reset($testimonialFallbacks));
              $testimonialImage = $safeImage((string)($testimonial['image'] ?? ''), $testimonialFallback);
            ?>
            <img class="quote-cover" src="<?= htmlspecialchars($testimonialImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($testimonialName, ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= htmlspecialchars($testimonialFallback, ENT_QUOTES, 'UTF-8') ?>';" />
            <div class="quote-overlay">
              <h4><?= htmlspecialchars($testimonialName, ENT_QUOTES, 'UTF-8') ?></h4>
              <span><?= htmlspecialchars((string)($testimonial['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          </div>
          <p>“<?= htmlspecialchars((string)($testimonial['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>”</p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <h2><?= htmlspecialchars((string)($content['home.brands.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="brand-grid">
      <?php foreach ($brands as $brand): ?>
        <article class="brand-card">
          <?php
            $brandName = (string)($brand['name'] ?? '');
            $brandFallback = (string)($brandFallbacks[$brandName] ?? reset($brandFallbacks));
            $brandLogo = $safeImage((string)($brand['logo'] ?? $brand['url'] ?? ''), $brandFallback);
          ?>
          <div class="brand-logo-wrap"><img src="<?= htmlspecialchars($brandLogo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= htmlspecialchars($brandFallback, ENT_QUOTES, 'UTF-8') ?>';" /></div>
          <strong><?= htmlspecialchars((string)($brand['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container twin-grid">
    <div>
      <h2><?= htmlspecialchars((string)($content['home.trust.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="media-collection-grid">
        <?php foreach ($trustVisuals as $index => $item): ?>
          <article class="media-block">
            <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>" />
            <div class="media-block-copy">
              <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($normalizeCopy((string)($trustPoints[$index] ?? ''), $trustCopy[$index] ?? $item['title'], ['centralized discovery', 'quality checks', 'on-time delivery', 'verified professionals', 'transparent pricing', 'project oversight', 'reliable handover']), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <h2><?= htmlspecialchars((string)($content['home.usp.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="media-collection-grid">
        <?php foreach ($uspVisuals as $index => $item): ?>
          <article class="media-block">
            <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>" />
            <div class="media-block-copy">
              <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($normalizeCopy((string)($uspPoints[$index] ?? ''), $uspCopy[$index] ?? $item['title'], ['centralized discovery', 'lead management engine', 'verified network', 'growth content system', 'lead marketplace', 'profile management', 'brand visibility', 'growth support']), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  const form = document.getElementById('heroLeadForm');
  if (!form) return;

  const msg = document.getElementById('heroLeadMessage');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const payload = Object.fromEntries(new FormData(form).entries());
    payload.source = 'homepage';

    const res = await fetch('/api/leads', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    const data = await res.json();
    if (res.ok) {
      msg.className = 'form-message ok';
      msg.textContent = <?= json_encode((string)($content['home.lead.success'] ?? 'Thank you. Our team will call you shortly.'), JSON_UNESCAPED_UNICODE) ?>;
      form.reset();
      return;
    }

    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
