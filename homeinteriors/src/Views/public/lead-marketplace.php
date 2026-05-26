<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="lead-market-firstfold" data-reveal>
  <div class="container lead-market-firstfold-grid">
    <div class="lead-market-copy">
      <p class="eyebrow eyebrow-dark">Lead Marketplace</p>
      <h1>Buy filtered homeowner leads with full context.</h1>
      <p class="hero-subtitle">Choose leads by city, society, budget, type of work, and date range. Your matching packages update instantly in the first fold.</p>
      <div class="story-badges">
        <span class="chip">City-wise leads</span>
        <span class="chip">Work-type filters</span>
        <span class="chip">Slab pricing</span>
      </div>
      <div class="lead-offer-strip">
        <span>Launch Offer</span>
        <strong>First 10 leads are free</strong>
        <p>Available once for first-time buyers. Final eligibility is checked at purchase.</p>
      </div>
      <div id="publicCouponStrip" class="lead-public-coupons"></div>
    </div>

    <div class="lead-card lead-card-flat lead-filter-card lead-market-panel">
      <div class="hero-lead-grid lead-market-controls">
        <label><span>Date Filter</span><select id="leadDateFilter"><option value="today">Today</option><option value="last_7_days">Last 7 days</option><option value="last_30_days" selected>Last 30 days</option><option value="custom">Custom date range</option></select></label>
        <label><span>City</span><select id="leadCityFilter"><option value="">All cities</option></select></label>
        <label><span>Type of Work</span><select id="leadWorkFilter"><option value="">All work types</option></select></label>
        <a class="btn-primary hero-lead-submit" href="/lead-cart">Open Cart</a>
        <label><span>Start Date</span><input id="leadStartDate" type="date" disabled></label>
        <label><span>End Date</span><input id="leadEndDate" type="date" disabled></label>
      </div>
      <p class="form-message" id="leadMarketMsg"></p>
      <div id="leadSections" class="lead-market-sections lead-market-first-results"></div>
    </div>
  </div>
</section>

<section class="section section-tight" data-reveal>
  <div class="container twin-grid">
    <article class="story-panel story-essay">
      <p class="eyebrow eyebrow-dark">How pricing works</p>
      <h2>Transparent slab pricing for every filter.</h2>
      <p>First-time buyers get 10 free leads once. After that, paid slabs apply: first 100 leads at ₹100, leads 101 to 1000 at ₹80, and additional volume above 1000 at ₹60.</p>
    </article>
    <article class="story-panel story-essay">
      <p class="eyebrow eyebrow-dark">Secure access</p>
      <h2>Download only what you buy.</h2>
      <p>Each purchase stores the exact filter criteria. Your dashboard can only download Excel files for paid lead packages linked to your buyer account.</p>
    </article>
  </div>
</section>

