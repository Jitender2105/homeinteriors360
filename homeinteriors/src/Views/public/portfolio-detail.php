<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <p class="muted-line"><?= htmlspecialchars((string)($content['portfolio.hero.title'] ?? 'Project Portfolio Details'), ENT_QUOTES, 'UTF-8') ?></p>
    <h1><?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string)($project['project_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

    <div class="portfolio-kpis">
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['profile.amount_spent'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p>₹<?= number_format((float)($project['total_cost'] ?? 0), 0) ?></p></div>
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['profile.timeline'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars((string)($project['project_duration_label'] ?: ((int)$project['timeline_months'] . ' months')), ENT_QUOTES, 'UTF-8') ?></p></div>
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['profile.work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars((string)($project['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></div>
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['profile.work_area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars((string)($project['area_of_work'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></div>
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['profile.area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars((string)($project['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></div>
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['portfolio.design_style'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars((string)($project['design_style'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></div>
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['portfolio.team_size'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= (int)($project['team_size'] ?? 0) ?></p></div>
      <div class="card-lite"><h3><?= htmlspecialchars((string)($content['portfolio.warranty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3><p><?= (int)($project['warranty_years'] ?? 0) ?> years</p></div>
    </div>

    <h2><?= htmlspecialchars((string)($content['profile.materials_used'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="chip-row"><?php foreach (($project['materials_json'] ?? []) as $material): ?><span class="chip"><?= htmlspecialchars((string)$material, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>

    <h2 style="margin-top:18px;">Project Gallery</h2>
    <div class="portfolio-gallery"><?php foreach (($project['media_json'] ?? []) as $image): ?><img src="<?= htmlspecialchars((string)$image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($project['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" /><?php endforeach; ?></div>

    <?php if (!empty($project['video_url'])): ?>
      <h2 style="margin-top:20px;"><?= htmlspecialchars((string)($content['portfolio.video.title'] ?? 'Project Video'), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="video-frame"><iframe src="<?= htmlspecialchars((string)$project['video_url'], ENT_QUOTES, 'UTF-8') ?>" title="Project video" loading="lazy" allowfullscreen></iframe></div>
    <?php endif; ?>

    <?php if (!empty($project['testimonial_text'])): ?>
      <h2 style="margin-top:20px;"><?= htmlspecialchars((string)($content['profile.reviews.title'] ?? 'Customer Reviews'), ENT_QUOTES, 'UTF-8') ?></h2>
      <article class="review-card">
        <p>★ <?= (int)($project['testimonial_rating'] ?? 0) ?>/5</p>
        <p>“<?= htmlspecialchars((string)$project['testimonial_text'], ENT_QUOTES, 'UTF-8') ?>”</p>
        <p><strong><?= htmlspecialchars((string)($project['testimonial_client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
      </article>
    <?php endif; ?>

    <div class="portfolio-pro-cta card-lite" style="margin-top:24px;">
      <p>Professional: <strong><?= htmlspecialchars((string)($project['pro_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars((string)($project['pro_role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</p>
      <a class="btn-link" href="/professionals/<?= htmlspecialchars((string)($project['pro_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['directory.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
    </div>

    <?php if (!empty($relatedProjects)): ?>
      <h2 style="margin-top:24px;"><?= htmlspecialchars((string)($content['portfolio.more_projects'] ?? 'More Projects by this Professional'), ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="cards-grid">
        <?php foreach ($relatedProjects as $rp): ?>
          <article class="portfolio-card">
            <?php $rpImage = $rp['media_json'][0] ?? ''; if ($rpImage): ?><img src="<?= htmlspecialchars((string)$rpImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($rp['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" /><?php endif; ?>
            <div>
              <h3><?= htmlspecialchars((string)($rp['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
              <p>₹<?= number_format((float)($rp['total_cost'] ?? 0), 0) ?></p>
              <a class="btn-link" href="/portfolio/<?= htmlspecialchars((string)$rp['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['profile.project_details'] ?? 'View Project Details'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
