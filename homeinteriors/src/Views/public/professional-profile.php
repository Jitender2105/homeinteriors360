<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="profile-hero" style="background-image:url('<?= htmlspecialchars((string)($pro['cover_photo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container profile-hero-inner" data-reveal>
    <img class="profile-avatar" src="<?= htmlspecialchars((string)($pro['profile_pic'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    <div>
      <h1><?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
      <p><?= htmlspecialchars((string)($pro['specialization'] ?? $pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
      <p><?= htmlspecialchars((string)($content['profile.work_type'] ?? 'Type of Work'), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($pro['primary_work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($content['profile.work_area'] ?? 'Area of Work'), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($pro['primary_work_area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
      <p>★ <?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?><?php if ((int)($pro['verification_status'] ?? 0) === 1): ?> <span class="verify-badge"><?= htmlspecialchars((string)($content['directory.verified'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></p>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container" data-reveal>
    <div class="profile-top-lead">
      <div>
        <h2><?= htmlspecialchars((string)($content['profile.lead.title'] ?? 'Get Project Proposal from this Professional'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars((string)($pro['profile_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <form id="profileLeadTopForm" class="stack-form top-lead-form">
        <input type="hidden" name="pro_id" value="<?= (int)$pro['id'] ?>" />
        <input type="hidden" name="source" value="profile" />
        <input name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="city" required placeholder="<?= htmlspecialchars((string)($content['ui.city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        <textarea name="requirement" required placeholder="<?= htmlspecialchars((string)($content['ui.requirement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></textarea>
        <button type="submit" class="btn-primary"><?= htmlspecialchars((string)($content['profile.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
        <p class="form-message" id="profileTopLeadMessage"></p>
      </form>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container" data-reveal>
    <h2><?= htmlspecialchars((string)($content['profile.expertise.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <p><?= htmlspecialchars((string)($pro['bio'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><?= htmlspecialchars((string)($pro['why_work_with_me'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

    <div class="profile-meta-grid">
      <div class="card-lite">
        <h3><?= htmlspecialchars((string)($content['profile.materials.title'] ?? 'Preferred Materials'), ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="chip-row"><?php foreach (($pro['materials_json'] ?? []) as $material): ?><span class="chip"><?= htmlspecialchars((string)$material, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
      </div>
      <div class="card-lite">
        <h3><?= htmlspecialchars((string)($content['profile.languages'] ?? 'Languages'), ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="chip-row"><?php foreach (($pro['languages_json'] ?? []) as $language): ?><span class="chip"><?= htmlspecialchars((string)$language, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
      </div>
      <div class="card-lite">
        <h3><?= htmlspecialchars((string)($content['profile.response_time'] ?? 'Average Response Time'), ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= (int)($pro['response_time_hours'] ?? 0) ?> hours</p>
      </div>
      <div class="card-lite">
        <h3><?= htmlspecialchars((string)($content['profile.work_area'] ?? 'Area of Work'), ENT_QUOTES, 'UTF-8') ?></h3>
        <div class="chip-row"><?php foreach (($pro['service_areas'] ?? []) as $serviceArea): ?><span class="chip"><?= htmlspecialchars((string)$serviceArea, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
      </div>
    </div>

    <div class="chip-row" style="margin-top:12px;">
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
            <p><?= htmlspecialchars((string)($content['profile.timeline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($project['project_duration_label'] ?: ((int)$project['timeline_months'] . ' months')), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars((string)($content['profile.work_type'] ?? 'Type of Work'), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($project['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars((string)($content['profile.work_area'] ?? 'Area of Work'), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($project['area_of_work'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <a class="btn-link" href="/portfolio/<?= htmlspecialchars((string)$project['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['profile.project_details'] ?? 'View Project Details'), ENT_QUOTES, 'UTF-8') ?></a>
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
          <p><?= htmlspecialchars((string)($review['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($review['area_of_work'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          <?php if (!empty($review['materials_highlight'])): ?><p><?= htmlspecialchars((string)$review['materials_highlight'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
          <?php if ((int)($review['verified_purchase'] ?? 0) === 1): ?>
            <span class="verify-badge"><?= htmlspecialchars((string)($content['profile.verified_purchase'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <?php if (!empty($review['photos_json'])): ?>
            <div class="photo-row"><?php foreach ($review['photos_json'] as $photo): ?><img src="<?= htmlspecialchars((string)$photo, ENT_QUOTES, 'UTF-8') ?>" alt="review" /><?php endforeach; ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
(() => {
  const form = document.getElementById('profileLeadTopForm');
  if (!form) return;
  const msg = document.getElementById('profileTopLeadMessage');

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
      return;
    }

    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
