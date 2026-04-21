<?php
require __DIR__ . '/../partials/header.php';
$roles = array_values(array_unique(array_filter(array_map(static fn(array $p): string => (string)($p['role'] ?? ''), $pros))));
$cities = array_values(array_unique(array_filter(array_map(static fn(array $p): string => (string)($p['city'] ?? ''), $pros))));
sort($roles);
sort($cities);
?>
<section class="section">
  <div class="container directory-layout" data-reveal>
    <aside class="filter-card">
      <h3><?= htmlspecialchars((string)($content['directory.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
      <p><?= htmlspecialchars((string)($content['directory.subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

      <label><?= htmlspecialchars((string)($content['directory.filter.role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <select id="fRole">
        <option value=""></option>
        <?php foreach ($roles as $role): ?>
          <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>

      <label><?= htmlspecialchars((string)($content['directory.filter.city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <select id="fCity">
        <option value=""></option>
        <?php foreach ($cities as $city): ?>
          <option value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>

      <label><?= htmlspecialchars((string)($content['directory.filter.budget'] ?? ''), ENT_QUOTES, 'UTF-8') ?></label>
      <div class="budget-grid">
        <input id="fBudgetMin" type="number" placeholder="Min" />
        <input id="fBudgetMax" type="number" placeholder="Max" />
      </div>
    </aside>

    <div>
      <div class="cards-grid" id="proResults">
        <?php foreach ($pros as $pro): ?>
          <article class="listing-card">
            <img src="<?= htmlspecialchars((string)($pro['profile_pic'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($pro['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
            <div>
              <h4><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></h4>
              <p><?= htmlspecialchars((string)($pro['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
              <p>★ <?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?><?php if ((int)($pro['verification_status'] ?? 0) === 1): ?> <span class="verify-badge"><?= htmlspecialchars((string)($content['directory.verified'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></p>
              <p><?= htmlspecialchars((string)($content['directory.starting_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?> ₹<?= number_format((float)($pro['starting_price'] ?? 0), 0) ?></p>
              <p><?= htmlspecialchars((string)($content['directory.experience'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <?= (int)($pro['years_experience'] ?? 0) ?>+</p>
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
  const min = document.getElementById('fBudgetMin');
  const max = document.getElementById('fBudgetMax');
  const results = document.getElementById('proResults');
  const emptyState = document.getElementById('emptyState');

  const labels = {
    verified: <?= json_encode((string)($content['directory.verified'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    startingFrom: <?= json_encode((string)($content['directory.starting_from'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    experience: <?= json_encode((string)($content['directory.experience'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
    cta: <?= json_encode((string)($content['directory.cta'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
  };

  function card(pro) {
    return `
      <article class="listing-card">
        <img src="${pro.profile_pic || ''}" alt="${pro.full_name || ''}">
        <div>
          <h4>${pro.full_name || ''}</h4>
          <p>${pro.role || ''} · ${pro.city || ''}</p>
          <p>★ ${pro.rating || 0} ${Number(pro.verification_status) === 1 ? `<span class="verify-badge">${labels.verified}</span>` : ''}</p>
          <p>${labels.startingFrom} ₹${Number(pro.starting_price || 0).toLocaleString('en-IN')}</p>
          <p>${labels.experience} ${Number(pro.years_experience || 0)}+</p>
          <a class="btn-link" href="/professionals/${pro.slug}">${labels.cta}</a>
        </div>
      </article>
    `;
  }

  async function load() {
    const qs = new URLSearchParams();
    if (role.value) qs.set('role', role.value);
    if (city.value) qs.set('city', city.value);
    if (min.value) qs.set('budget_min', min.value);
    if (max.value) qs.set('budget_max', max.value);

    const response = await fetch(`/api/pros?${qs.toString()}`);
    const data = await response.json();
    const list = data.pros || [];
    results.innerHTML = list.map(card).join('');
    emptyState.style.display = list.length ? 'none' : 'block';
  }

  [role, city, min, max].forEach((el) => {
    el.addEventListener('input', load);
    el.addEventListener('change', load);
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