<script>
(() => {
  const sections = document.getElementById('leadSections');
  const msg = document.getElementById('leadMarketMsg');
  const dateFilter = document.getElementById('leadDateFilter');
  const cityFilter = document.getElementById('leadCityFilter');
  const workFilter = document.getElementById('leadWorkFilter');
  const start = document.getElementById('leadStartDate');
  const end = document.getElementById('leadEndDate');
  let allItems = [];
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  const money = (value) => `₹${Number(value || 0).toLocaleString('en-IN')}`;
  const sectionOrder = ['City', 'Society', 'City + Budget', 'Type of Work', 'City + Type of Work', 'City + Society', 'City + Budget + Type', 'Society + Type of Work'];

  function params() {
    const p = new URLSearchParams({ date_filter: dateFilter.value });
    if (dateFilter.value === 'custom') {
      if (start.value) p.set('start_date', start.value);
      if (end.value) p.set('end_date', end.value);
    }
    return p;
  }

  function hydrateFilters(items) {
    const currentCity = cityFilter.value;
    const currentWork = workFilter.value;
    const cities = [...new Set(items.map((item) => item.criteria?.city).filter(Boolean))].sort();
    const workTypes = [...new Set(items.map((item) => item.criteria?.work_type).filter(Boolean))].sort();
    cityFilter.innerHTML = '<option value="">All cities</option>' + cities.map((city) => `<option value="${esc(city)}">${esc(city)}</option>`).join('');
    workFilter.innerHTML = '<option value="">All work types</option>' + workTypes.map((type) => `<option value="${esc(type)}">${esc(type)}</option>`).join('');
    cityFilter.value = cities.includes(currentCity) ? currentCity : '';
    workFilter.value = workTypes.includes(currentWork) ? currentWork : '';
  }

  function selectedItems() {
    return allItems.filter((item) => {
      if (cityFilter.value && item.criteria?.city !== cityFilter.value) return false;
      if (workFilter.value && item.criteria?.work_type !== workFilter.value) return false;
      return true;
    });
  }

  function render(items) {
    const grouped = items.reduce((acc, item) => {
      (acc[item.section] ||= []).push(item);
      return acc;
    }, {});
    sections.innerHTML = sectionOrder.filter((name) => grouped[name]?.length).map((name) => `
      <div class="lead-package-section">
        <div class="section-head lead-mini-head"><h2>${esc(name)}</h2><p>${grouped[name].length} combinations</p></div>
        <div class="lead-package-grid lead-package-grid-compact">
          ${grouped[name].map((item) => `
            <article class="lead-package-card" data-item='${esc(JSON.stringify(item))}'>
              <p class="eyebrow eyebrow-dark">${esc(item.date_filter.replaceAll('_', ' '))}</p>
              <h3>${esc(item.filter_name)}</h3>
              <div class="lead-package-count"><strong>${Number(item.lead_count).toLocaleString('en-IN')}</strong><span>matching leads</span></div>
              <p class="muted">First 10 free for first purchase · Estimated price ${money(item.price_total)}</p>
              <button type="button" class="btn-primary add-lead-cart">Add to Cart</button>
            </article>
          `).join('')}
        </div>
      </div>
    `).join('') || '<div class="lead-card"><h2>No matching lead packages yet.</h2><p class="muted">Try another city, work type, or date range.</p></div>';
  }

  async function load() {
    msg.textContent = 'Loading lead counts...';
    const res = await fetch(`/api/lead-marketplace/counts?${params()}`);
    const data = await res.json();
    allItems = data.items || [];
    hydrateFilters(allItems);
    const visible = selectedItems();
    render(visible);
    msg.textContent = `${visible.length} packages shown from ${allItems.length} available.`;
  }

  async function loadCoupons() {
    const res = await fetch('/api/lead-coupons/public');
    if (!res.ok) return;
    const data = await res.json();
    const coupons = data.coupons || [];
    document.getElementById('publicCouponStrip').innerHTML = coupons.slice(0, 3).map((coupon) => `
      <button type="button" class="lead-coupon-pill" data-code="${esc(coupon.code)}">
        <span>${esc(coupon.code)}</span>
        <strong>${esc(coupon.title)}</strong>
      </button>
    `).join('');
  }

  function rerender() {
    const visible = selectedItems();
    render(visible);
    msg.textContent = `${visible.length} packages shown from ${allItems.length} available.`;
  }

  dateFilter.addEventListener('change', () => {
    const custom = dateFilter.value === 'custom';
    start.disabled = !custom;
    end.disabled = !custom;
    load();
  });
  start.addEventListener('change', load);
  end.addEventListener('change', load);
  cityFilter.addEventListener('change', rerender);
  workFilter.addEventListener('change', rerender);

  sections.addEventListener('click', async (event) => {
    const btn = event.target.closest('.add-lead-cart');
    if (!btn) return;
    const card = btn.closest('.lead-package-card');
    const item = JSON.parse(card.dataset.item || '{}');
    btn.disabled = true;
    btn.textContent = 'Adding...';
    const res = await fetch('/api/lead-cart', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(item) });
    btn.disabled = false;
    btn.textContent = res.ok ? 'Added' : 'Add to Cart';
    msg.textContent = res.ok ? 'Package added to cart.' : 'Could not add package.';
  });
  document.getElementById('publicCouponStrip').addEventListener('click', async (event) => {
    const btn = event.target.closest('.lead-coupon-pill');
    if (!btn) return;
    await navigator.clipboard?.writeText(btn.dataset.code).catch(() => {});
    msg.textContent = `${btn.dataset.code} copied. Apply it in cart.`;
  });
  loadCoupons();
  load();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
