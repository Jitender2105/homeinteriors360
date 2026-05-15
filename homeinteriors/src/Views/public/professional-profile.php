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
      <div class="profile-intro-copy">
        <h2><?= htmlspecialchars((string)($content['profile.lead.title'] ?? 'Get Project Proposal from this Professional'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars((string)($pro['service_summary'] ?? $pro['profile_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <?php if (!empty($pro['office_address']) || !empty($pro['phone']) || !empty($pro['email']) || !empty($pro['website_url'])): ?>
          <div class="chip-row profile-contact-strip">
            <?php if (!empty($pro['office_address'])): ?><span class="chip"><?= htmlspecialchars((string)$pro['office_address'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty($pro['phone'])): ?><span class="chip"><?= htmlspecialchars((string)$pro['phone'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty($pro['email'])): ?><span class="chip"><?= htmlspecialchars((string)$pro['email'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty($pro['website_url'])): ?><a class="chip" href="<?= htmlspecialchars((string)$pro['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Website</a><?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="profile-mini-stats">
          <div class="mini-card"><strong><?= (int)($pro['years_experience'] ?? 0) ?>+</strong><span>Years</span></div>
          <div class="mini-card"><strong><?= (int)($pro['projects_delivered'] ?? 0) ?>+</strong><span>Projects</span></div>
          <div class="mini-card"><strong><?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></strong><span>Rating</span></div>
          <?php if (!empty($pro['response_time_hours'])): ?><div class="mini-card"><strong><?= (int)$pro['response_time_hours'] ?>h</strong><span>Response</span></div><?php endif; ?>
        </div>
      </div>
      <form id="profileLeadTopForm" class="stack-form top-lead-form">
        <input type="hidden" name="pro_id" value="<?= (int)$pro['id'] ?>" />
        <input type="hidden" name="source" value="profile" />
        <input name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="city" required placeholder="<?= htmlspecialchars((string)($content['ui.city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="society_area" placeholder="<?= htmlspecialchars((string)($content['ui.society_area'] ?? 'Society / Area'), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="budget" placeholder="<?= htmlspecialchars((string)($content['ui.budget'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?>" />
        <textarea name="requirement" required placeholder="<?= htmlspecialchars((string)($content['ui.requirement'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></textarea>
        <button type="submit" class="btn-primary"><?= htmlspecialchars((string)($content['profile.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></button>
        <p class="form-message" id="profileTopLeadMessage"></p>
      </form>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container" data-reveal>
    <div class="profile-rich-grid">
      <article class="profile-panel profile-panel-main">
        <p class="eyebrow"><?= htmlspecialchars((string)($content['profile.expertise.title'] ?? 'Professional Profile'), ENT_QUOTES, 'UTF-8') ?></p>
        <h2><?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if (!empty($pro['service_summary'])): ?>
          <p class="profile-summary"><?= htmlspecialchars((string)$pro['service_summary'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if (!empty($pro['profile_description'])): ?>
          <p><?= htmlspecialchars((string)$pro['profile_description'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if (!empty($pro['bio'])): ?>
          <p><?= htmlspecialchars((string)$pro['bio'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if (!empty($pro['why_work_with_me'])): ?>
          <p><?= htmlspecialchars((string)$pro['why_work_with_me'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if (!empty($pro['offerings_json'])): ?>
          <div class="chip-row profile-chip-block">
            <?php foreach (($pro['offerings_json'] ?? []) as $offering): ?>
              <span class="chip"><?= htmlspecialchars((string)$offering, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </article>
      <aside class="profile-panel profile-panel-side">
        <h3>At a glance</h3>
        <div class="profile-stat-stack">
          <?php if (!empty($pro['founded_year'])): ?><div class="mini-card"><strong><?= (int)$pro['founded_year'] ?></strong><span>Founded</span></div><?php endif; ?>
          <?php if (!empty($pro['team_size'])): ?><div class="mini-card"><strong><?= (int)$pro['team_size'] ?>+</strong><span>Team size</span></div><?php endif; ?>
          <?php if (!empty($pro['client_count'])): ?><div class="mini-card"><strong><?= number_format((int)$pro['client_count']) ?></strong><span>Client count</span></div><?php endif; ?>
          <?php if (!empty($pro['consultation_fee'])): ?><div class="mini-card"><strong>₹<?= number_format((float)$pro['consultation_fee'], 0) ?></strong><span>Consultation</span></div><?php endif; ?>
          <?php if (!empty($pro['starting_price'])): ?><div class="mini-card"><strong>₹<?= number_format((float)$pro['starting_price'], 0) ?></strong><span>Starting at</span></div><?php endif; ?>
          <?php if (!empty($pro['office_hours'])): ?><div class="mini-card"><strong><?= htmlspecialchars((string)$pro['office_hours'], ENT_QUOTES, 'UTF-8') ?></strong><span>Office hours</span></div><?php endif; ?>
        </div>
        <?php if (!empty($pro['office_address']) || !empty($pro['phone']) || !empty($pro['email']) || !empty($pro['website_url']) || !empty($pro['google_business_url'])): ?>
          <div class="profile-contact-card">
            <h3>Contact</h3>
            <ul class="contact-list">
              <?php if (!empty($pro['office_address'])): ?><li><span>Office</span><strong><?= htmlspecialchars((string)$pro['office_address'], ENT_QUOTES, 'UTF-8') ?></strong></li><?php endif; ?>
              <?php if (!empty($pro['phone'])): ?><li><span>Phone</span><strong><?= htmlspecialchars((string)$pro['phone'], ENT_QUOTES, 'UTF-8') ?></strong></li><?php endif; ?>
              <?php if (!empty($pro['email'])): ?><li><span>Email</span><strong><?= htmlspecialchars((string)$pro['email'], ENT_QUOTES, 'UTF-8') ?></strong></li><?php endif; ?>
              <?php if (!empty($pro['website_url'])): ?><li><span>Website</span><strong><a href="<?= htmlspecialchars((string)$pro['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string)$pro['website_url'], ENT_QUOTES, 'UTF-8') ?></a></strong></li><?php endif; ?>
              <?php if (!empty($pro['google_business_url'])): ?><li><span>Google Business</span><strong><a href="<?= htmlspecialchars((string)$pro['google_business_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open listing</a></strong></li><?php endif; ?>
            </ul>
          </div>
        <?php endif; ?>
      </aside>
    </div>
    <div class="profile-meta-grid profile-meta-grid-dense">
      <?php if (!empty($pro['service_areas'])): ?>
        <div class="card-lite">
          <h3><?= htmlspecialchars((string)($content['profile.work_area'] ?? 'Area of Work'), ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="chip-row"><?php foreach (($pro['service_areas'] ?? []) as $serviceArea): ?><span class="chip"><?= htmlspecialchars((string)$serviceArea, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($pro['materials_json'])): ?>
        <div class="card-lite">
          <h3><?= htmlspecialchars((string)($content['profile.materials.title'] ?? 'Preferred Materials'), ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="chip-row"><?php foreach (($pro['materials_json'] ?? []) as $material): ?><span class="chip"><?= htmlspecialchars((string)$material, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($pro['design_styles_json'])): ?>
        <div class="card-lite">
          <h3>Design Styles</h3>
          <div class="chip-row"><?php foreach (($pro['design_styles_json'] ?? []) as $style): ?><span class="chip"><?= htmlspecialchars((string)$style, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($pro['languages_json'])): ?>
        <div class="card-lite">
          <h3><?= htmlspecialchars((string)($content['profile.languages'] ?? 'Languages'), ENT_QUOTES, 'UTF-8') ?></h3>
          <div class="chip-row"><?php foreach (($pro['languages_json'] ?? []) as $language): ?><span class="chip"><?= htmlspecialchars((string)$language, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($pro['certifications_json'])): ?>
        <div class="card-lite">
          <h3>Certifications</h3>
          <div class="chip-row"><?php foreach (($pro['certifications_json'] ?? []) as $certification): ?><span class="chip"><?= htmlspecialchars((string)$certification, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($pro['office_hours'])): ?>
        <div class="card-lite">
          <h3>Office Hours</h3>
          <p><?= htmlspecialchars((string)$pro['office_hours'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container profile-story-grid" data-reveal>
    <article class="profile-panel">
      <p class="eyebrow">Execution flow</p>
      <h2>How they work</h2>
      <?php if (!empty($pro['process_steps_json'])): ?>
        <div class="process-grid">
          <?php foreach (($pro['process_steps_json'] ?? []) as $index => $step): ?>
            <?php
              $parts = array_map('trim', explode('|', (string)$step, 2));
              $title = $parts[0] ?? ('Step ' . ($index + 1));
              $detail = $parts[1] ?? '';
            ?>
            <div class="process-card">
              <strong><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></strong>
              <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
              <?php if ($detail !== ''): ?><p><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p><?= htmlspecialchars((string)($pro['service_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
    </article>
    <article class="profile-panel">
      <p class="eyebrow">Recognition</p>
      <h2>Highlights & FAQs</h2>
      <?php if (!empty($pro['awards_json'])): ?>
        <div class="chip-row profile-chip-block">
          <?php foreach (($pro['awards_json'] ?? []) as $award): ?>
            <span class="chip"><?= htmlspecialchars((string)$award, ENT_QUOTES, 'UTF-8') ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($pro['faq_json'])): ?>
        <div class="faq-stack">
          <?php foreach (($pro['faq_json'] ?? []) as $faq): ?>
            <?php $parts = array_map('trim', explode('|', (string)$faq, 2)); ?>
            <details class="faq-item">
              <summary><?= htmlspecialchars($parts[0] ?: (string)$faq, ENT_QUOTES, 'UTF-8') ?></summary>
              <?php if (!empty($parts[1])): ?><p><?= htmlspecialchars($parts[1], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            </details>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </article>
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

<section class="section section-tight">
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
