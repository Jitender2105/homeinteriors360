<?php
require __DIR__ . '/../partials/header.php';
$media = array_values(array_filter($project['media'] ?? [], static fn(array $item): bool => ($item['media_type'] ?? 'image') === 'image'));
$cover = $media[0]['media_url'] ?? '/logo.png';
$mode = (($project['listing_for'] ?? 'buy') === 'rent') ? 'rent' : 'buy';
$money = static function (float $amount, bool $monthly = false): string {
    if ($amount <= 0) return 'Price on request';
    if ($amount >= 10000000) $value = rtrim(rtrim(number_format($amount / 10000000, 2), '0'), '.') . ' Cr';
    elseif ($amount >= 100000) $value = rtrim(rtrim(number_format($amount / 100000, 2), '0'), '.') . ' L';
    else $value = number_format($amount, 0);
    return '₹' . $value . ($monthly ? '/month' : '');
};
?>
<main class="property-detail-page">
  <section class="property-detail-head">
    <div class="container">
      <nav class="property-breadcrumb"><a href="/">Home</a><span>/</span><a href="/properties?listing_for=<?= $mode ?>">Properties</a><span>/</span><span><?= htmlspecialchars((string)$project['project_name'], ENT_QUOTES, 'UTF-8') ?></span></nav>
      <div class="property-title-row">
        <div>
          <p class="eyebrow"><?= htmlspecialchars((string)($project['property_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($project['project_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          <h1><?= htmlspecialchars((string)$project['project_name'], ENT_QUOTES, 'UTF-8') ?></h1>
          <p><?= htmlspecialchars(trim((string)($project['address'] ?? '') . ', ' . (string)($project['locality'] ?? '') . ', ' . (string)$project['city'], ', '), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="property-title-price">
          <span>Starting from</span>
          <strong><?= htmlspecialchars($money($mode === 'rent' ? (float)$project['rent_min'] : (float)$project['price_min'], $mode === 'rent'), ENT_QUOTES, 'UTF-8') ?></strong>
          <?php if (!empty($project['price_per_sqft']) && $mode === 'buy'): ?><small>₹<?= number_format((float)$project['price_per_sqft'], 0) ?>/sq.ft.</small><?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="container property-gallery">
    <div class="property-gallery-main"><img src="<?= htmlspecialchars((string)$cover, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$project['project_name'], ENT_QUOTES, 'UTF-8') ?>"></div>
    <div class="property-gallery-side">
      <?php foreach (array_slice($media, 1, 4) as $item): ?><img src="<?= htmlspecialchars((string)$item['media_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?: $project['project_name']), ENT_QUOTES, 'UTF-8') ?>"><?php endforeach; ?>
      <?php if (count($media) < 2): ?><div class="property-gallery-placeholder">More project media can be added from the backend.</div><?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="container property-detail-layout">
      <div class="property-detail-content">
        <section class="property-info-section">
          <h2>Project overview</h2>
          <div class="property-overview-grid">
            <div><span>Developer</span><strong><?= htmlspecialchars((string)($project['builder_name'] ?? 'Not specified'), ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Possession</span><strong><?= !empty($project['possession_date']) ? date('M Y', strtotime((string)$project['possession_date'])) : 'Ask developer' ?></strong></div>
            <div><span>Project size</span><strong><?= (float)($project['total_area_acres'] ?? 0) ?> acres</strong></div>
            <div><span>Towers / units</span><strong><?= (int)($project['total_towers'] ?? 0) ?> / <?= (int)($project['total_units'] ?? 0) ?></strong></div>
            <div><span>RERA</span><strong><?= htmlspecialchars((string)($project['rera_number'] ?? 'Not provided'), ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Area range</span><strong><?= (int)($project['area_min'] ?? 0) ?>–<?= (int)($project['area_max'] ?? 0) ?> sq.ft.</strong></div>
          </div>
          <p><?= nl2br(htmlspecialchars((string)($project['description'] ?: $project['short_description']), ENT_QUOTES, 'UTF-8')) ?></p>
        </section>

        <?php if (!empty($project['highlights_json'])): ?>
          <section class="property-info-section"><h2>Why consider this project</h2><ul class="property-highlight-list"><?php foreach ($project['highlights_json'] as $item): ?><li><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></section>
        <?php endif; ?>

        <section class="property-info-section">
          <h2>Available configurations and prices</h2>
          <div class="property-unit-table">
            <div class="property-unit-head"><span>Configuration</span><span>Area</span><span><?= $mode === 'rent' ? 'Monthly rent' : 'Price' ?></span><span>Availability</span></div>
            <?php foreach (($project['units'] ?? []) as $unit): ?>
              <div class="property-unit-row">
                <span><strong><?= htmlspecialchars((string)$unit['unit_name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string)($unit['furnishing'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></span>
                <span><?= (int)($unit['carpet_area'] ?: $unit['builtup_area']) ?> sq.ft.</span>
                <span><?= htmlspecialchars($money($mode === 'rent' ? (float)$unit['monthly_rent'] : (float)$unit['sale_price'], $mode === 'rent'), ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= (int)$unit['available_units'] ?> units</span>
              </div>
            <?php endforeach; ?>
            <?php if (empty($project['units'])): ?><p class="property-empty-inline">Inventory and pricing will be updated by the project team.</p><?php endif; ?>
          </div>
        </section>

        <?php if (!empty($project['floor_plans'])): ?>
          <section class="property-info-section"><h2>Floor plans</h2><div class="property-floor-grid"><?php foreach ($project['floor_plans'] as $plan): ?><article><img src="<?= htmlspecialchars((string)$plan['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$plan['title'], ENT_QUOTES, 'UTF-8') ?>"><div><h3><?= htmlspecialchars((string)$plan['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(trim((string)($plan['area_label'] ?? '') . ' · ' . (string)($plan['price_label'] ?? ''), ' ·'), ENT_QUOTES, 'UTF-8') ?></p></div></article><?php endforeach; ?></div></section>
        <?php endif; ?>

        <?php if (!empty($project['amenities_json'])): ?>
          <section class="property-info-section"><h2>Amenities</h2><div class="property-amenity-grid"><?php foreach ($project['amenities_json'] as $item): ?><span><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div></section>
        <?php endif; ?>

        <?php if (!empty($project['nearby_json'])): ?>
          <section class="property-info-section"><h2>Location advantages</h2><ul class="property-nearby-list"><?php foreach ($project['nearby_json'] as $item): ?><li><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></section>
        <?php endif; ?>

        <?php if (!empty($project['video_url'])): ?>
          <section class="property-info-section"><h2>Project video</h2><div class="property-video"><iframe src="<?= htmlspecialchars((string)$project['video_url'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars((string)$project['project_name'], ENT_QUOTES, 'UTF-8') ?> video" allowfullscreen loading="lazy"></iframe></div></section>
        <?php endif; ?>
      </div>

      <aside class="property-enquiry-card" id="property-enquiry">
        <p class="eyebrow"><?= $mode === 'rent' ? 'Schedule a visit' : 'Get price and availability' ?></p>
        <h2>Interested in this project?</h2>
        <p>Share your details and the project team will contact you.</p>
        <form id="propertyEnquiryForm">
          <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">
          <input type="hidden" name="requirement" value="<?= $mode ?>">
          <label><span>Name</span><input name="name" required></label>
          <label><span>Phone</span><input name="phone" type="tel" required></label>
          <label><span>Email</span><input name="email" type="email"></label>
          <?php if (!empty($project['units'])): ?><label><span>Configuration</span><select name="unit_id"><option value="">Any configuration</option><?php foreach ($project['units'] as $unit): ?><option value="<?= (int)$unit['id'] ?>"><?= htmlspecialchars((string)$unit['unit_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label><?php endif; ?>
          <label><span>Message</span><textarea name="message" rows="3" placeholder="Your budget, move-in timeline, or preferred visit date"></textarea></label>
          <label class="property-consent"><input type="checkbox" name="consent" value="1" required><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
          <button class="btn-primary" type="submit">Request details</button>
          <p class="form-message" id="propertyEnquiryMessage"></p>
        </form>
      </aside>
    </div>
  </section>
</main>
<script>
document.getElementById('propertyEnquiryForm')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const message = document.getElementById('propertyEnquiryMessage');
  const payload = Object.fromEntries(new FormData(form).entries());
  const response = await fetch('/api/property-enquiries', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
  const data = await response.json();
  message.className = `form-message ${response.ok ? 'ok' : 'error'}`;
  message.textContent = response.ok ? 'Thank you. Your enquiry has been submitted.' : (data.error || 'Could not submit enquiry.');
  if (response.ok) form.reset();
});
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
