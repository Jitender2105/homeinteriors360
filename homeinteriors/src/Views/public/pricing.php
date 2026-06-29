<?php
require __DIR__ . '/../partials/header.php';

$plans = [
    [
        'id' => 'lead_purchase',
        'title' => 'Lead Purchase Mode',
        'price' => 'Pay per lead',
        'description' => 'Buy qualified homeowner leads charged by location, locality, and budget bands.',
        'items' => [
            'Targeted lead routing by city, locality, and budget',
            'Pay only for the leads you want to activate',
            'Fast access to high-intent homeowners',
            'Ideal for architects and designers who want flexible scale',
        ],
    ],
    [
        'id' => 'managed_account',
        'title' => 'Managed Growth Account',
        'price' => 'Custom retainer',
        'description' => 'We manage your full interior design presence end-to-end, including leads and brand growth.',
        'items' => [
            'Lead generation, nurturing, and follow-up support',
            'Social media and Google Business Profile management',
            'Profile, portfolio, and testimonial management',
            'A good fit for teams that want a hands-off growth engine',
        ],
    ],
];

$whyBuyPoints = [
    'Verified demand from homeowners actively looking for architects and interior designers',
    'Sales team support for onboarding, plan selection, and account setup',
    'A platform built to convert traffic into qualified design conversations',
    'Flexible growth paths for solo consultants, studios, and turnkey firms',
];

$offerings = [
  'Lead purchase pricing by locality, city, and budget',
  'Managed profile and portfolio publishing',
  'Social media and Google Business Page support',
  'Customer testimonial collection and reputation building',
  'Priority visibility for premium plans',
  'Dedicated sales and onboarding assistance',
];

$whyImages = [
  'https://images.pexels.com/photos/8403087/pexels-photo-8403087.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'https://images.pexels.com/photos/6585750/pexels-photo-6585750.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'https://images.pexels.com/photos/9011226/pexels-photo-9011226.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'https://images.pexels.com/photos/3615613/pexels-photo-3615613.jpeg?auto=compress&cs=tinysrgb&w=1200',
];
$offerImages = [
  'https://images.pexels.com/photos/6283979/pexels-photo-6283979.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'https://images.pexels.com/photos/8867439/pexels-photo-8867439.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'https://images.pexels.com/photos/3184325/pexels-photo-3184325.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'https://images.pexels.com/photos/3183150/pexels-photo-3183150.jpeg?auto=compress&cs=tinysrgb&w=1200',
  'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1200&q=80',
  'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1200&q=80',
];

$registrationSuccess = (string)($content['pricing.form.success'] ?? 'Thanks. Our sales team will contact you shortly.');
$leadPurchaseSteps = [
    'Tell us your target city, locality, and budget band.',
    'We route the request to the right interior professional segment.',
    'Your purchase request is stored in the backend and handed to sales.',
];
$cities = is_array($payload['city_options'] ?? null) ? $payload['city_options'] : ['Gurgaon', 'Delhi', 'Noida'];
?>

