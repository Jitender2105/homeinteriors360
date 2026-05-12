<?php
require __DIR__ . '/../partials/header.php';

$heroAssets = is_array($payload['hero_assets'] ?? null) ? $payload['hero_assets'] : [];
$heroBg = $heroAssets[0] ?? '';
$heroBg2 = $heroAssets[1] ?? $heroBg;
$topPros = is_array($payload['top_pros'] ?? null) ? $payload['top_pros'] : [];
$services = is_array($payload['services'] ?? null) ? $payload['services'] : [];
$testimonials = is_array($payload['testimonials'] ?? null) ? $payload['testimonials'] : [];
$brands = is_array($payload['brands'] ?? null) ? $payload['brands'] : [];
$trustPoints = is_array($payload['trust_points'] ?? null) ? $payload['trust_points'] : [];
$uspPoints = is_array($payload['usp_points'] ?? null) ? $payload['usp_points'] : [];
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
  ['title' => 'Verified Professionals', 'image' => 'https://images.pexels.com/photos/8403087/pexels-photo-8403087.jpeg?auto=compress&cs=tinysrgb&w=1200'],
  ['title' => 'Transparent Pricing', 'image' => 'https://images.pexels.com/photos/6585750/pexels-photo-6585750.jpeg?auto=compress&cs=tinysrgb&w=1200'],
  ['title' => 'Quality Checks', 'image' => 'https://images.pexels.com/photos/9011226/pexels-photo-9011226.jpeg?auto=compress&cs=tinysrgb&w=1200'],
  ['title' => 'On-Time Delivery', 'image' => 'https://images.pexels.com/photos/3615613/pexels-photo-3615613.jpeg?auto=compress&cs=tinysrgb&w=1200'],
];
$uspVisuals = [
  ['title' => 'Centralized Discovery', 'image' => 'https://images.pexels.com/photos/6283979/pexels-photo-6283979.jpeg?auto=compress&cs=tinysrgb&w=1200'],
  ['title' => 'Lead Management Engine', 'image' => 'https://images.pexels.com/photos/8867439/pexels-photo-8867439.jpeg?auto=compress&cs=tinysrgb&w=1200'],
  ['title' => 'Verified Network', 'image' => 'https://images.pexels.com/photos/3184325/pexels-photo-3184325.jpeg?auto=compress&cs=tinysrgb&w=1200'],
  ['title' => 'Growth Content System', 'image' => 'https://images.pexels.com/photos/3183150/pexels-photo-3183150.jpeg?auto=compress&cs=tinysrgb&w=1200'],
];
$serviceFallbacks = [
  'kitchen' => 'https://images.unsplash.com/photo-1556912167-f556f1f39fdf?auto=format&fit=crop&w=1200&q=80',
  'wardrobe' => 'https://images.pexels.com/photos/6585750/pexels-photo-6585750.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'full_home' => 'https://images.unsplash.com/photo-1615529182904-14819c35db37?auto=format&fit=crop&w=1200&q=80',
];
$testimonialFallbacks = [
  'Priya S' => 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=900',
  'Vikas A' => 'https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=900',
  'Karan M' => 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=900',
];
$brandFallbacks = [
  'Hafele' => 'https://upload.wikimedia.org/wikipedia/commons/2/2a/Haefele_Logo.png',
  'Hettich' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Logo_of_Hettich_%28company%29.svg/960px-Logo_of_Hettich_%28company%29.svg.png',
  'Asian Paints' => 'https://upload.wikimedia.org/wikipedia/commons/0/09/Asian_paints_logo.jpg',
  'Kajaria' => 'https://companieslogo.com/img/orig/KAJARIACER.NS_BIG-300b5e39.png?download=true&t=1720244492',
];
$cities = is_array($payload['city_options'] ?? null) ? $payload['city_options'] : [];
$requirements = is_array($payload['requirement_options'] ?? null) ? $payload['requirement_options'] : [];
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
              <p><?= htmlspecialchars((string)($trustPoints[$index] ?? $item['title']), ENT_QUOTES, 'UTF-8') ?></p>
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
              <p><?= htmlspecialchars((string)($uspPoints[$index] ?? $item['title']), ENT_QUOTES, 'UTF-8') ?></p>
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
