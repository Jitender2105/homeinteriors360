<?php
$verificationStatus = (string)($pro['verification_status_code'] ?? ((int)($pro['verification_status'] ?? 0) === 1 ? 'PROFESSIONAL_VERIFIED' : 'UNVERIFIED'));
$listingTier = (string)($pro['listing_tier'] ?? (!empty($pro['is_premium']) ? 'PAID' : 'FREE'));
$isVerified = in_array($verificationStatus, ['BUSINESS_VERIFIED', 'PROFESSIONAL_VERIFIED'], true);
$isSponsored = $listingTier === 'SPONSORED';
$approvedPortfolioCount = count($projects ?? []);
require __DIR__ . '/../partials/header.php';
?>

<section class="profile-hero" style="background-image:url('<?= htmlspecialchars((string)($pro['cover_photo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container profile-hero-inner" data-reveal>
    <img class="profile-avatar" src="<?= htmlspecialchars((string)($pro['profile_pic'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
    <div>
      <p class="eyebrow"><?= htmlspecialchars((string)($pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
      <h1><?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
      <div class="profile-hero-chips">
        <span class="profile-hero-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <?php if (!empty($pro['primary_work_type'])): ?>
          <span class="profile-hero-chip">
            <?= htmlspecialchars((string)$pro['primary_work_type'], ENT_QUOTES, 'UTF-8') ?><?= !empty($pro['primary_work_area']) ? ' · ' . htmlspecialchars((string)$pro['primary_work_area'], ENT_QUOTES, 'UTF-8') : '' ?>
          </span>
        <?php endif; ?>
        <?php if (!empty($pro['rating'])): ?>
          <span class="profile-hero-chip rating">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <?= htmlspecialchars((string)$pro['rating'], ENT_QUOTES, 'UTF-8') ?> Rating
          </span>
        <?php endif; ?>
        <?php if ($isVerified): ?>
          <span class="profile-hero-chip verified">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars((string)($content['directory.verified'] ?? 'Verified'), ENT_QUOTES, 'UTF-8') ?>
          </span>
        <?php endif; ?>
        <?php if ($isSponsored): ?>
          <span class="profile-hero-chip premium-chip">Sponsored</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container" data-reveal>
    <div class="profile-top-lead">
      <div class="profile-intro-copy">
        <h2><?= htmlspecialchars((string)($content['profile.lead.title'] ?? 'Get Project Proposal from this Professional'), ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if (!empty($pro['service_summary']) || !empty($pro['profile_description'])): ?>
          <p class="profile-summary"><?= htmlspecialchars((string)($pro['service_summary'] ?? $pro['profile_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if (!empty($pro['office_address']) || !empty($pro['phone']) || !empty($pro['email']) || !empty($pro['website_url'])): ?>
          <div class="chip-row profile-contact-strip">
            <?php if (!empty($pro['office_address'])): ?><span class="chip"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><?= htmlspecialchars((string)$pro['office_address'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty($pro['phone'])): ?><span class="chip"><?= htmlspecialchars((string)$pro['phone'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty($pro['email'])): ?><span class="chip"><?= htmlspecialchars((string)$pro['email'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            <?php if (!empty($pro['website_url'])): ?><a class="chip" href="<?= htmlspecialchars((string)$pro['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Website ↗</a><?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="profile-kpi-grid">
          <div class="profile-kpi-card">
            <strong><?= (int)($pro['years_experience'] ?? 0) ?>+</strong>
            <span>Years Experience</span>
          </div>
          <div class="profile-kpi-card">
            <strong><?= (int)$approvedPortfolioCount ?></strong>
            <span>Approved Portfolio</span>
          </div>
          <div class="profile-kpi-card kpi-rating">
            <strong><?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></strong>
            <span>Rating</span>
          </div>
          <?php if (!empty($pro['response_time_hours'])): ?>
            <div class="profile-kpi-card">
              <strong><?= (int)$pro['response_time_hours'] ?>h</strong>
              <span>Response Time</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <form id="profileLeadTopForm" class="stack-form top-lead-form profile-lead-form">
        <p class="profile-form-heading">Send an Enquiry</p>
        <input type="hidden" name="pro_id" value="<?= (int)$pro['id'] ?>" />
        <input type="hidden" name="source" value="profile" />
        <input name="name" required placeholder="<?= htmlspecialchars((string)($content['ui.name'] ?? 'Your Name'), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="phone" required placeholder="<?= htmlspecialchars((string)($content['ui.phone'] ?? 'Phone Number'), ENT_QUOTES, 'UTF-8') ?>" />
        <input name="city" required placeholder="<?= htmlspecialchars((string)($content['ui.city'] ?? 'Your City'), ENT_QUOTES, 'UTF-8') ?>" />
        <?php require __DIR__ . '/../partials/society-field.php'; ?>
        <select name="budget">
          <option value=""><?= htmlspecialchars((string)($content['ui.budget'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?></option>
          <?php require __DIR__ . '/../partials/budget-options.php'; ?>
        </select>
        <textarea name="requirement" required placeholder="<?= htmlspecialchars((string)($content['ui.requirement'] ?? 'Describe your requirement'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
        <?php require __DIR__ . '/../partials/lead-consent.php'; ?>
        <button type="submit" class="btn-primary"><?= htmlspecialchars((string)($content['profile.cta'] ?? 'Get a Proposal'), ENT_QUOTES, 'UTF-8') ?></button>
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
          <div class="profile-why-block">
            <p class="eyebrow" style="margin-bottom:6px">Why work with me</p>
            <p><?= htmlspecialchars((string)$pro['why_work_with_me'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
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
          <?php if (!empty($pro['founded_year'])): ?><div class="mini-card profile-glance-card"><strong><?= (int)$pro['founded_year'] ?></strong><span>Founded</span></div><?php endif; ?>
          <?php if (!empty($pro['team_size'])): ?><div class="mini-card profile-glance-card"><strong><?= (int)$pro['team_size'] ?>+</strong><span>Team size</span></div><?php endif; ?>
          <?php if (!empty($pro['client_count'])): ?><div class="mini-card profile-glance-card"><strong><?= number_format((int)$pro['client_count']) ?></strong><span>Clients</span></div><?php endif; ?>
          <?php if (!empty($pro['consultation_fee'])): ?><div class="mini-card profile-glance-card"><strong>₹<?= number_format((float)$pro['consultation_fee'], 0) ?></strong><span>Consultation</span></div><?php endif; ?>
          <?php if (!empty($pro['starting_price'])): ?><div class="mini-card profile-glance-card"><strong>₹<?= number_format((float)$pro['starting_price'], 0) ?></strong><span>Starting at</span></div><?php endif; ?>
          <?php if (!empty($pro['office_hours'])): ?><div class="mini-card profile-glance-card"><strong><?= htmlspecialchars((string)$pro['office_hours'], ENT_QUOTES, 'UTF-8') ?></strong><span>Office hours</span></div><?php endif; ?>
        </div>
        <?php if (!empty($pro['office_address']) || !empty($pro['phone']) || !empty($pro['email']) || !empty($pro['website_url']) || !empty($pro['google_business_url'])): ?>
          <div class="profile-contact-card">
            <h3>Contact</h3>
            <ul class="contact-list">
              <?php if (!empty($pro['office_address'])): ?><li><span>Office</span><strong><?= htmlspecialchars((string)$pro['office_address'], ENT_QUOTES, 'UTF-8') ?></strong></li><?php endif; ?>
              <?php if (!empty($pro['phone'])): ?><li><span>Phone</span><strong><?= htmlspecialchars((string)$pro['phone'], ENT_QUOTES, 'UTF-8') ?></strong></li><?php endif; ?>
              <?php if (!empty($pro['email'])): ?><li><span>Email</span><strong><?= htmlspecialchars((string)$pro['email'], ENT_QUOTES, 'UTF-8') ?></strong></li><?php endif; ?>
              <?php if (!empty($pro['website_url'])): ?><li><span>Website</span><strong><a href="<?= htmlspecialchars((string)$pro['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string)$pro['website_url'], ENT_QUOTES, 'UTF-8') ?></a></strong></li><?php endif; ?>
              <?php if (!empty($pro['google_business_url'])): ?><li><span>Google</span><strong><a href="<?= htmlspecialchars((string)$pro['google_business_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open listing ↗</a></strong></li><?php endif; ?>
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
      <h2>Highlights &amp; FAQs</h2>
      <?php if (!empty($pro['awards_json'])): ?>
        <div class="chip-row profile-chip-block">
          <?php foreach (($pro['awards_json'] ?? []) as $award): ?>
            <span class="chip profile-award-chip">🏆 <?= htmlspecialchars((string)$award, ENT_QUOTES, 'UTF-8') ?></span>
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
    <div class="profile-section-head">
      <p class="eyebrow">Work Showcase</p>
      <h2><?= htmlspecialchars((string)($content['profile.portfolio.title'] ?? 'Portfolio'), ENT_QUOTES, 'UTF-8') ?></h2>
    </div>
    <div class="cards-grid portfolio-grid-v2">
      <?php foreach ($projects as $project): ?>
        <article class="portfolio-card portfolio-card-v2">
          <?php $projectImage = $project['media_json'][0] ?? ''; if ($projectImage): ?>
            <div class="portfolio-card-img-wrap">
              <img src="<?= htmlspecialchars((string)$projectImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" />
              <div class="portfolio-card-overlay">
                <p class="portfolio-overlay-type"><?= htmlspecialchars((string)($project['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($project['area_of_work']) ? ' · ' . htmlspecialchars((string)$project['area_of_work'], ENT_QUOTES, 'UTF-8') : '' ?></p>
                <h3><?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
              </div>
            </div>
          <?php endif; ?>
          <div class="portfolio-card-meta">
            <h3><?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <div class="portfolio-meta-row">
              <span><?= htmlspecialchars((string)($content['profile.amount_spent'] ?? 'Budget'), ENT_QUOTES, 'UTF-8') ?>: <strong>₹<?= number_format((float)($project['total_cost'] ?? 0), 0) ?></strong></span>
              <span><?= htmlspecialchars((string)($content['profile.timeline'] ?? 'Timeline'), ENT_QUOTES, 'UTF-8') ?>: <strong><?= htmlspecialchars((string)($project['project_duration_label'] ?: ((int)$project['timeline_months'] . ' months')), ENT_QUOTES, 'UTF-8') ?></strong></span>
            </div>
            <a class="btn-link" href="/portfolio/<?= htmlspecialchars((string)$project['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['profile.project_details'] ?? 'View Project Details'), ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (!empty($reviews)): ?>
<section class="section section-tight profile-reviews-section">
  <div class="container" data-reveal>
    <div class="profile-section-head">
      <p class="eyebrow">Client Voices</p>
      <h2><?= htmlspecialchars((string)($content['profile.reviews.title'] ?? 'Reviews'), ENT_QUOTES, 'UTF-8') ?></h2>
    </div>
    <div class="cards-grid">
      <?php foreach ($reviews as $review): ?>
        <?php $reviewRating = (int)($review['rating'] ?? 0); ?>
        <article class="review-card review-card-v2">
          <div class="review-stars-row">
            <span class="review-stars-visual">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="<?= $s <= $reviewRating ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <?php endfor; ?>
            </span>
            <span class="review-rating-num"><?= $reviewRating ?>/5</span>
              <?php if ((int)($review['verified_purchase'] ?? 0) === 1): ?>
	              <span class="verify-badge"><?= htmlspecialchars((string)($content['profile.verified_purchase'] ?? 'Verified Consultation'), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </div>
          <blockquote class="review-quote">"<?= htmlspecialchars((string)($review['review_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"</blockquote>
          <div class="review-footer">
            <strong class="review-author"><?= htmlspecialchars((string)($review['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            <span class="review-work"><?= htmlspecialchars((string)($review['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($review['area_of_work']) ? ' · ' . htmlspecialchars((string)$review['area_of_work'], ENT_QUOTES, 'UTF-8') : '' ?></span>
          </div>
          <?php if (!empty($review['materials_highlight'])): ?>
            <p class="review-materials"><?= htmlspecialchars((string)$review['materials_highlight'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
          <?php if (!empty($review['photos_json'])): ?>
            <div class="photo-row"><?php foreach ($review['photos_json'] as $photo): ?><img src="<?= htmlspecialchars((string)$photo, ENT_QUOTES, 'UTF-8') ?>" alt="review photo" /><?php endforeach; ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
(() => {
  const form = document.getElementById('profileLeadTopForm');
  if (!form) return;
  const msg = document.getElementById('profileTopLeadMessage');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const payload = Object.fromEntries(new FormData(form).entries());
    const response = await fetch('/api/leads', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await response.json();

    if (response.ok) {
      msg.className = 'form-message ok';
      msg.textContent = <?= json_encode((string)($content['home.lead.success'] ?? 'Submitted successfully!'), JSON_UNESCAPED_UNICODE) ?>;
      form.reset();
      return;
    }

    msg.className = 'form-message error';
    msg.textContent = data.error || 'Failed to submit. Please try again.';
  });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
