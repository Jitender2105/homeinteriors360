<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="profile-hero" style="background-image:url('<?= htmlspecialchars((string)($pro['cover_photo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container profile-hero-inner" data-reveal>
    <img class="profile-avatar" src="<?= htmlspecialchars((string)($pro['profile_pic'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    <div>
      <h1><?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars((string)($pro['specialization'] ?? $pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
      <p>★ <?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?><?php if ((int)($pro['verification_status'] ?? 0) === 1): ?> <span class="verify-badge"><?= htmlspecialchars((string)($content['directory.verified'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></p>
      <button class="btn-primary" type="button" onclick="document.getElementById('quoteModal').classList.add('open')"><?= htmlspecialchars((string)($content['profile.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
    </div>
  </div>
</section>

<section class="section">
  <div class="container" data-reveal>
    <h2><?= htmlspecialchars((string)($content['profile.expertise.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <p><?= htmlspecialchars((string)($pro['bio'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><?= htmlspecialchars((string)($pro['why_work_with_me'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <div class="chip-row">
      <?php foreach (($pro['offerings_json'] ?? []) as $offering): ?>
        <span class="chip"><?= htmlspecialchars((string)$offering, ENT_QUOTES, 'UTF-8') ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container" data-reveal>
    <h2><?= htmlspecialchars((string)($content['profile.portfolio.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="cards-grid">
      <?php foreach ($projects as $project): ?>
        <article class="portfolio-card">
          <?php $projectImage = $project['media_json'][0] ?? ''; if ($projectImage): ?>
            <img src="<?= htmlspecialchars((string)$projectImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <?php endif; ?>
          <div>
            <h3><?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)($content['profile.amount_spent'] ?? ''), ENT_QUOTES, 'UTF-8') ?>: ₹<?= number_format((float)($project['total_cost'] ?? 0), 0) ?></p>
            <p><?= htmlspecialchars((string)($content['profile.timeline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>: <?= (int)($project['timeline_months'] ?? 0) ?> months</p>
            <p><?= htmlspecialchars((string)($content['profile.area'] ?? ''), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($project['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container" data-reveal>
    <h2><?= htmlspecialchars((string)($content['profile.reviews.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="cards-grid">
      <?php foreach ($reviews as $review): ?>
        <article class="review-card">
          <p>★ <?= (int)($review['rating'] ?? 0) ?>/5</p>
          <p>“<?= htmlspecialchars((string)($review['review_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>”</p>
          <p><strong><?= htmlspecialchars((string)($review['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
          <?php if ((int)($review['verified_purchase'] ?? 0) === 1): ?>
            <span class="verify-badge"><?= htmlspecialchars((string)($content['profile.verified_purchase'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <?php if (!empty($review['photos_json'])): ?>
            <div class="photo-row">
              <?php foreach ($review['photos_json'] as $photo): ?>
                <img src="<?= htmlspecialchars((string)$photo, ENT_QUOTES, 'UTF-8') ?>" alt="review" />
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="modal" id="quoteModal" onclick="if(event.target===this){this.classList.remove('open')}">
  <div class="modal-card">
    <button type="button" class="modal-close" onclick="document.getElementById('quoteModal').classList.remove('open')">×</button>
    <h3><?= htmlspecialchars((string)($content['profile.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
    <form id="profileLeadForm" class="stack-form">
      <input type="hidden" name="pro_id" value="<?= (int)$pro['id'] ?>" />
      <input type="hidden" name="source" value="profile" />
      <input name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      <input name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      <input name="city" required placeholder="<?= htmlspecialchars((string)($content['ui.city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      <textarea name="requirement" required placeholder="<?= htmlspecialchars((string)($content['ui.requirement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></textarea>
      <button type="submit" class="btn-primary"><?= htmlspecialchars((string)($content['profile.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
      <p class="form-message" id="profileLeadMessage"></p>
    </form>
  </div>
</div>

<script>
(() => {
  const form = document.getElementById('profileLeadForm');
  if (!form) return;
  const msg = document.getElementById('profileLeadMessage');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = Object.fromEntries(new FormData(form).entries());
    const response = await fetch('/api/leads', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await response.json();

    if (response.ok) {
      msg.className = 'form-message ok';
      msg.textContent = <?= json_encode((string)($content['home.lead.success'] ?? 'Submitted'), JSON_UNESCAPED_UNICODE) ?>;
      form.reset();
      setTimeout(() => document.getElementById('quoteModal').classList.remove('open'), 1000);
      return;
    }

    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
