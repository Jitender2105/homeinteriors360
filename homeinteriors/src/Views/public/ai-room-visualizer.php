<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$styles = is_array($styles ?? null) ? $styles : [];
$roomTypes = ['Living room', 'Bedroom', 'Kitchen', 'Bathroom', 'Full home / open layout'];
$budgetTiers = ['economy' => 'Economy', 'mid-range' => 'Mid-range', 'premium' => 'Premium', 'luxury' => 'Luxury'];
?>

<section class="visualizer-hero">
  <div class="container visualizer-hero-grid" data-reveal>
    <div>
      <p class="eyebrow">AI Room Visualizer</p>
      <h1>See your room as a designer-ready before and after brief.</h1>
      <p>Upload a room photo, pick a style, and HomeInteriors360 creates a structure-preserving redesign prompt and lead brief for verified interior designers.</p>
    </div>
    <div class="visualizer-note">
      <strong>Built for real execution</strong>
      <span>Walls, windows, doors, ceiling height and camera angle are preserved in the generated prompt.</span>
    </div>
  </div>
</section>

<section class="section visualizer-section">
  <div class="container visualizer-grid">
    <form id="visualizerForm" class="visualizer-panel" enctype="multipart/form-data">
      <h2>Create your room render brief</h2>
      <label><span>Room photo</span><input name="room_photo" type="file" accept="image/*" required></label>
      <div class="budget-grid">
        <label><span>Room type</span><select name="room_type" required><option value="">Select room</option><?php foreach ($roomTypes as $room): ?><option value="<?= htmlspecialchars($room, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($room, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label><span>Style</span><select name="style" required><option value="">Select style</option><?php foreach ($styles as $style): ?><option value="<?= htmlspecialchars((string)$style['style_key'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$style['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      </div>
      <label><span>Budget tier</span><select name="budget_tier" required><?php foreach ($budgetTiers as $key => $label): ?><option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $key === 'mid-range' ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label><span>What should the designer keep or improve?</span><textarea name="freeform_notes" rows="3" placeholder="Example: keep the window as is, add more storage, make it brighter"></textarea></label>
      <input type="hidden" name="detected_elements" value="uploaded room photo with visible existing layout, furniture, walls, windows, doors, ceiling, flooring, and camera angle">
      <h3>Save render and get matched</h3>
      <div class="budget-grid">
        <input name="name" required placeholder="Name">
        <input name="phone" required placeholder="Phone">
      </div>
      <div class="budget-grid">
        <input name="email" type="email" placeholder="Email">
        <input name="city" required placeholder="City">
      </div>
      <input name="locality" placeholder="Locality / society">
      <label class="lead-consent"><input type="checkbox" name="consent" value="1" required checked><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
      <button class="btn-primary" type="submit">Generate before-after brief</button>
      <p class="form-message" id="visualizerMessage"></p>
    </form>

    <aside class="visualizer-preview">
      <div class="visualizer-before-after">
        <figure><span>Before</span><img id="visualizerBefore" alt="Uploaded room preview"></figure>
        <figure><span>After</span><div id="visualizerAfter" class="visualizer-after-placeholder">Your AI render will appear here when the image provider is connected.</div></figure>
      </div>
      <article class="visualizer-prompt-card">
        <p class="eyebrow eyebrow-dark">Designer brief</p>
        <h2>Prompt generated for image-to-image render</h2>
        <p id="visualizerPrompt">Upload your room and choose a style to create a designer-ready prompt.</p>
        <small id="visualizerStatus">The lead will include the original photo, style, budget tier, and generated prompt.</small>
      </article>
    </aside>
  </div>
</section>

<section class="section section-tight">
  <div class="container visualizer-style-strip">
    <?php foreach (array_slice($styles, 0, 6) as $style): ?>
      <article>
        <strong><?= htmlspecialchars((string)$style['name'], ENT_QUOTES, 'UTF-8') ?></strong>
        <span><?= htmlspecialchars((string)$style['materials_palette'], ENT_QUOTES, 'UTF-8') ?></span>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<script>
(() => {
  const form = document.getElementById('visualizerForm');
  const before = document.getElementById('visualizerBefore');
  const after = document.getElementById('visualizerAfter');
  const promptBox = document.getElementById('visualizerPrompt');
  const statusBox = document.getElementById('visualizerStatus');
  const message = document.getElementById('visualizerMessage');
  const fileInput = form?.elements.room_photo;
  fileInput?.addEventListener('change', () => {
    const file = fileInput.files?.[0];
    if (!file) return;
    before.src = URL.createObjectURL(file);
    before.style.display = 'block';
  });
  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    message.className = 'form-message';
    message.textContent = 'Creating your visualizer brief...';
    const response = await fetch('/api/ai-room-visualizer/render', {
      method: 'POST',
      body: new FormData(form),
    });
    const data = await response.json();
    if (!response.ok) {
      message.className = 'form-message error';
      message.textContent = data.error || 'Could not create visualizer brief.';
      return;
    }
    const render = data.render || {};
    promptBox.textContent = render.prompt || '';
    statusBox.textContent = render.generation_status === 'generated'
      ? 'Render generated and lead captured.'
      : 'Design brief captured. The AI render will appear here once image generation is available.';
    if (render.rendered_image_url) {
      after.innerHTML = `<img src="${render.rendered_image_url}" alt="AI room render">`;
    }
    message.className = 'form-message ok';
    message.textContent = 'Saved. A designer can now see your original photo, style, budget and render brief.';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
