<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
$floorPlans = [
  ['value' => '1BHK', 'title' => '1 BHK', 'text' => 'Compact home with essential storage and smart space planning.', 'image' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=900&q=80'],
  ['value' => '2BHK', 'title' => '2 BHK', 'text' => 'A balanced home setup for families and rental upgrades.', 'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=900&q=80'],
  ['value' => '3BHK', 'title' => '3 BHK', 'text' => 'Full home interiors with wardrobes, kitchen and living zones.', 'image' => 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=900&q=80'],
  ['value' => '4BHK', 'title' => '4 BHK', 'text' => 'Large homes that need a more detailed execution scope.', 'image' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=900&q=80'],
];
$homeSizes = [
  ['value' => 'Compact', 'title' => 'Compact', 'text' => 'Up to 900 sq ft'],
  ['value' => 'Standard', 'title' => 'Standard', 'text' => '900 - 1,500 sq ft'],
  ['value' => 'Spacious', 'title' => 'Spacious', 'text' => '1,500 - 2,400 sq ft'],
  ['value' => 'Luxury', 'title' => 'Luxury', 'text' => '2,400 sq ft and above'],
];
$rooms = [
  ['value' => 'Living Room', 'title' => 'Living Room', 'text' => 'TV unit, seating wall, storage and decor details'],
  ['value' => 'Kitchen', 'title' => 'Kitchen', 'text' => 'Modular cabinets, countertop planning and accessories'],
  ['value' => 'Master Bedroom', 'title' => 'Master Bedroom', 'text' => 'Wardrobe, bed-back wall, side units and storage'],
  ['value' => 'Bedroom 2', 'title' => 'Bedroom 2', 'text' => 'Wardrobe and study or guest room planning'],
  ['value' => 'Bedroom 3', 'title' => 'Bedroom 3', 'text' => 'Kids, parents or flexible bedroom interiors'],
  ['value' => 'Bathroom', 'title' => 'Bathroom', 'text' => 'Vanity, mirrors and dry storage additions'],
  ['value' => 'Pooja Unit', 'title' => 'Pooja Unit', 'text' => 'Compact or statement mandir unit'],
];
$packages = [
  ['value' => 'Essential', 'title' => 'Essential', 'price' => 'Value focused', 'text' => 'Functional finishes, core storage and practical accessories.'],
  ['value' => 'Premium', 'title' => 'Premium', 'price' => 'Most selected', 'text' => 'Better finishes, richer hardware and balanced styling.'],
  ['value' => 'Luxury', 'title' => 'Luxury', 'price' => 'Statement homes', 'text' => 'Premium materials, custom detailing and elevated execution.'],
];
$cities = is_array($payload['city_options'] ?? null) ? $payload['city_options'] : ['Gurgaon', 'Delhi', 'Noida'];
?>

<section class="calculator-hero">
  <div class="container calculator-hero-inner" data-reveal>
    <p class="eyebrow">Home Interior Price Calculator</p>
    <h1><?= htmlspecialchars((string)($content['calculator.title'] ?? 'Curious about your dream interior price?'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string)($content['calculator.subtitle'] ?? 'Get a starting estimate for your full home interiors in a few guided steps.'), ENT_QUOTES, 'UTF-8') ?></p>
  </div>
</section>

<section class="section calculator-experience" data-reveal>
  <div class="container calculator-layout">
    <form id="calculatorForm" class="calculator-panel">
      <div class="calculator-progress">
        <span id="calcStepLabel">Step 1 of 5</span>
        <div><i id="calcProgressBar"></i></div>
      </div>

      <div class="calc-step active" data-step="1" data-title="Choose your BHK type">
        <p class="eyebrow eyebrow-dark">Configuration</p>
        <h2>Choose your BHK type</h2>
        <p>The type of home helps us understand your interior scope.</p>
        <div class="calc-option-grid calc-image-grid">
          <?php foreach ($floorPlans as $plan): ?>
            <label class="calc-option-card">
              <input type="radio" name="floor_plan" value="<?= htmlspecialchars($plan['value'], ENT_QUOTES, 'UTF-8') ?>" required>
              <img src="<?= htmlspecialchars($plan['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($plan['title'], ENT_QUOTES, 'UTF-8') ?>">
              <span><?= htmlspecialchars($plan['title'], ENT_QUOTES, 'UTF-8') ?></span>
              <small><?= htmlspecialchars($plan['text'], ENT_QUOTES, 'UTF-8') ?></small>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="calc-step" data-step="2" data-title="Select the size of your house">
        <p class="eyebrow eyebrow-dark">Home Size</p>
        <h2>Select the size of your house</h2>
        <p>This gives your estimate better context for space planning and scope.</p>
        <div class="calc-option-grid">
          <?php foreach ($homeSizes as $size): ?>
            <label class="calc-option-card calc-text-card">
              <input type="radio" name="home_size" value="<?= htmlspecialchars($size['value'], ENT_QUOTES, 'UTF-8') ?>" required>
              <span><?= htmlspecialchars($size['title'], ENT_QUOTES, 'UTF-8') ?></span>
              <small><?= htmlspecialchars($size['text'], ENT_QUOTES, 'UTF-8') ?></small>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="calc-step" data-step="3" data-title="Pick the rooms to be designed">
        <p class="eyebrow eyebrow-dark">Scope</p>
        <h2>Pick the rooms to be designed</h2>
        <p>Select at least one room. You can update this anytime before submitting.</p>
        <div class="calc-room-grid">
          <?php foreach ($rooms as $room): ?>
            <label class="calc-room-card">
              <input type="checkbox" name="rooms" value="<?= htmlspecialchars($room['value'], ENT_QUOTES, 'UTF-8') ?>">
              <span><?= htmlspecialchars($room['title'], ENT_QUOTES, 'UTF-8') ?></span>
              <small><?= htmlspecialchars($room['text'], ENT_QUOTES, 'UTF-8') ?></small>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="calc-step" data-step="4" data-title="Pick a package">
        <p class="eyebrow eyebrow-dark">Finish Level</p>
        <h2>Pick a package as per your preference</h2>
        <p>This fine tunes the calculation based on materials, accessories and finish level.</p>
        <div class="calc-package-grid">
          <?php foreach ($packages as $package): ?>
            <label class="calc-package-card">
              <input type="radio" name="package_tier" value="<?= htmlspecialchars($package['value'], ENT_QUOTES, 'UTF-8') ?>" required>
              <strong><?= htmlspecialchars($package['title'], ENT_QUOTES, 'UTF-8') ?></strong>
              <span><?= htmlspecialchars($package['price'], ENT_QUOTES, 'UTF-8') ?></span>
              <small><?= htmlspecialchars($package['text'], ENT_QUOTES, 'UTF-8') ?></small>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="calc-step" data-step="5" data-title="Get your estimate">
        <p class="eyebrow eyebrow-dark">Contact Details</p>
        <h2>Get your free estimate</h2>
        <p>Share your details and we will save this estimate as a calculator lead.</p>
        <div class="calc-contact-grid">
          <input name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? 'Name'), ENT_QUOTES, 'UTF-8') ?>" />
          <input name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? 'Phone'), ENT_QUOTES, 'UTF-8') ?>" />
          <select name="city" required>
            <option value=""><?= htmlspecialchars((string)($content['ui.city'] ?? 'City'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php foreach ($cities as $city): ?>
              <option value="<?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$city, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
          <?php require __DIR__ . '/../partials/society-field.php'; ?>
          <select name="budget">
            <option value=""><?= htmlspecialchars((string)($content['ui.budget'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?></option>
            <?php require __DIR__ . '/../partials/budget-options.php'; ?>
          </select>
          <input name="requirement" placeholder="<?= htmlspecialchars((string)($content['ui.requirement'] ?? 'Requirement'), ENT_QUOTES, 'UTF-8') ?>" value="Design Cost Calculator" />
        </div>
        <?php require __DIR__ . '/../partials/lead-consent.php'; ?>
      </div>

      <div class="calculator-actions">
        <button class="btn-muted" type="button" id="calcPrev">Back</button>
        <button class="btn-primary" type="button" id="calcNext">Next</button>
        <button class="btn-primary" type="submit" id="calcSubmit"><?= htmlspecialchars((string)($content['calculator.submit'] ?? 'Get Free Estimate'), ENT_QUOTES, 'UTF-8') ?></button>
      </div>
      <p class="form-message" id="calcMsg"></p>
    </form>

    <aside class="calculator-summary">
      <p class="eyebrow eyebrow-dark">Live Summary</p>
      <h2>Your estimate</h2>
      <div class="calc-estimate-box calc-estimate-locked" id="calcEstimateBox">
        <span id="calcEstimateLabel">Estimate locked</span>
        <strong id="calcEstimate">Fill lead form</strong>
        <small id="calcEstimateHint">Complete the steps and submit your contact details to view the price.</small>
      </div>
      <dl>
        <div><dt>BHK Type</dt><dd id="summaryPlan">Not selected</dd></div>
        <div><dt>Home Size</dt><dd id="summarySize">Not selected</dd></div>
        <div><dt>Rooms</dt><dd id="summaryRooms">Not selected</dd></div>
        <div><dt>Package</dt><dd id="summaryPackage">Not selected</dd></div>
      </dl>
      <div class="calc-trust-strip">
        <span>4 guided steps</span>
        <span>Backend estimate</span>
        <span>Lead saved securely</span>
      </div>
    </aside>
  </div>
</section>

<script>
(() => {
  const form = document.getElementById('calculatorForm');
  if (!form) return;

  const steps = [...form.querySelectorAll('.calc-step')];
  const nextBtn = document.getElementById('calcNext');
  const prevBtn = document.getElementById('calcPrev');
  const submitBtn = document.getElementById('calcSubmit');
  const progressBar = document.getElementById('calcProgressBar');
  const stepLabel = document.getElementById('calcStepLabel');
  const estimateBox = document.getElementById('calcEstimateBox');
  const estimateEl = document.getElementById('calcEstimate');
  const estimateLabel = document.getElementById('calcEstimateLabel');
  const estimateHint = document.getElementById('calcEstimateHint');
  const msg = document.getElementById('calcMsg');
  const summaryPlan = document.getElementById('summaryPlan');
  const summarySize = document.getElementById('summarySize');
  const summaryRooms = document.getElementById('summaryRooms');
  const summaryPackage = document.getElementById('summaryPackage');
  let current = 0;
  let latestEstimate = null;

  function getChecked(name) {
    return form.querySelector(`input[name="${name}"]:checked`);
  }

  function getRooms() {
    return [...form.querySelectorAll('input[name="rooms"]:checked')].map((el) => el.value);
  }

  function formatCurrency(value) {
    return `₹${Number(value || 0).toLocaleString('en-IN')}`;
  }

  function setMessage(text, type = '') {
    msg.className = `form-message ${type}`.trim();
    msg.textContent = text;
  }

  function validStep() {
    setMessage('');
    if (current === 0 && !getChecked('floor_plan')) {
      setMessage('Please choose your BHK type.', 'error');
      return false;
    }
    if (current === 1 && !getChecked('home_size')) {
      setMessage('Please select your home size.', 'error');
      return false;
    }
    if (current === 2 && getRooms().length === 0) {
      setMessage('Please select at least one room.', 'error');
      return false;
    }
    if (current === 3 && !getChecked('package_tier')) {
      setMessage('Please pick a package.', 'error');
      return false;
    }
    if (current === 4) {
      return ['name', 'phone', 'city', 'lead_consent'].every((field) => form.elements[field].reportValidity());
    }
    return true;
  }

  function updateSummary() {
    const plan = getChecked('floor_plan')?.value || 'Not selected';
    const size = getChecked('home_size')?.value || 'Not selected';
    const tier = getChecked('package_tier')?.value || 'Not selected';
    const rooms = getRooms();
    summaryPlan.textContent = plan;
    summarySize.textContent = size;
    summaryRooms.textContent = rooms.length ? rooms.join(', ') : 'Not selected';
    summaryPackage.textContent = tier;

    if (latestEstimate) {
      estimateBox.classList.remove('calc-estimate-locked');
      estimateLabel.textContent = <?= json_encode((string)($content['calculator.result_prefix'] ?? 'Estimated starting from'), JSON_UNESCAPED_UNICODE) ?>;
      estimateEl.textContent = formatCurrency(latestEstimate);
      estimateHint.textContent = 'This estimate has been generated after saving your calculator lead.';
      return;
    }

    estimateBox.classList.add('calc-estimate-locked');
    estimateLabel.textContent = 'Estimate locked';
    estimateEl.textContent = 'Fill lead form';
    estimateHint.textContent = 'Complete the steps and submit your contact details to view the price.';
  }

  function sync() {
    steps.forEach((step, index) => step.classList.toggle('active', index === current));
    stepLabel.textContent = `Step ${current + 1} of ${steps.length}`;
    progressBar.style.width = `${((current + 1) / steps.length) * 100}%`;
    prevBtn.style.display = current === 0 ? 'none' : 'inline-flex';
    nextBtn.style.display = current === steps.length - 1 ? 'none' : 'inline-flex';
    submitBtn.style.display = current === steps.length - 1 ? 'inline-flex' : 'none';
    updateSummary();
  }

  form.addEventListener('change', () => {
    latestEstimate = null;
    updateSummary();
  });

  nextBtn.addEventListener('click', () => {
    if (!validStep()) return;
    current = Math.min(current + 1, steps.length - 1);
    sync();
  });

  prevBtn.addEventListener('click', () => {
    current = Math.max(current - 1, 0);
    setMessage('');
    sync();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validStep()) return;

    const fd = new FormData(form);
    const homeSize = String(fd.get('home_size') || '').trim();
    const requirementBase = String(fd.get('requirement') || 'Design Cost Calculator').trim();
    const payload = {
      floor_plan: fd.get('floor_plan'),
      package_tier: fd.get('package_tier'),
      rooms: getRooms(),
      name: fd.get('name'),
      phone: fd.get('phone'),
      city: fd.get('city'),
      society_area: fd.get('society_area'),
      budget: fd.get('budget'),
      requirement: homeSize ? `${requirementBase} | Home size: ${homeSize}` : requirementBase,
      lead_consent: fd.get('lead_consent'),
    };

    submitBtn.disabled = true;
    submitBtn.textContent = 'Calculating...';

    try {
      const response = await fetch('/api/calculator/estimate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const data = await response.json();
      if (response.ok) {
        latestEstimate = Number(data.estimate || 0);
        updateSummary();
        setMessage(<?= json_encode((string)($content['home.lead.success'] ?? 'Submitted. Our team will contact you shortly.'), JSON_UNESCAPED_UNICODE) ?>, 'ok');
        return;
      }

      setMessage(data.error || 'Failed to calculate estimate.', 'error');
    } catch (error) {
      setMessage('Something went wrong. Please try again.', 'error');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = <?= json_encode((string)($content['calculator.submit'] ?? 'Get Free Estimate'), JSON_UNESCAPED_UNICODE) ?>;
    }
  });

  sync();
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
