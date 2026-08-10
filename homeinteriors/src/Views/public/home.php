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
$cities = array_values(array_unique(array_merge(['Delhi', 'Gurgaon', 'Noida', 'Greater Noida', 'Faridabad', 'Ghaziabad'], is_array($payload['city_options'] ?? null) ? $payload['city_options'] : [])));
$requirements = is_array($payload['requirement_options'] ?? null) ? $payload['requirement_options'] : [];
$projectServices = ['Complete Home Interiors', 'Architecture', 'Interior Design', 'Design Only', 'Turnkey Execution', 'Renovation', 'Modular Kitchen', 'Wardrobes', 'Furniture', 'False Ceiling', 'Painting', 'Electrical', 'Plumbing', 'Flooring', 'Civil Work', 'Bathroom Renovation', 'Commercial Interior', 'Other'];
$propertyTypes = ['Apartment', 'Builder Floor', 'Independent House', 'Villa', 'Commercial', 'Office', 'Other'];
$bhkOptions = ['1', '2', '3', '4', '5+'];
$propertyStatuses = ['New possession', 'Under construction', 'Renovation', 'Occupied', 'Resale'];
$timelineOptions = ['Immediate', '<30 days', '1-3 months', '3-6 months', '6+ months'];
$styleOptions = ['Modern', 'Contemporary', 'Minimal', 'Luxury', 'Scandinavian', 'Traditional', 'Industrial', 'Classic', 'No Preference'];
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
$propertyHeroImage = $safeImage($heroBg, $editorialImages['story']);
?>

