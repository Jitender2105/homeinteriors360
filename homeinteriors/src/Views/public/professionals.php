<?php
require __DIR__ . '/../partials/header.php';
$roles = $filterOptions['roles'] ?? [];
$cities = $filterOptions['cities'] ?? [];
$workTypes = $filterOptions['work_types'] ?? [];
$workAreas = $filterOptions['work_areas'] ?? [];
$initialFilters = $initialFilters ?? [];
$directoryTitle = $directoryTitle ?? (string)($content['directory.title'] ?? '');
$directorySubtitle = $directorySubtitle ?? (string)($content['directory.subtitle'] ?? '');
?>

<section class="dir-hero">
  <div class="container dir-hero-inner">
    <p class="eyebrow">Curated &amp; Verified</p>
    <h1><?= htmlspecialchars($directoryTitle ?: 'Find the Right Interior Professional', ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="dir-hero-subtitle"><?= htmlspecialchars($directorySubtitle ?: 'Browse verified architects, interior designers, and contractors. Filter by city, budget, work type, and rating to find your perfect match.', ENT_QUOTES, 'UTF-8') ?></p>
    <div class="dir-hero-stats">
      <div class="dir-stat"><strong>Verified</strong><span>Badge shown only when marked by admin</span></div>
      <div class="dir-stat-sep"></div>
      <div class="dir-stat"><strong>Delhi NCR</strong><span>City and locality filters</span></div>
      <div class="dir-stat-sep"></div>
      <div class="dir-stat"><strong>Portfolio</strong><span>Compare visible project work</span></div>
      <div class="dir-stat-sep"></div>
      <div class="dir-stat"><strong>Reviews</strong><span>Ratings shown where available</span></div>
    </div>
  </div>
</section>

<section class="section dir-body">
  <div class="container directory-layout">
    <aside class="filter-card dir-filter-card">
      <div class="dir-filter-head">
        <h3>Refine Search</h3>
        <button class="dir-filter-clear" id="clearFilters" type="button">Clear all</button>
      </div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fRole"><?= htmlspecialchars((string)($content['directory.filter.role'] ?? 'Professional Type'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="fRole">
          <option value="" <?= empty($initialFilters['role']) ? 'selected' : '' ?>>All Types</option>
          <?php foreach ($roles as $role): ?>
            <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['role'] ?? '') === $role) ? 'selected' : '' ?>><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fCity"><?= htmlspecialchars((string)($content['directory.filter.city'] ?? 'City'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="fCity">
          <option value="" <?= empty($initialFilters['city']) ? 'selected' : '' ?>>All Cities</option>
          <?php foreach ($cities as $city): ?>
            <option value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['city'] ?? '') === $city) ? 'selected' : '' ?>><?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fWorkType"><?= htmlspecialchars((string)($content['directory.filter.work_type'] ?? 'Work Type'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="fWorkType">
          <option value="" <?= empty($initialFilters['work_type']) ? 'selected' : '' ?>>All Types</option>
          <?php foreach ($workTypes as $workType): ?>
            <option value="<?= htmlspecialchars($workType, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['work_type'] ?? '') === $workType) ? 'selected' : '' ?>><?= htmlspecialchars($workType, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fWorkArea"><?= htmlspecialchars((string)($content['directory.filter.work_area'] ?? 'Area of Work'), ENT_QUOTES, 'UTF-8') ?></label>
        <select id="fWorkArea">
          <option value="" <?= empty($initialFilters['work_area']) ? 'selected' : '' ?>>All Areas</option>
          <?php foreach ($workAreas as $workArea): ?>
            <option value="<?= htmlspecialchars($workArea, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['work_area'] ?? '') === $workArea) ? 'selected' : '' ?>><?= htmlspecialchars($workArea, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="dir-filter-divider"></div>

      <div class="dir-filter-group">
        <label class="dir-filter-label"><?= htmlspecialchars((string)($content['directory.filter.budget'] ?? 'Budget Range (₹)'), ENT_QUOTES, 'UTF-8') ?></label>
        <div class="budget-grid">
          <input id="fBudgetMin" type="number" placeholder="Min ₹" value="<?= htmlspecialchars((string)($initialFilters['budget_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
          <input id="fBudgetMax" type="number" placeholder="Max ₹" value="<?= htmlspecialchars((string)($initialFilters['budget_max'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        </div>
      </div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fExperienceMin">Min Experience (yrs)</label>
        <input id="fExperienceMin" type="number" placeholder="e.g. 5" value="<?= htmlspecialchars((string)($initialFilters['experience_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      </div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fProjectsMin">Min Projects Delivered</label>
        <input id="fProjectsMin" type="number" placeholder="e.g. 20" value="<?= htmlspecialchars((string)($initialFilters['projects_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      </div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fRatingMin">Min Rating</label>
        <input id="fRatingMin" type="number" step="0.1" min="0" max="5" placeholder="e.g. 4.2" value="<?= htmlspecialchars((string)($initialFilters['rating_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      </div>

      <div class="dir-filter-divider"></div>

      <div class="dir-filter-group">
        <label class="dir-filter-label" for="fSortBy">Sort By</label>
        <select id="fSortBy">
          <option value="rating_desc" <?= (($initialFilters['sort_by'] ?? 'rating_desc') === 'rating_desc') ? 'selected' : '' ?>>Rating: High to Low</option>
          <option value="experience_desc" <?= (($initialFilters['sort_by'] ?? '') === 'experience_desc') ? 'selected' : '' ?>>Experience: High to Low</option>
          <option value="projects_desc" <?= (($initialFilters['sort_by'] ?? '') === 'projects_desc') ? 'selected' : '' ?>>Projects: High to Low</option>
          <option value="price_asc" <?= (($initialFilters['sort_by'] ?? '') === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
          <option value="price_desc" <?= (($initialFilters['sort_by'] ?? '') === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
          <option value="newest" <?= (($initialFilters['sort_by'] ?? '') === 'newest') ? 'selected' : '' ?>>Newest Added</option>
        </select>
      </div>
    </aside>

    <div>
      <div class="dir-results-header">
        <p class="dir-results-count"><span id="resultsCount"><?= count($pros) ?></span> professionals found</p>
      </div>

      <div class="listing-list" id="proResults">
        <?php foreach ($pros as $pro): ?>
          <?php
            $slides = [];
            foreach (($pro['portfolio_previews'] ?? []) as $preview) {
                $image = $preview['media_json'][0] ?? '';
                if (!$image) continue;
                $slides[] = [
                    'image' => $image,
                    'title' => $preview['project_name'] ?? '',
                    'location' => $preview['location'] ?? '',
                    'work_type' => $preview['work_type'] ?? '',
                    'area_of_work' => $preview['area_of_work'] ?? '',
                ];
            }
            if (!empty($pro['profile_pic'])) {
                $slides[] = [
                    'image' => $pro['profile_pic'],
                    'title' => $pro['full_name'] ?? '',
                    'location' => $pro['city'] ?? '',
                    'work_type' => $pro['primary_work_type'] ?? '',
                    'area_of_work' => $pro['primary_work_area'] ?? '',
                ];
            }
            $slidesJson = htmlspecialchars(json_encode($slides, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            $verificationStatus = (string)($pro['verification_status_code'] ?? ((int)($pro['verification_status'] ?? 0) === 1 ? 'PROFESSIONAL_VERIFIED' : 'UNVERIFIED'));
            $listingTier = (string)($pro['listing_tier'] ?? (!empty($pro['is_premium']) ? 'PAID' : 'FREE'));
            $isSponsored = $listingTier === 'SPONSORED';
            $isVerified = in_array($verificationStatus, ['BUSINESS_VERIFIED', 'PROFESSIONAL_VERIFIED'], true);
            $dotCount = min(count($slides), 5);
          ?>
          <article class="listing-card <?= $isSponsored ? 'sponsored' : '' ?>" data-carousel-slides="<?= $slidesJson ?>">
            <div class="listing-carousel">
              <img class="listing-carousel-image" src="<?= htmlspecialchars((string)($slides[0]['image'] ?? ($pro['profile_pic'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" />
              <div class="listing-carousel-caption">
                <?php if ($isSponsored): ?><span class="premium-badge">Sponsored</span><?php endif; ?>
                <strong><?= htmlspecialchars((string)($slides[0]['title'] ?? $pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars((string)($slides[0]['location'] ?? $pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <small><?= htmlspecialchars((string)($slides[0]['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($slides[0]['area_of_work']) ? ' · ' . htmlspecialchars((string)$slides[0]['area_of_work'], ENT_QUOTES, 'UTF-8') : '' ?></small>
              </div>
              <?php if ($dotCount > 1): ?>
                <div class="carousel-dots">
                  <?php for ($i = 0; $i < $dotCount; $i++): ?>
                    <span class="carousel-dot<?= $i === 0 ? ' active' : '' ?>"></span>
                  <?php endfor; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="listing-card-body">
              <div class="listing-topline">
                <?php if ($isSponsored): ?><span class="listing-pill listing-pill-premium">Sponsored</span><?php endif; ?>
                <?php if ($isVerified): ?><span class="listing-pill listing-pill-verified">✓ Verified</span><?php endif; ?>
              </div>
              <h4><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></h4>
              <p class="listing-role"><?= htmlspecialchars((string)($pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <span class="listing-sep">·</span> <?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
              <p class="listing-speciality"><?= htmlspecialchars((string)($pro['primary_work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($pro['primary_work_area']) ? ' · ' . htmlspecialchars((string)$pro['primary_work_area'], ENT_QUOTES, 'UTF-8') : '' ?></p>
              <div class="listing-stats-row">
                <span class="stat-chip stat-rating">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                  <?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="stat-chip"><?= (int)($pro['years_experience'] ?? 0) ?>+ yrs exp</span>
                <span class="stat-chip"><?= (int)($pro['projects_delivered'] ?? 0) ?> projects</span>
              </div>
              <div class="listing-price-row">
                <div>
                  <span class="listing-price-label"><?= htmlspecialchars((string)($content['directory.starting_from'] ?? 'Starting from'), ENT_QUOTES, 'UTF-8') ?></span>
                  <p class="listing-price">₹<?= number_format((float)($pro['starting_price'] ?? 0), 0) ?></p>
                </div>
                <a class="btn-primary listing-cta" href="/professionals/<?= htmlspecialchars((string)$pro['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['directory.cta'] ?? 'View Profile'), ENT_QUOTES, 'UTF-8') ?></a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div id="emptyState" class="dir-empty-state" style="display:none;">
        <div class="dir-empty-icon">⌖</div>
        <h3>No professionals found</h3>
        <p><?= htmlspecialchars((string)($content['directory.empty'] ?? 'Try adjusting your filters to find more professionals.'), ENT_QUOTES, 'UTF-8') ?></p>
        <button class="btn-muted" id="clearFiltersEmpty" type="button">Clear all filters</button>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  const role = document.getElementById('fRole');
  const city = document.getElementById('fCity');
  const workType = document.getElementById('fWorkType');
  const workArea = document.getElementById('fWorkArea');
  const min = document.getElementById('fBudgetMin');
  const max = document.getElementById('fBudgetMax');
  const expMin = document.getElementById('fExperienceMin');
  const projectsMin = document.getElementById('fProjectsMin');
  const ratingMin = document.getElementById('fRatingMin');
  const sortBy = document.getElementById('fSortBy');
  const results = document.getElementById('proResults');
  const emptyState = document.getElementById('emptyState');
  const resultsCount = document.getElementById('resultsCount');

  const labels = {
    verified: <?= json_encode((string)($content['directory.verified'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    startingFrom: <?= json_encode((string)($content['directory.starting_from'] ?? 'Starting from'), JSON_UNESCAPED_UNICODE) ?>,
    cta: <?= json_encode((string)($content['directory.cta'] ?? 'View Profile'), JSON_UNESCAPED_UNICODE) ?>,
  };

  function esc(value) {
    return String(value ?? '').replace(/[&<>\"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  }

  const starSvg = '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

  function card(pro) {
    const slides = [];
    (pro.portfolio_previews || []).forEach((preview) => {
      const image = (preview.media_json || [])[0] || '';
      if (!image) return;
      slides.push({
        image,
        title: preview.project_name || '',
        location: preview.location || '',
        work_type: preview.work_type || '',
        area_of_work: preview.area_of_work || '',
      });
    });
    if (pro.profile_pic) {
      slides.push({
        image: pro.profile_pic,
        title: pro.full_name || '',
        location: pro.city || '',
        work_type: pro.primary_work_type || '',
        area_of_work: pro.primary_work_area || '',
      });
    }
    const verificationStatus = pro.verification_status_code || (Number(pro.verification_status) === 1 ? 'PROFESSIONAL_VERIFIED' : 'UNVERIFIED');
    const listingTier = pro.listing_tier || (Number(pro.is_premium) === 1 ? 'PAID' : 'FREE');
    const sponsored = listingTier === 'SPONSORED';
    const verified = ['BUSINESS_VERIFIED', 'PROFESSIONAL_VERIFIED'].includes(verificationStatus);
    const dotCount = Math.min(slides.length, 5);
    const dotsHtml = dotCount > 1
      ? `<div class="carousel-dots">${Array.from({length: dotCount}, (_, i) => `<span class="carousel-dot${i === 0 ? ' active' : ''}"></span>`).join('')}</div>`
      : '';
    return `
      <article class="listing-card ${sponsored ? 'sponsored' : ''}" data-carousel-slides="${esc(JSON.stringify(slides))}">
        <div class="listing-carousel">
          <img class="listing-carousel-image" src="${esc((slides[0] && slides[0].image) || pro.profile_pic || '')}" alt="${esc(pro.full_name || '')}" loading="lazy">
          <div class="listing-carousel-caption">
            ${sponsored ? '<span class="premium-badge">Sponsored</span>' : ''}
            <strong>${esc((slides[0] && slides[0].title) || pro.full_name || '')}</strong>
            <span>${esc((slides[0] && slides[0].location) || pro.city || '')}</span>
            <small>${esc((slides[0] && slides[0].work_type) || '')}${(slides[0] && slides[0].area_of_work) ? ' · ' + esc(slides[0].area_of_work) : ''}</small>
          </div>
          ${dotsHtml}
        </div>
        <div class="listing-card-body">
          <div class="listing-topline">
            ${sponsored ? '<span class="listing-pill listing-pill-premium">Sponsored</span>' : ''}
            ${verified ? '<span class="listing-pill listing-pill-verified">✓ Verified</span>' : ''}
          </div>
          <h4>${esc(pro.full_name || '')}</h4>
          <p class="listing-role">${esc(pro.role || '')} <span class="listing-sep">·</span> ${esc(pro.city || '')}</p>
          <p class="listing-speciality">${esc(pro.primary_work_type || '')}${pro.primary_work_area ? ' · ' + esc(pro.primary_work_area) : ''}</p>
          <div class="listing-stats-row">
            <span class="stat-chip stat-rating">${starSvg} ${esc(pro.rating || '0')}</span>
            <span class="stat-chip">${Number(pro.years_experience || 0)}+ yrs exp</span>
            <span class="stat-chip">${Number(pro.projects_delivered || 0)} projects</span>
          </div>
          <div class="listing-price-row">
            <div>
              <span class="listing-price-label">${labels.startingFrom}</span>
              <p class="listing-price">₹${Number(pro.starting_price || 0).toLocaleString('en-IN')}</p>
            </div>
            <a class="btn-primary listing-cta" href="/professionals/${esc(pro.slug || '')}">${labels.cta}</a>
          </div>
        </div>
      </article>
    `;
  }

  async function load() {
    const qs = new URLSearchParams();
    if (role.value) qs.set('role', role.value);
    if (city.value) qs.set('city', city.value);
    if (workType.value) qs.set('work_type', workType.value);
    if (workArea.value) qs.set('work_area', workArea.value);
    if (min.value) qs.set('budget_min', min.value);
    if (max.value) qs.set('budget_max', max.value);
    if (expMin.value) qs.set('experience_min', expMin.value);
    if (projectsMin.value) qs.set('projects_min', projectsMin.value);
    if (ratingMin.value) qs.set('rating_min', ratingMin.value);
    if (sortBy.value) qs.set('sort_by', sortBy.value);

    const response = await fetch(`/api/pros?${qs.toString()}`);
    const data = await response.json();
    const list = data.pros || [];
    results.innerHTML = list.map(card).join('');
    if (resultsCount) resultsCount.textContent = list.length;
    results.style.display = list.length ? '' : 'none';
    emptyState.style.display = list.length ? 'none' : 'block';
    initCarousels();
  }

  function clearAll() {
    [role, city, workType, workArea, sortBy].forEach(s => { s.selectedIndex = 0; });
    [min, max, expMin, projectsMin, ratingMin].forEach(i => { i.value = ''; });
    load();
  }

  [role, city, workType, workArea, min, max, expMin, projectsMin, ratingMin, sortBy].forEach((el) => {
    el.addEventListener('input', load);
    el.addEventListener('change', load);
  });
  document.getElementById('clearFilters')?.addEventListener('click', clearAll);
  document.getElementById('clearFiltersEmpty')?.addEventListener('click', clearAll);

  const timers = new Map();
  function initCarousels() {
    timers.forEach((t) => clearInterval(t));
    timers.clear();
    document.querySelectorAll('[data-carousel-slides]').forEach((card) => {
      const slides = JSON.parse(card.dataset.carouselSlides || '[]');
      if (!Array.isArray(slides) || slides.length < 2) return;
      const image = card.querySelector('.listing-carousel-image');
      const titleEl = card.querySelector('.listing-carousel-caption strong');
      const locationEl = card.querySelector('.listing-carousel-caption span');
      const detail = card.querySelector('.listing-carousel-caption small');
      const dots = card.querySelectorAll('.carousel-dot');
      let idx = 0;
      const tick = () => {
        idx = (idx + 1) % slides.length;
        image.src = slides[idx].image || image.src;
        image.alt = slides[idx].title || '';
        titleEl.textContent = slides[idx].title || '';
        locationEl.textContent = slides[idx].location || '';
        detail.textContent = `${slides[idx].work_type || ''}${slides[idx].area_of_work ? ' · ' + slides[idx].area_of_work : ''}`;
        dots.forEach((dot, i) => dot.classList.toggle('active', i === idx % dots.length));
      };
      timers.set(card, setInterval(tick, 2800));
    });
  }

  initCarousels();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
