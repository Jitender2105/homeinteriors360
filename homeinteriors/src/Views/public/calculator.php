<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container calculator-shell" data-reveal>
    <h1><?= htmlspecialchars((string)($content['calculator.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string)($content['calculator.subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

    <form id="calculatorForm" class="calculator-card">
      <div class="step active" data-step="1">
        <h3><?= htmlspecialchars((string)($content['calculator.step1'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="select-grid">
          <label><input type="radio" name="floor_plan" value="1BHK" required> 1BHK</label>
          <label><input type="radio" name="floor_plan" value="2BHK" required> 2BHK</label>
          <label><input type="radio" name="floor_plan" value="3BHK" required> 3BHK</label>
          <label><input type="radio" name="floor_plan" value="4BHK" required> 4BHK</label>
        </div>
      </div>

      <div class="step" data-step="2">
        <h3><?= htmlspecialchars((string)($content['calculator.step2'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="select-grid">
          <label><input type="radio" name="package_tier" value="Essential" required> Essential</label>
          <label><input type="radio" name="package_tier" value="Premium" required> Premium</label>
          <label><input type="radio" name="package_tier" value="Luxury" required> Luxury</label>
        </div>
      </div>

      <div class="step" data-step="3">
        <h3><?= htmlspecialchars((string)($content['calculator.step3'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="select-grid">
          <label><input type="checkbox" name="rooms" value="Living Room"> Living Room</label>
          <label><input type="checkbox" name="rooms" value="Kitchen"> Kitchen</label>
          <label><input type="checkbox" name="rooms" value="Master Bedroom"> Master Bedroom</label>
          <label><input type="checkbox" name="rooms" value="Bedroom 2"> Bedroom 2</label>
          <label><input type="checkbox" name="rooms" value="Bedroom 3"> Bedroom 3</label>
          <label><input type="checkbox" name="rooms" value="Bathroom"> Bathroom</label>
          <label><input type="checkbox" name="rooms" value="Pooja Unit"> Pooja Unit</label>
        </div>
      </div>

      <div class="step" data-step="4">
        <h3><?= htmlspecialchars((string)($content['calculator.step4'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="stack-form">
          <input name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <input name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <input name="city" required placeholder="<?= htmlspecialchars((string)($content['ui.city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <input name="requirement" placeholder="<?= htmlspecialchars((string)($content['ui.requirement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" value="Design Cost Calculator" />
        </div>
      </div>

      <div class="step-actions">
        <button class="btn-muted" type="button" id="calcPrev">Back</button>
        <button class="btn-primary" type="button" id="calcNext">Next</button>
        <button class="btn-primary" type="submit" id="calcSubmit"><?= htmlspecialchars((string)($content['calculator.submit'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
      </div>
      <p class="estimate" id="calcEstimate"></p>
      <p class="form-message" id="calcMsg"></p>
    </form>
  </div>
</section>

<script>
(() => {
  const form = document.getElementById('calculatorForm');
  if (!form) return;

  const steps = [...form.querySelectorAll('.step')];
  const nextBtn = document.getElementById('calcNext');
  const prevBtn = document.getElementById('calcPrev');
  const submitBtn = document.getElementById('calcSubmit');
  const estimateEl = document.getElementById('calcEstimate');
  const msg = document.getElementById('calcMsg');
  let current = 0;

  function getRooms() {
    return [...form.querySelectorAll('input[name="rooms"]:checked')].map((el) => el.value);
  }

  function validStep() {
    if (current === 0 && !form.querySelector('input[name="floor_plan"]:checked')) return false;
    if (current === 1 && !form.querySelector('input[name="package_tier"]:checked')) return false;
    if (current === 2 && getRooms().length === 0) return false;

    if (current === 3) {
      return ['name', 'phone', 'city'].every((field) => form.elements[field].reportValidity());
    }
    return true;
  }

  function sync() {
    steps.forEach((step, index) => step.classList.toggle('active', index === current));
    prevBtn.style.display = current === 0 ? 'none' : 'inline-flex';
    nextBtn.style.display = current === steps.length - 1 ? 'none' : 'inline-flex';
    submitBtn.style.display = current === steps.length - 1 ? 'inline-flex' : 'none';
  }

  nextBtn.addEventListener('click', () => {
    if (!validStep()) return;
    current = Math.min(current + 1, steps.length - 1);
    sync();
  });

  prevBtn.addEventListener('click', () => {
    current = Math.max(current - 1, 0);
    sync();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validStep()) return;

    const fd = new FormData(form);
    const payload = {
      floor_plan: fd.get('floor_plan'),
      package_tier: fd.get('package_tier'),
      rooms: getRooms(),
      name: fd.get('name'),
      phone: fd.get('phone'),
      city: fd.get('city'),
      requirement: fd.get('requirement'),
    };

    const response = await fetch('/api/calculator/estimate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    const data = await response.json();
    if (response.ok) {
      estimateEl.textContent = `${<?= json_encode((string)($content['calculator.result_prefix'] ?? ''), JSON_UNESCAPED_UNICODE) ?>} ₹${Number(data.estimate || 0).toLocaleString('en-IN')}`;
      msg.className = 'form-message ok';
      msg.textContent = <?= json_encode((string)($content['home.lead.success'] ?? 'Submitted'), JSON_UNESCAPED_UNICODE) ?>;
      form.reset();
      current = 0;
      sync();
      return;
    }

    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed';
  });

  sync();
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
