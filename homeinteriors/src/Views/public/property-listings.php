<?php
require __DIR__ . '/../partials/header.php';
$filters = $filters ?? [];
$projects = $projects ?? [];
$filterOptions = $filterOptions ?? [];
$listingFor = in_array(($filters['listing_for'] ?? ''), ['buy', 'rent'], true) ? $filters['listing_for'] : 'buy';
$money = static function (float $amount, bool $monthly = false): string {
    if ($amount <= 0) return 'Price on request';
    if ($amount >= 10000000) $value = rtrim(rtrim(number_format($amount / 10000000, 2), '0'), '.') . ' Cr';
    elseif ($amount >= 100000) $value = rtrim(rtrim(number_format($amount / 100000, 2), '0'), '.') . ' L';
    else $value = number_format($amount, 0);
    return '₹' . $value . ($monthly ? '/month' : '');
};
?>
<main class="property-market">
  <section class="property-search-band">
    <div class="container">
      <div class="property-search-heading">
        <div>
          <p class="eyebrow">Homes and residential projects</p>
          <h1>Find a home that fits the way you live.</h1>
        </div>
        <div class="property-mode" role="group" aria-label="Listing type">
          <a class="<?= $listingFor === 'buy' ? 'active' : '' ?>" href="/properties?listing_for=buy">Buy</a>
          <a class="<?= $listingFor === 'rent' ? 'active' : '' ?>" href="/properties?listing_for=rent">Rent</a>
        </div>
      </div>
      <form class="property-search-form" method="get" action="/properties">
        <input type="hidden" name="listing_for" value="<?= htmlspecialchars($listingFor, ENT_QUOTES, 'UTF-8') ?>">
        <label class="property-keyword">
          <span>Search</span>
          <input name="q" value="<?= htmlspecialchars((string)($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Project, builder, locality or city">
        </label>
        <label><span>City</span><select name="city"><option value="">All cities</option><?php foreach (($filterOptions['cities'] ?? []) as $option): ?><option <?= ($filters['city'] ?? '') === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label><span>Property type</span><select name="property_type"><option value="">All types</option><?php foreach (($filterOptions['property_types'] ?? []) as $option): ?><option <?= ($filters['property_type'] ?? '') === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <button class="btn-primary" type="submit">Search properties</button>
      </form>
    </div>
  </section>

  <section class="section property-results-section">
    <div class="container property-results-layout">
      <aside class="property-filter-panel">
        <form method="get" action="/properties">
          <input type="hidden" name="listing_for" value="<?= htmlspecialchars($listingFor, ENT_QUOTES, 'UTF-8') ?>">
          <h2>Filters</h2>
          <label><span>Locality</span><select name="locality"><option value="">Any locality</option><?php foreach (($filterOptions['localities'] ?? []) as $option): ?><option <?= ($filters['locality'] ?? '') === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
          <label><span>Configuration</span><select name="bhk_type"><option value="">Any configuration</option><?php foreach (($filterOptions['bhk_types'] ?? []) as $option): ?><option <?= ($filters['bhk_type'] ?? '') === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
          <div class="property-price-inputs">
            <label><span>Minimum price</span><input type="number" name="price_min" value="<?= htmlspecialchars((string)($filters['price_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="₹ min"></label>
            <label><span>Maximum price</span><input type="number" name="price_max" value="<?= htmlspecialchars((string)($filters['price_max'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="₹ max"></label>
          </div>
          <label><span>Sort by</span><select name="sort"><option value="">Recommended</option><option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: low to high</option><option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: high to low</option><option value="possession" <?= ($filters['sort'] ?? '') === 'possession' ? 'selected' : '' ?>>Possession date</option></select></label>
          <button class="btn-primary property-filter-submit" type="submit">Apply filters</button>
          <a class="btn-link" href="/properties?listing_for=<?= urlencode($listingFor) ?>">Clear filters</a>
        </form>
      </aside>

      <div class="property-results">
        <div class="property-results-head">
          <div><p class="eyebrow"><?= $listingFor === 'rent' ? 'Homes for rent' : 'Projects for sale' ?></p><h2><?= count($projects) ?> matching projects</h2></div>
        </div>
        <?php if (!$projects): ?>
          <div class="property-empty"><h3>No projects match these filters yet.</h3><p>Try a broader city, price, or property type. New inventory can be published from the admin backend.</p></div>
        <?php endif; ?>
        <div class="property-card-list">
          <?php foreach ($projects as $project): ?>
            <?php
              $price = $listingFor === 'rent' ? (float)($project['rent_min'] ?? 0) : (float)($project['price_min'] ?? 0);
              $cover = trim((string)($project['cover_image'] ?? '')) ?: '/logo.png';
            ?>
            <article class="property-result-card">
              <a class="property-result-media" href="/property/<?= htmlspecialchars((string)$project['slug'], ENT_QUOTES, 'UTF-8') ?>">
                <img src="<?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$project['project_name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($project['is_featured'])): ?><span>Featured</span><?php endif; ?>
              </a>
              <div class="property-result-body">
                <div class="property-result-top">
                  <div>
                    <p><?= htmlspecialchars((string)($project['property_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($project['project_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <h3><a href="/property/<?= htmlspecialchars((string)$project['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$project['project_name'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                    <p><?= htmlspecialchars(trim((string)($project['locality'] ?? '') . ', ' . (string)$project['city'], ', '), ENT_QUOTES, 'UTF-8') ?></p>
                  </div>
                  <strong><?= htmlspecialchars($money($price, $listingFor === 'rent'), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="property-spec-row">
                  <span><small>Configurations</small><?= htmlspecialchars((string)($project['configurations'] ?? 'Contact for details'), ENT_QUOTES, 'UTF-8') ?></span>
                  <span><small>Area</small><?= (int)($project['area_min'] ?? 0) ?><?= !empty($project['area_max']) ? '–' . (int)$project['area_max'] : '' ?> sq.ft.</span>
                  <span><small>Builder</small><?= htmlspecialchars((string)($project['builder_name'] ?? 'Independent'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <p class="property-summary"><?= htmlspecialchars((string)($project['short_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="property-card-actions">
                  <a class="btn-primary" href="/property/<?= htmlspecialchars((string)$project['slug'], ENT_QUOTES, 'UTF-8') ?>">View details</a>
                  <a class="btn-muted" href="/property/<?= htmlspecialchars((string)$project['slug'], ENT_QUOTES, 'UTF-8') ?>#property-enquiry">Contact seller</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