<section class="section pricing-plans-first" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2><?= htmlspecialchars((string)($content['pricing.plans.title'] ?? 'Two ways to grow'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p><?= htmlspecialchars((string)($content['pricing.plans.subtitle'] ?? 'Start with lead buying or hand over the full growth engine to our team.'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="pricing-grid">
      <?php foreach ($plans as $index => $plan): ?>
        <article class="pricing-card <?= $index === 1 ? 'featured' : '' ?>">
          <?php if ($index === 1): ?><span class="pricing-badge">Most Comprehensive</span><?php endif; ?>
          <div class="pricing-card-head">
            <h3><?= htmlspecialchars($plan['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <strong><?= htmlspecialchars($plan['price'], ENT_QUOTES, 'UTF-8') ?></strong>
          </div>
          <p><?= htmlspecialchars($plan['description'], ENT_QUOTES, 'UTF-8') ?></p>
          <ul class="benefit-list">
            <?php foreach ($plan['items'] as $item): ?>
              <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
          <?php if ($plan['id'] === 'lead_purchase'): ?>
            <a class="btn-primary" href="/lead-marketplace">Buy filtered leads</a>
          <?php endif; ?>
          <button class="btn-link pricing-select" type="button" data-plan="<?= htmlspecialchars($plan['title'], ENT_QUOTES, 'UTF-8') ?>">Register interest</button>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="pricing-hero section" data-reveal>
  <div class="container pricing-shell">
    <div class="pricing-copy-stack">
      <div class="pricing-copy">
        <p class="eyebrow"><?= htmlspecialchars((string)($content['pricing.hero.eyebrow'] ?? 'CHOOSE YOUR GROWTH MODEL'), ENT_QUOTES, 'UTF-8') ?></p>
        <h1><?= htmlspecialchars((string)($content['pricing.hero.title'] ?? 'A pricing page built for architects and interior designers.'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="hero-subtitle"><?= htmlspecialchars((string)($content['pricing.hero.subtitle'] ?? 'Pick the lead purchase mode or let our team manage your entire account end-to-end.'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>

      <div class="pricing-intro-panel">
        <div class="pricing-hero-points">
          <span class="chip">Lead purchase by budget band</span>
          <span class="chip">Managed account service</span>
          <span class="chip">Sales team registration</span>
        </div>

        <div class="pricing-stats">
          <article class="stat-mini">
            <strong>01</strong>
            <span>Lead capture with backend storage</span>
          </article>
          <article class="stat-mini">
            <strong>02</strong>
            <span>Filtered by city, locality, and budget</span>
          </article>
          <article class="stat-mini">
            <strong>03</strong>
            <span>Managed growth for full account ownership</span>
          </article>
        </div>

        <div class="pricing-story">
          <h3>How lead purchase works</h3>
          <ul class="benefit-list">
            <?php foreach ($leadPurchaseSteps as $step): ?>
              <li><?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
          <p class="pricing-note">Every purchase request submitted here is saved in the backend for follow-up by our sales team.</p>
        </div>
      </div>
    </div>

    <aside class="lead-card pricing-form-shell">
      <h2><?= htmlspecialchars((string)($content['pricing.form.title'] ?? 'Register interest'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="muted-line"><?= htmlspecialchars((string)($content['pricing.form.subtitle'] ?? 'Our sales team will connect with you and tailor the right plan.'), ENT_QUOTES, 'UTF-8') ?></p>
      <form id="pricingLeadForm" class="stack-form">
        <input type="hidden" name="source" value="pricing" />
        <label>
          <span><?= htmlspecialchars((string)($content['ui.name'] ?? 'Name'), ENT_QUOTES, 'UTF-8') ?></span>
          <input name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? 'Name'), ENT_QUOTES, 'UTF-8') ?>" />
        </label>
        <label>
          <span><?= htmlspecialchars((string)($content['ui.phone'] ?? 'Phone'), ENT_QUOTES, 'UTF-8') ?></span>
          <input name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? 'Phone'), ENT_QUOTES, 'UTF-8') ?>" />
        </label>
        <label>
          <span><?= htmlspecialchars((string)($content['ui.city'] ?? 'City'), ENT_QUOTES, 'UTF-8') ?></span>
          <select name="city" required>
            <option value=""><?= htmlspecialchars((string)($content['ui.city'] ?? 'City'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php foreach ($cities as $city): ?>
              <option value="<?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <?php require __DIR__ . '/../partials/society-field.php'; ?>
        <label>
          <span>Budget</span>
          <select name="budget">
            <option value="">Select budget</option>
            <?php require __DIR__ . '/../partials/budget-options.php'; ?>
          </select>
        </label>
        <label>
          <span>Lead Purchase Focus</span>
          <select name="lead_focus">
            <option value="">Select your focus</option>
            <option value="city">City</option>
            <option value="locality">Locality</option>
            <option value="budget">Budget</option>
            <option value="combined">Combined targeting</option>
          </select>
        </label>
        <label>
          <span>Plan Type</span>
          <select name="plan_type" required>
            <option value="">Select plan</option>
            <option value="Lead Purchase Mode">Lead Purchase Mode</option>
            <option value="Managed Growth Account">Managed Growth Account</option>
          </select>
        </label>
        <label>
          <span>Requirement</span>
          <textarea name="requirement" required placeholder="Tell us what you want to buy or manage"></textarea>
        </label>
        <?php require __DIR__ . '/../partials/lead-consent.php'; ?>
        <button type="submit" class="btn-primary"><?= htmlspecialchars((string)($content['pricing.form.submit'] ?? 'Connect with Sales'), ENT_QUOTES, 'UTF-8') ?></button>
        <p class="form-message" id="pricingLeadMessage"></p>
      </form>
    </aside>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container twin-grid">
    <div>
      <h2><?= htmlspecialchars((string)($content['pricing.why.title'] ?? 'Why buy from us'), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="media-collection-grid">
        <?php foreach ($whyBuyPoints as $index => $point): ?>
          <article class="media-block">
            <img src="<?= htmlspecialchars($whyImages[$index % count($whyImages)], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?>" />
            <div class="media-block-copy">
              <h3><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></h3>
              <p>Built for architects and interior designers who want sharper demand, cleaner lead flow, and more predictable growth.</p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <h2><?= htmlspecialchars((string)($content['pricing.offerings.title'] ?? 'What we offer'), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="media-collection-grid">
        <?php foreach ($offerings as $index => $point): ?>
          <article class="media-block">
            <img src="<?= htmlspecialchars($offerImages[$index % count($offerImages)], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?>" />
            <div class="media-block-copy">
              <h3><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></h3>
              <p>Modern support blocks that keep your account, visibility, and lead generation moving in one direction.</p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <div class="section-head">
      <h2><?= htmlspecialchars((string)($content['pricing.reviews.title'] ?? 'Best architect reviews'), ENT_QUOTES, 'UTF-8') ?></h2>
      <p>Feedback from verified professionals already building with us.</p>
    </div>
    <div class="cards-grid pricing-reviews">
      <?php if ($reviews === []): ?>
        <article class="review-card pricing-review-card">
          <p>No reviews available yet. New professional testimonials will appear here as the network grows.</p>
        </article>
      <?php else: ?>
        <?php foreach ($reviews as $review): ?>
          <article class="review-card pricing-review-card">
            <div class="review-topline">
              <?php if (!empty($review['pro_profile_pic'])): ?>
                <img class="quote-avatar" src="<?= htmlspecialchars((string)$review['pro_profile_pic'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($review['pro_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
              <?php endif; ?>
              <div>
                <h4><?= htmlspecialchars((string)($review['pro_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h4>
                <span><?= htmlspecialchars((string)($review['pro_role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($review['pro_city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>
            <p>★ <?= (int)($review['rating'] ?? 0) ?>/5</p>
            <p>“<?= htmlspecialchars((string)($review['review_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>”</p>
            <p><strong><?= htmlspecialchars((string)($review['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if ((int)($review['verified_purchase'] ?? 0) === 1): ?>
              <span class="verify-badge">Verified Purchase</span>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
(() => {
  const form = document.getElementById('pricingLeadForm');
  const message = document.getElementById('pricingLeadMessage');
  const buttons = document.querySelectorAll('.pricing-select');

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const plan = form.querySelector('[name="plan_type"]');
      if (plan) {
        plan.value = button.dataset.plan || '';
        plan.focus();
      }
      window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
    });
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const payload = Object.fromEntries(new FormData(form).entries());
    const leadFocus = String(payload.lead_focus || '').trim();
    if (leadFocus) {
      const requirementBase = String(payload.requirement || '').trim();
      payload.requirement = requirementBase ? `${requirementBase} | Lead purchase focus: ${leadFocus}` : `Lead purchase focus: ${leadFocus}`;
    }
    delete payload.lead_focus;
    payload.source = 'pricing';

    const response = await fetch('/api/leads', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await response.json();

    if (response.ok) {
      message.className = 'form-message ok';
      message.textContent = <?= json_encode($registrationSuccess, JSON_UNESCAPED_UNICODE) ?>;
      form.reset();
      return;
    }

    message.className = 'form-message error';
    message.textContent = data.error || 'Failed';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
