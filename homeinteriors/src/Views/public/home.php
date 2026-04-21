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
      <form id="heroLeadForm" class="multi-step">
        <div class="step active" data-step="1">
          <label><?= htmlspecialchars((string)($content['home.lead.step1_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
          <select name="city" required>
            <option value=""><?= htmlspecialchars((string)($content['home.lead.step1_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php foreach ($cities as $city): ?>
              <option value="<?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="step" data-step="2">
          <label><?= htmlspecialchars((string)($content['home.lead.step2_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
          <select name="requirement" required>
            <option value=""><?= htmlspecialchars((string)($content['home.lead.step2_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
            <?php foreach ($requirements as $req): ?>
              <option value="<?= htmlspecialchars((string)$req, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$req, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="step" data-step="3">
          <label><?= htmlspecialchars((string)($content['home.lead.step3_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
          <input type="text" name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <input type="tel" name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        </div>

        <div class="step-actions">
          <button type="button" class="btn-muted" id="heroPrev"><?= htmlspecialchars((string)($content['home.lead.step_prev'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
          <button type="button" class="btn-primary" id="heroNext"><?= htmlspecialchars((string)($content['home.lead.step_next'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
          <button type="submit" class="btn-primary" id="heroSubmit"><?= htmlspecialchars((string)($content['home.lead.submit'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
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
          <span class="service-icon"><?= htmlspecialchars((string)($service['icon'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
          <h3><?= htmlspecialchars((string)($service['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars((string)($service['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
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
          <span class="brand-pill"><?= htmlspecialchars((string)($brand['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
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

  const steps = Array.from(form.querySelectorAll('.step'));
  const prevBtn = document.getElementById('heroPrev');
  const nextBtn = document.getElementById('heroNext');
  const submitBtn = document.getElementById('heroSubmit');
  const msg = document.getElementById('heroLeadMessage');
  let current = 0;

  function renderStep() {
    steps.forEach((step, idx) => step.classList.toggle('active', idx === current));
    prevBtn.style.display = current === 0 ? 'none' : 'inline-flex';
    nextBtn.style.display = current === steps.length - 1 ? 'none' : 'inline-flex';
    submitBtn.style.display = current === steps.length - 1 ? 'inline-flex' : 'none';
  }

  function validateCurrent() {
    const fields = steps[current].querySelectorAll('input, select');
    return Array.from(fields).every((field) => field.reportValidity());
  }

  nextBtn.addEventListener('click', () => {
    if (!validateCurrent()) return;
    current = Math.min(current + 1, steps.length - 1);
    renderStep();
  });

  prevBtn.addEventListener('click', () => {
    current = Math.max(current - 1, 0);
    renderStep();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validateCurrent()) return;
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
      current = 0;
      renderStep();
      return;
    }

    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed';
  });

  renderStep();
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
