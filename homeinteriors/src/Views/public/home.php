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
    <div class="service-grid">
      <?php foreach ($services as $service): ?>
        <article class="service-card">
          <?php if (!empty($service['image'])): ?>
            <img class="card-image" src="<?= htmlspecialchars((string)$service['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <?php endif; ?>
          <div class="service-copy">
            <span class="service-icon"><?= htmlspecialchars((string)($service['icon'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
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
    <div class="h-scroll">
      <?php foreach ($testimonials as $testimonial): ?>
        <article class="quote-card">
          <?php if (!empty($testimonial['image'])): ?>
            <img class="quote-avatar" src="<?= htmlspecialchars((string)$testimonial['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($testimonial['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <?php endif; ?>
          <p>“<?= htmlspecialchars((string)($testimonial['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>”</p>
          <h4><?= htmlspecialchars((string)($testimonial['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h4>
          <span><?= htmlspecialchars((string)($testimonial['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <h2><?= htmlspecialchars((string)($content['home.brands.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="marquee">
      <div class="marquee-track">
        <?php for ($i = 0; $i < 2; $i++): foreach ($brands as $brand): ?>
          <span class="brand-pill">
            <?php $brandLogo = $brand['logo'] ?? $brand['url'] ?? ''; if (!empty($brandLogo)): ?>
              <img src="<?= htmlspecialchars((string)$brandLogo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($brand['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
            <?php endif; ?>
            <span><?= htmlspecialchars((string)($brand['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
          </span>
        <?php endforeach; endfor; ?>
      </div>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container twin-grid">
    <div>
      <h2><?= htmlspecialchars((string)($content['home.trust.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="h-scroll mini-scroll">
        <?php foreach ($trustPoints as $point): ?>
          <article class="mini-card"><?= htmlspecialchars((string)$point, ENT_QUOTES, 'UTF-8') ?></article>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <h2><?= htmlspecialchars((string)($content['home.usp.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="h-scroll mini-scroll">
        <?php foreach ($uspPoints as $point): ?>
          <article class="mini-card"><?= htmlspecialchars((string)$point, ENT_QUOTES, 'UTF-8') ?></article>
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
