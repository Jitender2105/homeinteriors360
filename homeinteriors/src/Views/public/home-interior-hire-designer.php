<?php
require __DIR__ . '/../partials/header.php';

$payload = is_array($payload ?? null) ? $payload : [];
$filterOptions = is_array($filterOptions ?? null) ? $filterOptions : [];
$cities = $filterOptions['cities'] ?? ($payload['city_options'] ?? []);
$workTypes = $filterOptions['work_types'] ?? ($payload['requirement_options'] ?? []);
if (!$cities) {
  $cities = ['Gurugram', 'Delhi NCR', 'Noida', 'Faridabad', 'Ghaziabad'];
}
if (!$workTypes) {
  $workTypes = ['Full Home', 'Modular Kitchen', 'Wardrobe', 'Renovation', 'Living Room'];
}

$heroImage = 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1500&q=85';
$roomCards = [
  ['title' => '2BHK interiors', 'price' => 'Project-fit matches', 'image' => 'https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=900&q=82'],
  ['title' => '3BHK interiors', 'price' => 'Verified designers', 'image' => 'https://images.pexels.com/photos/7031874/pexels-photo-7031874.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Modular kitchens', 'price' => 'Kitchen specialists', 'image' => 'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=900&q=82'],
  ['title' => 'Bedroom design', 'price' => 'Storage-led planning', 'image' => 'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=900&q=82'],
  ['title' => 'Living room', 'price' => 'Portfolio comparison', 'image' => 'https://images.pexels.com/photos/9976128/pexels-photo-9976128.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Renovation', 'price' => 'Scope-aware teams', 'image' => 'https://images.pexels.com/photos/15124969/pexels-photo-15124969.jpeg?auto=compress&cs=tinysrgb&w=900'],
];
$edgeRows = [
  ['label' => 'Choice', 'us' => 'Compare multiple verified designers and aggregator partners', 'market' => 'Talk to scattered vendors one by one'],
  ['label' => 'Budget', 'us' => 'Share your range first, then shortlist suitable teams', 'market' => 'Quotes often arrive without context'],
  ['label' => 'Design', 'us' => 'Review portfolios, work type, city, rating, and experience', 'market' => 'Depend on references and screenshots'],
  ['label' => 'Convenience', 'us' => 'One requirement form routes your enquiry to relevant professionals', 'market' => 'Repeat the same brief across calls'],
  ['label' => 'Transparency', 'us' => 'Aggregator model: we help you discover, compare, and connect', 'market' => 'Hard to know who is the right fit'],
];
$services = [
  ['title' => 'Civil work', 'image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'False ceiling', 'image' => 'https://images.pexels.com/photos/1928739/pexels-photo-1928739.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Electrical', 'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Plumbing', 'image' => 'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Flooring and wall tiling', 'image' => 'https://images.pexels.com/photos/10481158/pexels-photo-10481158.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Painting', 'image' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Modular furniture', 'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Wardrobes', 'image' => 'https://images.pexels.com/photos/36221937/pexels-photo-36221937.jpeg?auto=compress&cs=tinysrgb&w=900'],
];
$steps = [
  ['title' => 'Share requirement', 'text' => 'Tell us your city, home type, area, budget, and design scope.'],
  ['title' => 'Get matched', 'text' => 'We shortlist relevant designers, contractors, studios, or aggregator partners.'],
  ['title' => 'Compare options', 'text' => 'Review fit by portfolio, service area, work type, and project strength.'],
  ['title' => 'Start discussion', 'text' => 'Speak with the right teams and move forward with the quote that works for you.'],
];
$ideas = [
  ['title' => 'Modular Kitchen', 'image' => 'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Master Bedroom', 'image' => 'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Living Room', 'image' => 'https://images.pexels.com/photos/9976128/pexels-photo-9976128.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Wardrobe', 'image' => 'https://images.pexels.com/photos/36221937/pexels-photo-36221937.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Bathroom', 'image' => 'https://images.unsplash.com/photo-1620626011761-996317b8d101?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Pooja Room', 'image' => 'https://images.pexels.com/photos/32666419/pexels-photo-32666419.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Balcony', 'image' => 'https://images.pexels.com/photos/15667608/pexels-photo-15667608.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Dining Room', 'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Kids Room', 'image' => 'https://images.unsplash.com/photo-1617103996702-96ff29b1c467?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Workspace', 'image' => 'https://images.unsplash.com/photo-1593476550610-87baa860004a?auto=format&fit=crop&w=800&q=82'],
  ['title' => 'Foyer', 'image' => 'https://images.pexels.com/photos/8135493/pexels-photo-8135493.jpeg?auto=compress&cs=tinysrgb&w=900'],
  ['title' => 'Full Home', 'image' => 'https://images.pexels.com/photos/14613699/pexels-photo-14613699.jpeg?auto=compress&cs=tinysrgb&w=900'],
];
?>

<section class="hire-hero" style="--hire-hero-image:url('<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container hire-hero-grid" data-reveal>
    <div class="hire-hero-copy">
      <p class="eyebrow">Home Interior</p>
      <h1>Hire a designer for your dream home interiors.</h1>
      <p class="hero-subtitle">HomeInteriors360 is an aggregator. Share your requirement once and compare verified interior designers, architects, contractors, and partner studios for your project.</p>
      <div class="hire-hero-actions">
        <a class="btn-primary" href="#hireDesignerForm">Get Free Quote</a>
        <a class="btn-link" href="/professionals">View Professionals</a>
      </div>
    </div>
    <aside class="hire-form-card" id="hireDesignerForm">
      <p class="eyebrow eyebrow-dark">Get Free Quote</p>
      <h2>Tell us about your home</h2>
      <form id="hireLeadForm" class="stack-form hero-lead-form">
        <div class="hero-lead-grid">
          <label><span>Name</span><input name="name" required placeholder="Your name"></label>
          <label><span>Phone</span><input name="phone" type="tel" required placeholder="Mobile number"></label>
          <label>
            <span>City</span>
            <select name="city" required>
              <option value="">Select city</option>
              <?php foreach ($cities as $city): ?>
                <option value="<?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            <span>Requirement</span>
            <select name="requirement" required>
              <option value="">Select requirement</option>
              <?php foreach ($workTypes as $workType): ?>
                <option value="<?= htmlspecialchars((string)$workType, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$workType, ENT_QUOTES, 'UTF-8') ?></option>
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
        </div>
        <?php require __DIR__ . '/../partials/lead-consent.php'; ?>
        <button class="btn-primary hero-lead-submit" type="submit">Get Free Quote</button>
        <p class="form-message" id="hireLeadMessage"></p>
      </form>
    </aside>
  </div>
</section>

<section class="section hire-budget-section" data-reveal>
  <div class="container">
    <div class="section-head section-head-row">
      <div>
        <p class="eyebrow eyebrow-dark">Homes for every budget</p>
        <h2>Find designers by home type and scope.</h2>
      </div>
      <a class="btn-link" href="#hireDesignerForm">GET FREE QUOTE</a>
    </div>
    <div class="hire-room-grid">
      <?php foreach ($roomCards as $card): ?>
        <article class="hire-room-card">
          <img src="<?= htmlspecialchars($card['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
          <div>
            <h3><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($card['price'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-tight hire-edge-section" data-reveal>
  <div class="container">
    <div class="section-head">
      <p class="eyebrow eyebrow-dark">The HomeInteriors360 Edge</p>
      <h2>Your home interiors should start with the right shortlist.</h2>
      <p>We are not a single execution brand. We help you discover and compare suitable professionals so your project starts with more choice and better context.</p>
    </div>
    <div class="hire-edge-table">
      <div class="hire-edge-head"><span></span><strong>HomeInteriors360</strong><strong>Typical search</strong></div>
      <?php foreach ($edgeRows as $row): ?>
        <div class="hire-edge-row">
          <span><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <p><?= htmlspecialchars($row['us'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars($row['market'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="hire-center-cta"><a class="btn-primary" href="#hireDesignerForm">GET FREE CONSULTATION</a></div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <div class="section-head section-head-row">
      <div>
        <p class="eyebrow eyebrow-dark">Services</p>
        <h2>Interior teams for every major home requirement.</h2>
      </div>
      <a class="btn-link" href="#hireDesignerForm">GET FREE QUOTE</a>
    </div>
    <div class="hire-service-grid">
      <?php foreach ($services as $service): ?>
        <article class="hire-service-card">
          <img src="<?= htmlspecialchars($service['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
          <div>
            <span><?= htmlspecialchars(substr($service['title'], 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
            <h3><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></h3>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-tight hire-custom-section" data-reveal>
  <div class="container hire-custom-grid">
    <div>
      <p class="eyebrow eyebrow-dark">Customised services</p>
      <h2>Can't find exactly what you need?</h2>
      <p>Tell us the project scope. We can route your enquiry to designers, contractors, modular specialists, or full-home aggregator partners depending on your location and budget.</p>
      <a class="btn-primary" href="#hireDesignerForm">Get Free Consultation</a>
    </div>
    <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1200&q=85" alt="Custom home interior materials" loading="lazy" decoding="async">
  </div>
</section>

<section class="section hire-process-section" data-reveal>
  <div class="container">
    <div class="section-head">
      <p class="eyebrow eyebrow-dark">How it works</p>
      <h2>Home interiors in 4 easy steps.</h2>
    </div>
    <div class="hire-step-grid">
      <?php foreach ($steps as $index => $step): ?>
        <article class="hire-step-card">
          <strong><?= (int)($index + 1) ?></strong>
          <h3><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars($step['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="hire-center-cta"><a class="btn-primary" href="#hireDesignerForm">GET STARTED NOW</a></div>
  </div>
</section>

<section class="section section-tight" data-reveal>
  <div class="container">
    <div class="section-head section-head-row">
      <div>
        <p class="eyebrow eyebrow-dark">Ideas to inspire</p>
        <h2>Browse designers by the rooms you want to build.</h2>
      </div>
      <a class="btn-link" href="#hireDesignerForm">GET FREE QUOTE</a>
    </div>
    <div class="hire-idea-grid">
      <?php foreach ($ideas as $idea): ?>
        <a href="#hireDesignerForm" style="--idea-image:url('<?= htmlspecialchars($idea['image'], ENT_QUOTES, 'UTF-8') ?>');">
          <span><?= htmlspecialchars($idea['title'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
(() => {
  const form = document.getElementById('hireLeadForm');
  if (!form) return;
  const msg = document.getElementById('hireLeadMessage');
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const payload = Object.fromEntries(new FormData(form).entries());
    payload.source = 'home_interior_hire_designer';
    payload.plan_type = 'home_interior_designer_matching';
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    msg.className = 'form-message';
    msg.textContent = 'Submitting your requirement...';
    const res = await fetch('/api/leads', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    button.disabled = false;
    if (res.ok) {
      msg.className = 'form-message ok';
      msg.textContent = 'Thank you. Our team will call you shortly with suitable designer options.';
      form.reset();
      return;
    }
    msg.className = 'form-message error';
    msg.textContent = data.error || 'Could not submit your requirement.';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
