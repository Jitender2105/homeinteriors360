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
<section class="section">
  <div class="container directory-layout" data-reveal>
    <aside class="filter-card">
      <h3><?= htmlspecialchars((string)$directoryTitle, ENT_QUOTES, 'UTF-8') ?></h3>
      <p><?= htmlspecialchars((string)$directorySubtitle, ENT_QUOTES, 'UTF-8') ?></p>

      <label><?= htmlspecialchars((string)($content['directory.filter.role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <select id="fRole">
        <option value="" <?= empty($initialFilters['role']) ? 'selected' : '' ?>></option>
        <?php foreach ($roles as $role): ?>
          <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['role'] ?? '') === $role) ? 'selected' : '' ?>><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>

      <label><?= htmlspecialchars((string)($content['directory.filter.city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <select id="fCity">
        <option value="" <?= empty($initialFilters['city']) ? 'selected' : '' ?>></option>
        <?php foreach ($cities as $city): ?>
          <option value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['city'] ?? '') === $city) ? 'selected' : '' ?>><?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>

      <label><?= htmlspecialchars((string)($content['directory.filter.work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <select id="fWorkType">
        <option value="" <?= empty($initialFilters['work_type']) ? 'selected' : '' ?>></option>
        <?php foreach ($workTypes as $workType): ?>
          <option value="<?= htmlspecialchars($workType, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['work_type'] ?? '') === $workType) ? 'selected' : '' ?>><?= htmlspecialchars($workType, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>

      <label><?= htmlspecialchars((string)($content['directory.filter.work_area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <select id="fWorkArea">
        <option value="" <?= empty($initialFilters['work_area']) ? 'selected' : '' ?>></option>
        <?php foreach ($workAreas as $workArea): ?>
          <option value="<?= htmlspecialchars($workArea, ENT_QUOTES, 'UTF-8') ?>" <?= (($initialFilters['work_area'] ?? '') === $workArea) ? 'selected' : '' ?>><?= htmlspecialchars($workArea, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>

      <label><?= htmlspecialchars((string)($content['directory.filter.budget'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <div class="budget-grid">
        <input id="fBudgetMin" type="number" placeholder="Min" value="<?= htmlspecialchars((string)($initialFilters['budget_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
        <input id="fBudgetMax" type="number" placeholder="Max" value="<?= htmlspecialchars((string)($initialFilters['budget_max'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
      </div>

      <label>Experience (min years)</label>
      <input id="fExperienceMin" type="number" placeholder="e.g. 5" value="<?= htmlspecialchars((string)($initialFilters['experience_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />

      <label>Projects Delivered (min)</label>
      <input id="fProjectsMin" type="number" placeholder="e.g. 20" value="<?= htmlspecialchars((string)($initialFilters['projects_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />

      <label>Rating (min)</label>
      <input id="fRatingMin" type="number" step="0.1" min="0" max="5" placeholder="e.g. 4.2" value="<?= htmlspecialchars((string)($initialFilters['rating_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />

      <label>Sort By</label>
      <select id="fSortBy">
        <option value="rating_desc" <?= (($initialFilters['sort_by'] ?? 'rating_desc') === 'rating_desc') ? 'selected' : '' ?>>Rating: High to Low</option>
        <option value="experience_desc" <?= (($initialFilters['sort_by'] ?? '') === 'experience_desc') ? 'selected' : '' ?>>Experience: High to Low</option>
        <option value="projects_desc" <?= (($initialFilters['sort_by'] ?? '') === 'projects_desc') ? 'selected' : '' ?>>Projects: High to Low</option>
        <option value="price_asc" <?= (($initialFilters['sort_by'] ?? '') === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_desc" <?= (($initialFilters['sort_by'] ?? '') === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="newest" <?= (($initialFilters['sort_by'] ?? '') === 'newest') ? 'selected' : '' ?>>Newest Added</option>
      </select>
    </aside>

    <div>
      <div class="listing-list" id="proResults">
        <?php foreach ($pros as $pro): ?>
          <?php
            $slides = [];
            if (!empty($pro['profile_pic'])) {
                $slides[] = [
                    'image' => $pro['profile_pic'],
                    'title' => $pro['full_name'] ?? '',
                    'location' => $pro['city'] ?? '',
                    'work_type' => $pro['primary_work_type'] ?? '',
                    'area_of_work' => $pro['primary_work_area'] ?? '',
                ];
            }
            foreach (($pro['portfolio_previews'] ?? []) as $preview) {
                $image = $preview['media_json'][0] ?? '';
                if (!$image) {
                    continue;
                }
                $slides[] = [
                    'image' => $image,
                    'title' => $preview['project_name'] ?? '',
                    'location' => $preview['location'] ?? '',
                    'work_type' => $preview['work_type'] ?? '',
                    'area_of_work' => $preview['area_of_work'] ?? '',
                ];
            }
            $slidesJson = htmlspecialchars(json_encode($slides, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            $isPremium = !empty($pro['is_premium']);
          ?>
          <article class="listing-card <?= $isPremium ? 'premium' : '' ?>" data-carousel-slides="<?= $slidesJson ?>">
            <div class="listing-carousel">
              <img class="listing-carousel-image" src="<?= htmlspecialchars((string)($slides[0]['image'] ?? ($pro['profile_pic'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
              <div class="listing-carousel-caption">
                <?php if ($isPremium): ?><span class="premium-badge">Premium</span><?php endif; ?>
                <strong><?= htmlspecialchars((string)($slides[0]['title'] ?? $pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars((string)($slides[0]['location'] ?? $pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <small><?= htmlspecialchars((string)($slides[0]['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($slides[0]['area_of_work']) ? ' · ' . htmlspecialchars((string)$slides[0]['area_of_work'], ENT_QUOTES, 'UTF-8') : '' ?></small>
              </div>
            </div>
            <div>
              <h4><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></h4>
              <p><?= htmlspecialchars((string)($pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
              <p><?= htmlspecialchars((string)($content['profile.work_type'] ?? 'Type of Work'), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($pro['primary_work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
              <p><?= htmlspecialchars((string)($content['profile.work_area'] ?? 'Area of Work'), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string)($pro['primary_work_area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
              <p>★ <?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?><?php if ((int)($pro['verification_status'] ?? 0) === 1): ?> <span class="verify-badge"><?= htmlspecialchars((string)($content['directory.verified'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></p>
              <p><?= htmlspecialchars((string)($content['directory.starting_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?> ₹<?= number_format((float)($pro['starting_price'] ?? 0), 0) ?></p>
              <p><?= htmlspecialchars((string)($content['directory.experience'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <?= (int)($pro['years_experience'] ?? 0) ?>+</p>
              <p>Projects Delivered: <?= (int)($pro['projects_delivered'] ?? 0) ?></p>
              <a class="btn-link" href="/professionals/<?= htmlspecialchars((string)$pro['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($content['directory.cta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <p id="emptyState" class="empty-state" style="display:none;"><?= htmlspecialchars((string)($content['directory.empty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
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

  const labels = {
    verified: <?= json_encode((string)($content['directory.verified'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    startingFrom: <?= json_encode((string)($content['directory.starting_from'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    experience: <?= json_encode((string)($content['directory.experience'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    cta: <?= json_encode((string)($content['directory.cta'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    workTypeLabel: <?= json_encode((string)($content['profile.work_type'] ?? 'Type of Work'), JSON_UNESCAPED_UNICODE) ?>,
    workAreaLabel: <?= json_encode((string)($content['profile.work_area'] ?? 'Area of Work'), JSON_UNESCAPED_UNICODE) ?>,
  };

  function esc(value) {
    return String(value ?? '').replace(/[&<>\"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  }

  function card(pro) {
    const slides = [];
    if (pro.profile_pic) {
      slides.push({
        image: pro.profile_pic,
        title: pro.full_name || '',
        location: pro.city || '',
        work_type: pro.primary_work_type || '',
        area_of_work: pro.primary_work_area || '',
      });
    }
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
    const premium = Number(pro.is_premium) === 1;
    return `
      <article class="listing-card ${premium ? 'premium' : ''}" data-carousel-slides="${esc(JSON.stringify(slides))}">
        <div class="listing-carousel">
          <img class="listing-carousel-image" src="${esc((slides[0] && slides[0].image) || pro.profile_pic || '')}" alt="${esc(pro.full_name || '')}">
          <div class="listing-carousel-caption">
            ${premium ? '<span class="premium-badge">Premium</span>' : ''}
            <strong>${esc((slides[0] && slides[0].title) || pro.full_name || '')}</strong>
            <span>${esc((slides[0] && slides[0].location) || pro.city || '')}</span>
            <small>${esc((slides[0] && slides[0].work_type) || '')}${(slides[0] && slides[0].area_of_work) ? ' · ' + esc(slides[0].area_of_work) : ''}</small>
          </div>
        </div>
        <div>
          <h4>${esc(pro.full_name || '')}</h4>
          <p>${esc(pro.role || '')} · ${esc(pro.city || '')}</p>
          <p>${labels.workTypeLabel}: ${esc(pro.primary_work_type || '')}</p>
          <p>${labels.workAreaLabel}: ${esc(pro.primary_work_area || '')}</p>
          <p>★ ${esc(pro.rating || 0)} ${Number(pro.verification_status) === 1 ? `<span class="verify-badge">${labels.verified}</span>` : ''}</p>
          <p>${labels.startingFrom} ₹${Number(pro.starting_price || 0).toLocaleString('en-IN')}</p>
          <p>${labels.experience} ${Number(pro.years_experience || 0)}+</p>
          <p>Projects Delivered: ${Number(pro.projects_delivered || 0)}</p>
          <a class="btn-link" href="/professionals/${esc(pro.slug || '')}">${labels.cta}</a>
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
    emptyState.style.display = list.length ? 'none' : 'block';
    initCarousels();
  }

  [role, city, workType, workArea, min, max, expMin, projectsMin, ratingMin, sortBy].forEach((el) => {
    el.addEventListener('input', load);
    el.addEventListener('change', load);
  });

  const timers = new Map();
  function initCarousels() {
    timers.forEach((timer) => clearInterval(timer));
    timers.clear();
    document.querySelectorAll('[data-carousel-slides]').forEach((card) => {
      const slides = JSON.parse(card.dataset.carouselSlides || '[]');
      if (!Array.isArray(slides) || slides.length < 2) return;
      const image = card.querySelector('.listing-carousel-image');
      const title = card.querySelector('.listing-carousel-caption strong');
      const location = card.querySelector('.listing-carousel-caption span');
      const detail = card.querySelector('.listing-carousel-caption small');
      let idx = 0;
      const tick = () => {
        idx = (idx + 1) % slides.length;
        image.src = slides[idx].image || image.src;
        image.alt = slides[idx].title || '';
        title.textContent = slides[idx].title || '';
        location.textContent = slides[idx].location || '';
        detail.textContent = `${slides[idx].work_type || ''}${slides[idx].area_of_work ? ' · ' + slides[idx].area_of_work : ''}`;
      };
      timers.set(card, setInterval(tick, 2800));
    });
  }

  initCarousels();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