<section class="studio-hero home-property-hero" style="--hero-bg:url('<?= htmlspecialchars($propertyHeroImage, ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container studio-hero-inner" data-reveal>
    <p class="eyebrow">Professional marketplace</p>
    <h1>Find Verified Architects & Interior Designers Near You</h1>
    <p class="hero-subtitle">Tell us about your project. Compare suitable professionals, portfolios, pricing and consultations.</p>
    <div class="home-hero-lead-panel">
      <div>
        <p class="eyebrow eyebrow-dark">Free design consultation</p>
        <h2><?= htmlspecialchars((string)($content['home.lead.title'] ?? 'Get Matched'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p>Share your city, society, budget, and scope. We will route your enquiry to the right interior professional with useful project context.</p>
      </div>
      <form id="heroLeadForm" class="stack-form hero-lead-form project-brief-form" enctype="multipart/form-data">
        <input type="hidden" name="source" value="homepage">
        <input type="hidden" name="requirement_file_type" value="floor_plan">
        <input type="hidden" name="requirement_id" value="">
        <div class="project-brief-progress" aria-label="Consultation form progress">
          <span class="active" data-progress-step="1">1. Contact</span>
          <span data-progress-step="2">2. Project details</span>
        </div>
        <div class="project-brief-steps">
          <fieldset class="project-brief-step" data-step="1">
            <legend>Step 1. Contact and location</legend>
            <div class="hero-lead-grid">
              <label><span>Name</span><input type="text" name="name" required placeholder="Name *"></label>
              <label><span>Mobile</span><input type="tel" name="mobile" required placeholder="Mobile number *"></label>
              <label><span>City</span><select name="city" required><option value="">Select city *</option><?php foreach ($cities as $city): ?><option value="<?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
              <label><span>Locality</span><input name="locality" placeholder="Locality"></label>
              <label><span>Society</span><input name="society_name" placeholder="Society / project name"></label>
              <label><span>PIN code</span><input name="pincode" inputmode="numeric" maxlength="6" placeholder="PIN code"></label>
            </div>
            <label class="lead-consent"><input type="checkbox" name="contact_share_consent" value="1" required checked><span>I agree that HomeInteriors360 may share my requirement and contact details with suitable professionals for consultation and quotation.</span></label>
            <?php require __DIR__ . '/../partials/lead-consent.php'; ?>
            <button type="button" class="btn-primary hero-lead-submit" id="heroLeadStep1">Continue to project details</button>
          </fieldset>
          <div class="project-brief-step-wrap" data-step-panel="2" hidden>
          <fieldset class="project-brief-step">
            <legend>Step 2. Property</legend>
            <div class="hero-lead-grid">
              <label><span>Property type</span><select name="property_type"><option value="">Select property type</option><?php foreach ($propertyTypes as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
              <label><span>BHK</span><select name="bhk"><option value="">Select BHK</option><?php foreach ($bhkOptions as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
              <label><span>Area sq. ft.</span><input name="area_sqft" type="number" min="1" placeholder="Area"></label>
              <label><span>Property status</span><select name="property_status"><option value="">Select status</option><?php foreach ($propertyStatuses as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
              <label><span>Timeline</span><select name="timeline"><option value="">Select timeline</option><?php foreach ($timelineOptions as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            </div>
          </fieldset>
          <fieldset class="project-brief-step">
            <legend>Service, Budget and Style</legend>
            <label class="project-service-group"><span>Services required *</span><select name="services[]" class="standard-multi-select" multiple required><?php foreach ($projectServices as $service): ?><option value="<?= htmlspecialchars($service, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($service, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <div class="hero-lead-grid">
              <label><span>Budget</span><select name="budget_range"><option value="">Select budget</option><?php require __DIR__ . '/../partials/budget-options.php'; ?></select></label>
              <label><span>Style</span><select name="style_preference"><option value="">No Preference</option><?php foreach ($styleOptions as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            </div>
          </fieldset>
          <fieldset class="project-brief-step">
            <legend>Files and Contact Preference</legend>
            <label class="file-field"><span>Floor plan / site photos / inspiration images / existing quotation</span><input type="file" name="requirement_files[]" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" multiple></label>
            <div class="hero-lead-grid">
              <label><span>Email</span><input type="email" name="email" placeholder="Email"></label>
              <label><span>Preferred contact</span><select name="preferred_contact_method"><option value="">Any method</option><option>Phone</option><option>WhatsApp</option><option>Email</option></select></label>
              <label><span>Consultation time</span><input name="preferred_consultation_time" placeholder="e.g. Today evening"></label>
            </div>
            <textarea name="notes" rows="2" placeholder="Anything else we should know?"></textarea>
          </fieldset>
          </div>
        </div>
        <button type="submit" class="btn-primary hero-lead-submit" id="heroLeadFinal" hidden>Get Free Design Consultation</button>
        <p class="form-message" id="heroLeadMessage"></p>
      </form>
    </div>
    <div class="home-hero-paths">
      <a href="/home-interior-hire-a-designer">Hire an interior designer</a>
      <a href="/pricing#designer-quotation-builder">Quotation builder for designers</a>
      <a href="/lead-marketplace">Buy interior leads</a>
    </div>
  </div>
</section>

<section class="section designer-feature-band" id="designer-quotation-builder" data-reveal>
  <div class="container">
    <div class="designer-feature-grid">
      <div>
        <p class="eyebrow eyebrow-dark">For interior designers</p>
        <h2>Quotation Builder + Proposal Generator at ₹399/month.</h2>
        <p>Create itemised quotes, room-wise scope, client-ready proposal PDFs, status tracking, and payment milestones without building your own sales software.</p>
        <div class="feature-price-row"><span class="mrp">₹2,999/month</span><strong>₹399/month</strong><em>87% off launch offer</em></div>
        <ul class="benefit-list">
          <li>Access only your assigned leads and quotations.</li>
          <li>Generate branded proposal PDFs and WhatsApp-ready links.</li>
          <li>Track draft, sent, viewed, accepted, and revision-requested quotes.</li>
          <li>Use rate cards, packages, taxes, discounts, and payment milestones.</li>
        </ul>
        <a class="btn-primary" href="/pricing#designer-quotation-builder">Register for designer access</a>
      </div>
      <form class="designer-feature-form" data-designer-feature-form>
        <h3>Register interest</h3>
        <input name="name" required placeholder="Name">
        <input name="phone" required placeholder="Phone">
        <input name="email" type="email" placeholder="Email">
        <input name="company_name" placeholder="Studio / company name">
        <input name="city" placeholder="City">
        <input name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="Create password for designer login">
        <textarea name="message" rows="3" placeholder="Tell us your current quotation workflow"></textarea>
        <label class="lead-consent"><input type="checkbox" name="consent" value="1" required checked><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
        <button class="btn-primary" type="submit">Get ₹399/month offer</button>
        <p class="form-message"></p>
      </form>
    </div>
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
  const loadRazorpay = () => new Promise((resolve, reject) => {
    if (window.Razorpay) return resolve();
    const script = document.createElement('script');
    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
    script.onload = resolve;
    script.onerror = () => reject(new Error('Unable to load Razorpay checkout.'));
    document.head.appendChild(script);
  });

  document.querySelectorAll('[data-designer-feature-form]').forEach((featureForm) => {
    featureForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!featureForm.reportValidity()) return;
      const message = featureForm.querySelector('.form-message');
      const submit = featureForm.querySelector('button[type="submit"]');
      const payload = Object.fromEntries(new FormData(featureForm).entries());
      message.className = 'form-message';
      message.textContent = 'Creating secure Razorpay checkout...';
      if (submit) submit.disabled = true;
      const response = await fetch('/api/designer-feature-registrations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await response.json();
      if (!response.ok) {
        message.className = 'form-message error';
        message.textContent = data.error || 'Registration failed.';
        if (submit) submit.disabled = false;
        return;
      }
      try {
        await loadRazorpay();
        const checkout = new Razorpay({
          key: data.key_id,
          amount: data.amount,
          currency: data.currency || 'INR',
          name: 'HomeInteriors360',
          description: 'Quotation Builder + Proposal Generator',
          order_id: data.order_id,
          prefill: { name: payload.name || '', email: payload.email || '', contact: payload.phone || '' },
          handler: async (payment) => {
            const verify = await fetch('/api/designer-feature-registrations/verify', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payment),
            });
            const result = await verify.json();
            if (verify.ok) {
              message.className = 'form-message ok';
              message.textContent = 'Payment successful. Opening your proposal builder...';
              window.location.href = result.redirect_url || '/designer';
              return;
            }
            message.className = 'form-message error';
            message.textContent = result.error || 'Payment verification failed.';
            if (submit) submit.disabled = false;
          },
          modal: { ondismiss: () => { message.className = 'form-message error'; message.textContent = 'Payment cancelled.'; if (submit) submit.disabled = false; } },
        });
        checkout.on('payment.failed', (response) => {
          message.className = 'form-message error';
          message.textContent = response.error?.description || 'Payment failed.';
          if (submit) submit.disabled = false;
        });
        checkout.open();
      } catch (error) {
        message.className = 'form-message error';
        message.textContent = error.message || 'Unable to open Razorpay checkout.';
        if (submit) submit.disabled = false;
      }
    });
  });

  const form = document.getElementById('heroLeadForm');
  if (!form) return;

  const msg = document.getElementById('heroLeadMessage');
  const step1Button = document.getElementById('heroLeadStep1');
  const finalButton = document.getElementById('heroLeadFinal');
  const step2Panel = form.querySelector('[data-step-panel="2"]');
  const progressSteps = form.querySelectorAll('[data-progress-step]');
  const requirementIdInput = form.elements.requirement_id;

  const setProgress = (step) => {
    progressSteps.forEach((item) => item.classList.toggle('active', item.dataset.progressStep === String(step)));
  };

  const validateStep = (step) => {
    const panel = form.querySelector(`[data-step="${step}"]`);
    if (!panel) return true;
    const fields = panel.querySelectorAll('input, select, textarea');
    for (const field of fields) {
      if (!field.checkValidity()) {
        field.reportValidity();
        return false;
      }
    }
    return true;
  };

  const basicPayload = () => {
    const fd = new FormData();
    ['source', 'name', 'mobile', 'city', 'locality', 'society_name', 'pincode'].forEach((name) => {
      if (form.elements[name]) fd.set(name, form.elements[name].value || '');
    });
    if (form.elements.contact_share_consent?.checked) fd.set('contact_share_consent', '1');
    if (form.elements.lead_consent?.checked) fd.set('lead_consent', '1');
    fd.set('services[]', 'Free Design Consultation');
    return fd;
  };

  step1Button?.addEventListener('click', async () => {
    if (!validateStep(1)) return;
    step1Button.disabled = true;
    msg.className = 'form-message';
    msg.textContent = 'Saving your contact details...';

    const res = await fetch('/api/project-requirements', {
      method: 'POST',
      body: basicPayload(),
    });
    const data = await res.json();
    if (!res.ok) {
      msg.className = 'form-message error';
      msg.textContent = data.error || 'Could not save step 1. Please try again.';
      step1Button.disabled = false;
      return;
    }

    requirementIdInput.value = data.requirement_id || '';
    step2Panel.hidden = false;
    finalButton.hidden = false;
    step1Button.textContent = 'Step 1 saved';
    msg.className = 'form-message ok';
    msg.textContent = 'Step 1 saved. Please complete your project details.';
    setProgress(2);
    step2Panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const requirementId = requirementIdInput.value;
    if (!requirementId) {
      msg.className = 'form-message error';
      msg.textContent = 'Please complete Step 1 first.';
      return;
    }
    const payload = new FormData(form);
    msg.className = 'form-message';
    msg.textContent = 'Saving your complete project brief...';

    const res = await fetch(`/api/project-requirements/${encodeURIComponent(requirementId)}`, {
      method: 'POST',
      body: payload,
    });

    const data = await res.json();
    if (res.ok) {
      msg.className = 'form-message ok';
      msg.textContent = data.message || <?= json_encode((string)($content['home.lead.success'] ?? 'Thank you. Your project brief has been captured.'), JSON_UNESCAPED_UNICODE) ?>;
      form.reset();
      requirementIdInput.value = '';
      step2Panel.hidden = true;
      finalButton.hidden = true;
      step1Button.hidden = false;
      step1Button.disabled = false;
      step1Button.textContent = 'Continue to project details';
      setProgress(1);
      if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        window.jQuery(form).find('.standard-multi-select').val(null).trigger('change');
      }
      return;
    }

    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
