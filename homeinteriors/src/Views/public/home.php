<?php
require __DIR__ . '/../partials/header.php';

$heroAssets = is_array($payload['hero_assets'] ?? null) ? $payload['hero_assets'] : [];
$heroBg = $heroAssets[0] ?? '';
$heroBg2 = $heroAssets[1] ?? $heroBg;
$topPros = is_array($payload['top_pros'] ?? null) ? $payload['top_pros'] : [];
$featuredProjects = is_array($payload['featured_projects'] ?? null) ? $payload['featured_projects'] : [];
$featuredProperties = array_slice(is_array($payload['featured_properties'] ?? null) ? $payload['featured_properties'] : [], 0, 4);
$propertyFilters = is_array($payload['property_filters'] ?? null) ? $payload['property_filters'] : [];
$services = is_array($payload['services'] ?? null) ? $payload['services'] : [];
$testimonials = is_array($payload['testimonials'] ?? null) ? $payload['testimonials'] : [];
$brands = is_array($payload['brands'] ?? null) ? $payload['brands'] : [];
$trustPoints = is_array($payload['trust_points'] ?? null) ? $payload['trust_points'] : [];
$uspPoints = is_array($payload['usp_points'] ?? null) ? $payload['usp_points'] : [];
$defaultImage = 'https://images.pexels.com/photos/14613699/pexels-photo-14613699.jpeg?auto=compress&cs=tinysrgb&w=1200';
$safeImage = static function (string $url, string $fallback): string {
  $url = trim($url);
  if ($url === '' || !preg_match('~^https?://~i', $url)) {
    return $fallback;
  }
  if (str_contains($url, 'encrypted-tbn0.gstatic.com') || str_contains($url, 'gstatic.com/images?q=')) {
    return $fallback;
  }
  if (str_contains($url, '1616594039964-3dbbb0bd2e8f')) {
    return $fallback;
  }
  return $url;
};
$trustVisuals = [
  ['title' => 'Verified Professionals', 'image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=900&q=85'],
  ['title' => 'Transparent Pricing', 'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=900&q=85'],
  ['title' => 'Project Oversight', 'image' => 'https://images.pexels.com/photos/15124969/pexels-photo-15124969.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Reliable Handover', 'image' => 'https://images.pexels.com/photos/9976128/pexels-photo-9976128.jpeg?auto=compress&cs=tinysrgb&w=900'],
];
$uspVisuals = [
  ['title' => 'Lead Marketplace', 'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=900&q=85'],
  ['title' => 'Profile Management', 'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=85'],
  ['title' => 'Brand Visibility', 'image' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=85'],
  ['title' => 'Growth Support', 'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=900&q=85'],
];
$serviceFallbacks = [
  'kitchen' => 'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=900&q=85',
  'modular_kitchen' => 'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=900&q=85',
  'wardrobe' => 'https://images.pexels.com/photos/36221937/pexels-photo-36221937.jpeg?auto=compress&cs=tinysrgb&w=900',
  'full_home' => 'https://images.pexels.com/photos/14613699/pexels-photo-14613699.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'full_home_interiors' => 'https://images.pexels.com/photos/14613699/pexels-photo-14613699.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'living_room' => 'https://images.pexels.com/photos/9976128/pexels-photo-9976128.jpeg?auto=compress&cs=tinysrgb&w=900',
  'bedroom' => 'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=900&q=85',
  'renovation' => 'https://images.pexels.com/photos/15124969/pexels-photo-15124969.jpeg?auto=compress&cs=tinysrgb&w=900',
];
$testimonialFallbacks = [
  'Priya S' => 'https://images.pexels.com/photos/7031874/pexels-photo-7031874.jpeg?auto=compress&cs=tinysrgb&w=900',
  'Vikas A' => 'https://images.pexels.com/photos/14613699/pexels-photo-14613699.jpeg?auto=compress&cs=tinysrgb&w=900',
  'Karan M' => 'https://images.pexels.com/photos/9976128/pexels-photo-9976128.jpeg?auto=compress&cs=tinysrgb&w=900',
];
$brandFallbacks = [
  'Hafele' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=900&q=85',
  'Hettich' => 'https://images.pexels.com/photos/36221937/pexels-photo-36221937.jpeg?auto=compress&cs=tinysrgb&w=900',
  'Asian Paints' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?auto=format&fit=crop&w=900&q=85',
  'Kajaria' => 'https://images.pexels.com/photos/10481158/pexels-photo-10481158.jpeg?auto=compress&cs=tinysrgb&w=900',
];
$editorialImages = [
  'story' => 'https://images.pexels.com/photos/14613699/pexels-photo-14613699.jpeg?auto=compress&cs=tinysrgb&w=1400',
  'detail' => 'https://images.pexels.com/photos/9976128/pexels-photo-9976128.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'studio' => 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&w=1400&q=85',
  'materials' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1200&q=85',
  'render_1' => 'https://images.pexels.com/photos/7031874/pexels-photo-7031874.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'render_2' => 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=85',
  'render_3' => 'https://images.pexels.com/photos/8135493/pexels-photo-8135493.jpeg?auto=compress&cs=tinysrgb&w=1200',
];
$projectFallbacks = [
  'kitchen' => 'https://images.pexels.com/photos/10827348/pexels-photo-10827348.jpeg?auto=compress&cs=tinysrgb&w=900',
  'wardrobe' => 'https://images.pexels.com/photos/36221937/pexels-photo-36221937.jpeg?auto=compress&cs=tinysrgb&w=900',
  'renovation' => 'https://images.pexels.com/photos/15124969/pexels-photo-15124969.jpeg?auto=compress&cs=tinysrgb&w=900',
  'workspace' => 'https://images.pexels.com/photos/16501689/pexels-photo-16501689.jpeg?auto=compress&cs=tinysrgb&w=900',
  'full_home' => 'https://images.pexels.com/photos/20116484/pexels-photo-20116484.jpeg?auto=compress&cs=tinysrgb&w=900',
  'default' => 'https://images.pexels.com/photos/7031874/pexels-photo-7031874.jpeg?auto=compress&cs=tinysrgb&w=900',
];
$serviceFallbackFor = static function (string $key) use ($serviceFallbacks, $defaultImage): string {
  $normalized = strtolower(trim(preg_replace('~[^a-z0-9]+~i', '_', $key), '_'));
  if (isset($serviceFallbacks[$normalized])) {
    return $serviceFallbacks[$normalized];
  }
  foreach ($serviceFallbacks as $fallbackKey => $fallbackImage) {
    if ($fallbackKey !== '' && (str_contains($normalized, $fallbackKey) || str_contains($fallbackKey, $normalized))) {
      return $fallbackImage;
    }
  }
  return $defaultImage;
};
$projectFallbackFor = static function (array $project) use ($projectFallbacks): string {
  $name = strtolower((string)($project['project_name'] ?? ''));
  $workType = strtolower((string)($project['work_type'] ?? ''));
  $haystack = $name . ' ' . $workType;
  if (str_contains($haystack, 'kitchen')) return $projectFallbacks['kitchen'];
  if (str_contains($name, 'renovation')) return $projectFallbacks['renovation'];
  if (str_contains($name, 'home') || str_contains($name, 'bhk') || str_contains($name, 'villa')) return $projectFallbacks['full_home'];
  if (str_contains($workType, 'renovation')) return $projectFallbacks['renovation'];
  if (str_contains($haystack, 'workspace') || str_contains($haystack, 'office')) return $projectFallbacks['workspace'];
  if (str_contains($haystack, 'wardrobe')) return $projectFallbacks['wardrobe'];
  return $projectFallbacks['default'];
};
$cities = is_array($payload['city_options'] ?? null) ? $payload['city_options'] : [];
$requirements = is_array($payload['requirement_options'] ?? null) ? $payload['requirement_options'] : [];
$processSteps = [
  ['title' => 'DISCOVER', 'text' => 'We understand the city, budget, scope, and locality so the right professionals can be shortlisted with context.'],
  ['title' => 'COMPARE', 'text' => 'Verified architects, designers, and contractors are compared by portfolio, rating, work type, and service area.'],
  ['title' => 'CONNECT', 'text' => 'Your requirement is shared with the selected aggregator or professional team for a focused first conversation.'],
  ['title' => 'MANAGE', 'text' => 'Premium partners can use our backend for profile, portfolio, testimonials, and lead growth management.'],
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
$propertyMoney = static function (float $amount): string {
  if ($amount <= 0) return 'Price on request';
  if ($amount >= 10000000) return '₹' . rtrim(rtrim(number_format($amount / 10000000, 2), '0'), '.') . ' Cr';
  if ($amount >= 100000) return '₹' . rtrim(rtrim(number_format($amount / 100000, 2), '0'), '.') . ' L';
  return '₹' . number_format($amount, 0);
};
$propertyHeroImage = trim((string)($featuredProperties[0]['exterior_image'] ?? $featuredProperties[0]['cover_image'] ?? ''));
if ($propertyHeroImage === '') {
  $propertyHeroImage = $safeImage($heroBg, $editorialImages['story']);
}
?>

<section class="studio-hero home-property-hero" style="--hero-bg:url('<?= htmlspecialchars($propertyHeroImage, ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container studio-hero-inner" data-reveal>
    <p class="eyebrow">Buy. Rent. Design.</p>
    <h1>Find your next home, then make it unmistakably yours.</h1>
    <p class="hero-subtitle">Explore homes and residential projects for sale or rent, compare layouts and prices, and connect with verified interior professionals through one platform.</p>
    <div class="home-hero-lead-panel">
      <div>
        <p class="eyebrow eyebrow-dark">Free design consultation</p>
        <h2><?= htmlspecialchars((string)($content['home.lead.title'] ?? 'Get Free Interior Design Consultation'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p>Already own, bought, or rented a home? Share your requirement and we will connect you with the right verified interior professional.</p>
      </div>
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
          <?php require __DIR__ . '/../partials/society-field.php'; ?>
          <label>
            <span><?= htmlspecialchars((string)($content['ui.budget'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?></span>
            <select name="budget">
              <option value=""><?= htmlspecialchars((string)($content['ui.budget'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?></option>
              <?php require __DIR__ . '/../partials/budget-options.php'; ?>
            </select>
          </label>
        </div>
        <?php require __DIR__ . '/../partials/lead-consent.php'; ?>
        <button type="submit" class="btn-primary hero-lead-submit"><?= htmlspecialchars((string)($content['home.lead.submit'] ?? 'Request Callback'), ENT_QUOTES, 'UTF-8') ?></button>
        <p class="form-message" id="heroLeadMessage"></p>
      </form>
    </div>
    <div class="home-hero-paths">
      <a href="/properties?listing_for=buy">Homes for sale</a>
      <a href="/properties?listing_for=rent">Homes for rent</a>
      <a href="/home-interior-hire-a-designer">Hire an interior designer</a>
    </div>
  </div>
</section>

<section class="section home-property-section" data-reveal>
  <div class="container">
    <div class="section-head-row">
      <div>
        <p class="eyebrow eyebrow-dark">Property marketplace</p>
        <h2>Buy or rent with the details already in view.</h2>
        <p>Compare project photos, configurations, floor plans, current prices, amenities, location advantages, and available inventory.</p>
      </div>
      <a class="btn-link" href="/properties">Explore all properties</a>
    </div>
    <form class="home-property-search" method="get" action="/properties">
      <div class="home-property-mode">
        <label><input type="radio" name="listing_for" value="buy" checked><span>Buy</span></label>
        <label><input type="radio" name="listing_for" value="rent"><span>Rent</span></label>
      </div>
      <label class="home-property-keyword"><span>Location or project</span><input name="q" placeholder="Search project, locality, builder or city"></label>
      <label><span>City</span><select name="city"><option value="">All cities</option><?php foreach (($propertyFilters['cities'] ?? []) as $option): ?><option><?= htmlspecialchars((string)$option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label><span>Property type</span><select name="property_type"><option value="">All property types</option><?php foreach (($propertyFilters['property_types'] ?? []) as $option): ?><option><?= htmlspecialchars((string)$option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <button class="btn-primary" type="submit">Search homes</button>
    </form>
    <?php if ($featuredProperties): ?>
      <div class="home-property-grid">
        <?php foreach ($featuredProperties as $property): ?>
          <article class="home-property-card">
            <a class="home-property-image" href="/property/<?= htmlspecialchars((string)$property['slug'], ENT_QUOTES, 'UTF-8') ?>">
              <img src="<?= htmlspecialchars((string)($property['cover_image'] ?: '/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$property['project_name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
              <span><?= ($property['listing_for'] ?? '') === 'both' ? 'BUY / RENT' : htmlspecialchars(strtoupper((string)$property['listing_for']), ENT_QUOTES, 'UTF-8') ?></span>
            </a>
            <div>
              <p class="eyebrow eyebrow-dark"><?= htmlspecialchars((string)$property['property_type'], ENT_QUOTES, 'UTF-8') ?></p>
              <h3><a href="/property/<?= htmlspecialchars((string)$property['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$property['project_name'], ENT_QUOTES, 'UTF-8') ?></a></h3>
              <p><?= htmlspecialchars(trim((string)$property['locality'] . ', ' . (string)$property['city'], ', '), ENT_QUOTES, 'UTF-8') ?></p>
              <div class="home-property-facts">
                <span><small>Configuration</small><?= htmlspecialchars((string)($property['configurations'] ?: 'Ask for details'), ENT_QUOTES, 'UTF-8') ?></span>
                <span><small>Starting price</small><?= htmlspecialchars($propertyMoney((float)$property['price_min']), ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <a class="btn-link" href="/property/<?= htmlspecialchars((string)$property['slug'], ENT_QUOTES, 'UTF-8') ?>">View project</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="home-property-empty"><p>Property projects can be published from the property backend and will appear here automatically.</p><a class="btn-primary" href="/properties">Browse properties</a></div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-tight studio-story" data-reveal>
  <div class="container story-layout">
    <article class="story-panel story-essay">
      <p class="eyebrow">Our Story</p>
      <h2>A curated marketplace for homes that should feel personal, not random.</h2>
      <p>HomeInteriors360 was built for homeowners who want refined choices without chasing ten vendors, screenshots, and half-trusted recommendations.</p>
      <p>We bring aggregator discovery, professional profiles, project portfolios, reviews, pricing signals, and lead routing into one experience, so the journey from search to shortlist feels considered.</p>
      <div class="story-badges">
        <span class="chip">Verified aggregators</span>
        <span class="chip">Portfolio-led discovery</span>
        <span class="chip">Lead-backed matching</span>
      </div>
    </article>
    <div class="story-visual-grid">
      <div class="story-visual story-visual-large">
        <img src="<?= htmlspecialchars($editorialImages['detail'], ENT_QUOTES, 'UTF-8') ?>" alt="Interior inspiration" />
      </div>
      <div class="story-visual story-visual-small">
        <img src="<?= htmlspecialchars($editorialImages['materials'], ENT_QUOTES, 'UTF-8') ?>" alt="Interior material palette" />
      </div>
      <div class="story-metric">
        <strong><?= count($topPros) ?></strong>
        <span>featured aggregators and professionals in the network</span>
      </div>
    </div>
  </div>
</section>

<section class="section studio-why" data-reveal>
  <div class="container">
    <div class="section-head section-head-wide">
      <p class="eyebrow eyebrow-dark">Why HomeInteriors360?</p>
      <h2>Luxury discovery should feel calm, but the backend must stay sharp.</h2>
    </div>
    <div class="why-studio-grid">
      <article>
        <span>01</span>
        <h3>Bespoke, not anonymous</h3>
        <p>Professionals are shown with real work type, service area, portfolio detail, ratings, and project history.</p>
      </article>
      <article>
        <span>02</span>
        <h3>Aggregator-led choice</h3>
        <p>Homeowners can compare premium studios, architects, contractors, and full-service aggregators in one place.</p>
      </article>
      <article>
        <span>03</span>
        <h3>Lead flow with context</h3>
        <p>Each enquiry carries city, locality, budget, source, and professional ID so sales conversations start cleaner.</p>
      </article>
    </div>
  </div>
</section>

<section class="section section-tight" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2>Our Process</h2>
      <p>A refined journey from search intent to the right professional conversation.</p>
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
      <h2>Featured Aggregators</h2>
      <p>Verified professionals and studios, presented through the work they have delivered.</p>
    </div>
    <div class="aggregator-showcase">
      <?php foreach ($topPros as $pro): ?>
        <article class="aggregator-card">
          <img src="<?= htmlspecialchars((string)($pro['profile_pic'] ?? $defaultImage), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <div>
            <p class="eyebrow eyebrow-dark"><?= htmlspecialchars((string)($pro['city'] ?? 'Delhi NCR'), ENT_QUOTES, 'UTF-8') ?></p>
            <h3><?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)($pro['specialization'] ?? $pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p>Rating <?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> / 5</p>
            <a class="btn-link" href="/professionals/<?= htmlspecialchars((string)($pro['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['directory.cta'] ?? 'View Profile'), ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section project-gallery-section" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2>Executed Projects</h2>
      <p>Recent handovers that show how the work feels when the details are done right.</p>
    </div>
    <div class="cards-grid featured-project-grid">
      <?php foreach ($featuredProjects as $project): ?>
        <?php
          $projectFallback = $projectFallbackFor((array)$project);
          $projectImage = $projectFallback;
        ?>
        <article class="portfolio-card project-card">
          <img src="<?= htmlspecialchars((string)$projectImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= htmlspecialchars($projectFallback, ENT_QUOTES, 'UTF-8') ?>';" />
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

<section class="section renders-section" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2>Concept Direction</h2>
      <p>Material mood, light, and planning cues that help homeowners choose with confidence.</p>
    </div>
    <div class="studio-image-strip">
      <img src="<?= htmlspecialchars($editorialImages['render_1'], ENT_QUOTES, 'UTF-8') ?>" alt="Interior concept render" />
      <img src="<?= htmlspecialchars($editorialImages['render_2'], ENT_QUOTES, 'UTF-8') ?>" alt="Interior concept detail" />
      <img src="<?= htmlspecialchars($editorialImages['render_3'], ENT_QUOTES, 'UTF-8') ?>" alt="Interior material direction" />
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
              $serviceFallback = $serviceFallbackFor($serviceKey);
              $serviceImage = $safeImage((string)($service['image'] ?? ''), $serviceFallback);
            ?>
            <img class="media-card-image" src="<?= htmlspecialchars($serviceImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= htmlspecialchars($serviceFallback, ENT_QUOTES, 'UTF-8') ?>';" />
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
