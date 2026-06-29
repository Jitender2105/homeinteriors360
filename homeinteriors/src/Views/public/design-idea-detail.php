<?php
$globalAlias = is_array($globalAlias ?? null) ? $globalAlias : null;
$detailTitle = (string)($globalAlias['h1'] ?? $idea['name']);
$detailImage = (string)($globalAlias['image_url'] ?? $idea['image_url']);
$detailContent = trim((string)($globalAlias['content_html'] ?? ''));
require __DIR__ . '/../partials/header.php';
?>
<main class="design-detail-page">
  <section class="property-detail-head">
    <div class="container">
      <nav class="property-breadcrumb"><a href="/">Home</a><span>/</span><a href="/design-ideas">Design Ideas</a><span>/</span><span><?= htmlspecialchars($detailTitle, ENT_QUOTES, 'UTF-8') ?></span></nav>
      <div class="property-title-row">
        <div><p class="eyebrow"><?= htmlspecialchars((string)$idea['type'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$idea['style'], ENT_QUOTES, 'UTF-8') ?></p><h1><?= htmlspecialchars($detailTitle, ENT_QUOTES, 'UTF-8') ?></h1><p><?= htmlspecialchars(trim((string)$idea['location'] . ', ' . (string)$idea['city'] . ', ' . (string)$idea['state'], ', '), ENT_QUOTES, 'UTF-8') ?></p></div>
        <div class="property-card-actions"><button type="button" class="btn-primary quote-trigger" data-idea-id="<?= (int)$idea['id'] ?>" data-title="<?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>" data-requirement="Quote for design idea: <?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>">Get quote</button><button type="button" class="btn-link quote-trigger" data-idea-id="<?= (int)$idea['id'] ?>" data-title="Free design consultation" data-requirement="Free design consultation for <?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>">Free design consultation</button><button type="button" class="design-fav-btn design-detail-fav" data-idea-id="<?= (int)$idea['id'] ?>" data-title="<?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>" data-requirement="Favourite design idea: <?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>">Save idea</button></div>
      </div>
    </div>
  </section>
  <section class="container property-gallery design-detail-gallery">
    <div class="property-gallery-main"><img src="<?= htmlspecialchars($detailImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($detailTitle, ENT_QUOTES, 'UTF-8') ?>"></div>
    <div class="property-gallery-side"><?php foreach (array_slice($idea['gallery_json'] ?: [$idea['image_url']], 0, 4) as $image): ?><img src="<?= htmlspecialchars((string)$image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>"><?php endforeach; ?></div>
  </section>
  <section class="section"><div class="container property-detail-layout">
    <div class="property-detail-content">
      <section class="property-info-section"><h2>Design overview</h2><div class="property-overview-grid"><div><span>Type</span><strong><?= htmlspecialchars((string)$idea['type'], ENT_QUOTES, 'UTF-8') ?></strong></div><div><span>Colour</span><strong><?= htmlspecialchars((string)$idea['color'], ENT_QUOTES, 'UTF-8') ?></strong></div><div><span>Style</span><strong><?= htmlspecialchars((string)$idea['style'], ENT_QUOTES, 'UTF-8') ?></strong></div><div><span>Layout</span><strong><?= htmlspecialchars((string)$idea['layout'], ENT_QUOTES, 'UTF-8') ?></strong></div><div><span>Dimensions</span><strong><?= (float)$idea['length_ft'] ?> x <?= (float)$idea['breadth_ft'] ?> x <?= (float)$idea['height_ft'] ?> ft</strong></div><div><span>Budget</span><strong>₹<?= number_format((float)$idea['budget_min'], 0) ?> - ₹<?= number_format((float)$idea['budget_max'], 0) ?></strong></div></div><?php if ($detailContent !== ''): ?><div class="alias-rich-content"><?= $detailContent ?></div><?php else: ?><p><?= nl2br(htmlspecialchars((string)$idea['description'], ENT_QUOTES, 'UTF-8')) ?></p><?php endif; ?></section>
      <?php if (!empty($idea['tags_json'])): ?><section class="property-info-section"><h2>Tags</h2><div class="property-amenity-grid"><?php foreach ($idea['tags_json'] as $tag): ?><span><?= htmlspecialchars((string)$tag, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div></section><?php endif; ?>
    </div>
    <aside class="property-enquiry-card"><p class="eyebrow">Book free consultation</p><h2>Get this look priced</h2><p>Share details and we will help plan this idea for your space.</p><form id="designQuoteForm"><input type="hidden" name="design_idea_id" value="<?= (int)$idea['id'] ?>"><input type="hidden" name="requirement" value="<?= htmlspecialchars((string)$idea['name'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="source" value="design_idea_detail"><label><span>Name</span><input name="name" required></label><label><span>Phone</span><input name="phone" type="tel" required></label><label><span>Email</span><input name="email" type="email"></label><label><span>City</span><input name="city" value="<?= htmlspecialchars((string)$idea['city'], ENT_QUOTES, 'UTF-8') ?>"></label><label><span>Budget</span><select name="budget"><option value="">Select budget</option><?php require __DIR__ . '/../partials/budget-options.php'; ?></select></label><label><span>Message</span><textarea name="message" rows="3"></textarea></label><label class="property-consent"><input type="checkbox" name="consent" value="1" required><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label><button class="btn-primary" type="submit">Get quote</button><p class="form-message" id="designQuoteMessage"></p></form></aside>
  </div></section>
</main>
<script>
(() => {
const favKey = 'hi360-design-favourites';
const readFavs = () => JSON.parse(localStorage.getItem(favKey) || '[]');
const writeFavs = (ids) => localStorage.setItem(favKey, JSON.stringify([...new Set(ids.map(String))]));
const renderFav = () => {
  const ids = readFavs();
  document.querySelectorAll('.design-fav-btn').forEach((btn) => {
    btn.textContent = ids.includes(String(btn.dataset.ideaId)) ? 'Saved' : 'Save idea';
  });
};
document.querySelectorAll('.design-fav-btn').forEach((btn) => btn.addEventListener('click', () => {
  const ids = readFavs();
  const id = String(btn.dataset.ideaId);
  writeFavs(ids.includes(id) ? ids.filter((item) => item !== id) : [...ids, id]);
  renderFav();
  const form = document.getElementById('designQuoteForm');
  if (form?.elements.requirement) form.elements.requirement.value = btn.dataset.requirement || 'Favourite design idea';
  form?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  document.querySelector('#designQuoteForm [name="name"]')?.focus({ preventScroll: true });
}));
document.querySelectorAll('.quote-trigger').forEach((btn) => btn.addEventListener('click', () => {
  const form = document.getElementById('designQuoteForm');
  if (form?.elements.requirement) form.elements.requirement.value = btn.dataset.requirement || btn.dataset.title || 'Design idea quote';
  form?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  document.querySelector('#designQuoteForm [name="name"]')?.focus({ preventScroll: true });
}));
renderFav();

document.getElementById('designQuoteForm')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const message = document.getElementById('designQuoteMessage');
  const response = await fetch('/api/design-idea-leads', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(Object.fromEntries(new FormData(form).entries())) });
  const data = await response.json();
  message.className = `form-message ${response.ok ? 'ok' : 'error'}`;
  message.textContent = response.ok ? 'Thank you. Our design team will contact you.' : (data.error || 'Could not submit quote request.');
  if (response.ok) form.reset();
});
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
