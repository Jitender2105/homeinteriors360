<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="lead-market-firstfold" data-reveal>
  <div class="container lead-market-firstfold-grid">
    <div class="lead-market-copy">
      <div class="lead-market-intro">
        <p class="eyebrow eyebrow-dark">Lead Marketplace</p>
        <h1>Buy filtered homeowner leads.</h1>
        <p class="hero-subtitle">Choose city, budget, work type, and date range. Matching packages update instantly below.</p>
        <div class="story-badges">
          <span class="chip">City-wise leads</span>
          <span class="chip">Work-type filters</span>
          <span class="chip">Slab pricing</span>
        </div>
      </div>
      <div class="lead-market-promo">
        <div class="lead-offer-strip">
          <span>Lead pricing</span>
          <strong>Transparent slab pricing</strong>
          <p>Final eligibility, coupons, and checkout benefits are calculated after buyer login.</p>
        </div>
        <div id="publicCouponStrip" class="lead-public-coupons"></div>
      </div>
    </div>

    <div class="lead-card lead-card-flat lead-filter-card lead-market-panel">
      <div class="hero-lead-grid lead-market-controls">
        <label><span>Date Filter</span><select id="leadDateFilter"><option value="all_time" selected>All time</option><option value="today">Today</option><option value="last_7_days">Last 7 days</option><option value="last_30_days">Last 30 days</option><option value="custom">Custom date range</option></select></label>
        <label><span>City</span><select id="leadCityFilter"><option value="">All cities</option></select></label>
        <label><span>Type of Work</span><select id="leadWorkFilter"><option value="">All work types</option></select></label>
        <a class="btn-primary hero-lead-submit" href="/lead-cart">Open Cart</a>
        <label><span>Start Date</span><input id="leadStartDate" type="date" disabled></label>
        <label><span>End Date</span><input id="leadEndDate" type="date" disabled></label>
      </div>
      <div id="leadQuickFilters" class="lead-quick-filters"></div>
      <div class="lead-market-toolbar">
        <div>
          <strong id="leadVisibleCount">0 packages</strong>
          <span id="leadMarketMsg">Loading lead counts...</span>
        </div>
        <label><span>Sort</span><select id="leadSort"><option value="lead_desc">Most leads first</option><option value="price_asc">Price low to high</option><option value="price_desc">Price high to low</option></select></label>
      </div>
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
  const sortFilter = document.getElementById('leadSort');
  const quickFilters = document.getElementById('leadQuickFilters');
  const visibleCount = document.getElementById('leadVisibleCount');
  let allItems = [];
  let cartByPackageKey = new Map();
  let quantityByBaseKey = new Map();
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  const money = (value) => `₹${Number(value || 0).toLocaleString('en-IN')}`;
  const sectionOrder = ['City', 'City + Type of Work', 'Type of Work', 'City + Budget', 'City + Budget + Type', 'Society', 'City + Society', 'Society + Type of Work'];
  const stableCriteria = (criteria = {}) => Object.keys(criteria).sort().reduce((acc, key) => {
    if (criteria[key] !== undefined && criteria[key] !== null && criteria[key] !== '') acc[key] = criteria[key];
    return acc;
  }, {});
  const selectedCountFor = (item = {}) => Number(item.selected_count || item.lead_count || 0);
  const basePackageKey = (item = {}) => JSON.stringify({
    criteria: stableCriteria(item.criteria || {}),
    date_filter: item.date_filter || 'all_time',
    start_date: item.start_date || null,
    end_date: item.end_date || null,
  });
  const packageKey = (item = {}) => JSON.stringify({
    criteria: stableCriteria(item.criteria || {}),
    date_filter: item.date_filter || 'all_time',
    start_date: item.start_date || null,
    end_date: item.end_date || null,
    selected_count: selectedCountFor(item),
  });
  function priceForCount(count) {
    const first = Math.min(count, 100);
    const second = Math.min(Math.max(count - 100, 0), 900);
    const third = Math.max(count - 1000, 0);
    return (first * 100) + (second * 80) + (third * 60);
  }
  function selectedCartItem(card) {
    const item = JSON.parse(card.dataset.item || '{}');
    const input = card.querySelector('.lead-qty-input');
    const available = Math.max(1, Number(item.lead_count || 1));
    const requested = Number(input?.value || 1);
    const selected = Math.max(1, Math.min(requested, available));
    if (input && Number(input.value || 0) !== selected) input.value = selected;
    quantityByBaseKey.set(basePackageKey(item), selected);
    return { ...item, available_lead_count: available, selected_count: selected, lead_count: selected };
  }
  function syncCardQuantity(card) {
    const item = selectedCartItem(card);
    const selected = selectedCountFor(item);
    const price = priceForCount(selected);
    const selectedCount = card.querySelector('.lead-selected-count');
    const selectedPrice = card.querySelector('.lead-selected-price');
    const addBtn = card.querySelector('.add-lead-cart');
    const removeSlot = card.querySelector('.lead-remove-slot');
    const cartId = cartByPackageKey.get(packageKey(item));
    if (selectedCount) selectedCount.textContent = Number(selected).toLocaleString('en-IN');
    if (selectedPrice) selectedPrice.textContent = money(price);
    if (addBtn) {
      addBtn.disabled = Boolean(cartId);
      addBtn.textContent = cartId ? 'Added to Cart' : 'Add to Cart';
    }
    if (removeSlot) {
      removeSlot.innerHTML = cartId ? `<button type="button" class="btn-muted remove-lead-cart" data-cart-id="${esc(cartId)}">Remove from Cart</button>` : '';
    }
  }

  async function refreshCartState() {
    const res = await fetch('/api/lead-cart');
    if (!res.ok) {
      cartByPackageKey = new Map();
      return;
    }
    const data = await res.json();
    cartByPackageKey = new Map((data.items || []).map((item) => [packageKey(item), item.id]));
  }

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
    const cityCounts = items.reduce((acc, item) => {
      const city = item.criteria?.city;
      if (city) acc[city] = Math.max(acc[city] || 0, Number(item.lead_count || 0));
      return acc;
    }, {});
    quickFilters.innerHTML = [
      `<button type="button" class="lead-filter-chip ${cityFilter.value === '' ? 'active' : ''}" data-city="">All cities</button>`,
      ...Object.entries(cityCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 8)
        .map(([city, count]) => `<button type="button" class="lead-filter-chip ${cityFilter.value === city ? 'active' : ''}" data-city="${esc(city)}">${esc(city)} <span>${Number(count).toLocaleString('en-IN')}</span></button>`)
    ].join('');
  }

  function selectedItems() {
    const items = allItems.filter((item) => {
      if (cityFilter.value && item.criteria?.city !== cityFilter.value) return false;
      if (workFilter.value && item.criteria?.work_type !== workFilter.value) return false;
      return true;
    });
    return items.sort((a, b) => {
      if (sortFilter.value === 'price_asc') return Number(a.price_total || 0) - Number(b.price_total || 0);
      if (sortFilter.value === 'price_desc') return Number(b.price_total || 0) - Number(a.price_total || 0);
      const leadDelta = Number(b.lead_count || 0) - Number(a.lead_count || 0);
      if (leadDelta !== 0) return leadDelta;
      return sectionOrder.indexOf(a.section) - sectionOrder.indexOf(b.section);
    });
  }

  function render(items) {
    visibleCount.textContent = `${items.length} package${items.length === 1 ? '' : 's'}`;
    sections.innerHTML = items.length ? `
      <div class="lead-package-grid lead-package-grid-compact lead-shop-grid">
        ${items.map((item) => {
          const available = Math.max(1, Number(item.lead_count || 1));
          const defaultQty = Math.max(1, Math.min(Number(quantityByBaseKey.get(basePackageKey(item)) || available), available));
          const selectedItem = { ...item, selected_count: defaultQty, lead_count: defaultQty };
          const key = packageKey(selectedItem);
          const cartId = cartByPackageKey.get(key);
          return `
          <article class="lead-package-card lead-product-card" data-item='${esc(JSON.stringify(item))}'>
            <div class="lead-card-topline">
              <span>${esc(item.section)}</span>
              <em>${esc(item.date_filter.replaceAll('_', ' '))}</em>
            </div>
            <h3>${esc(item.filter_name)}</h3>
            <div class="lead-package-count"><strong>${Number(item.lead_count).toLocaleString('en-IN')}</strong><span>available leads</span></div>
            <label class="lead-quantity-control">
              <span>Quantity to buy</span>
              <input class="lead-qty-input" type="number" min="1" max="${available}" value="${defaultQty}" inputmode="numeric">
            </label>
            <div class="lead-price-row">
              <span>Estimated for <em class="lead-selected-count">${Number(defaultQty).toLocaleString('en-IN')}</em> leads</span>
              <strong class="lead-selected-price">${money(priceForCount(defaultQty))}</strong>
            </div>
            <p class="muted">Final checkout benefits are applied after login.</p>
            <div class="lead-card-actions">
              <button type="button" class="btn-primary add-lead-cart" ${cartId ? 'disabled' : ''}>${cartId ? 'Added to Cart' : 'Add to Cart'}</button>
              <span class="lead-remove-slot">${cartId ? `<button type="button" class="btn-muted remove-lead-cart" data-cart-id="${esc(cartId)}">Remove from Cart</button>` : ''}</span>
            </div>
          </article>
        `}).join('')}
      </div>
    ` : '<div class="lead-card"><h2>No matching lead packages yet.</h2><p class="muted">Try another city, work type, or date range.</p></div>';
  }

  async function load() {
    msg.textContent = 'Loading lead counts...';
    const res = await fetch(`/api/lead-marketplace/counts?${params()}`);
    const data = await res.json();
    allItems = data.items || [];
    await refreshCartState();
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
  sortFilter.addEventListener('change', rerender);
  quickFilters.addEventListener('click', (event) => {
    const btn = event.target.closest('.lead-filter-chip');
    if (!btn) return;
    cityFilter.value = btn.dataset.city || '';
    hydrateFilters(allItems);
    rerender();
  });

  sections.addEventListener('click', async (event) => {
    const addBtn = event.target.closest('.add-lead-cart');
    const removeBtn = event.target.closest('.remove-lead-cart');
    if (!addBtn && !removeBtn) return;
    const card = event.target.closest('.lead-package-card');
    if (addBtn) {
      const item = selectedCartItem(card);
      addBtn.disabled = true;
      addBtn.textContent = 'Adding...';
      const res = await fetch('/api/lead-cart', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(item) });
      msg.textContent = res.ok ? 'Package added to cart.' : 'Could not add package.';
      await refreshCartState();
      rerender();
    }
    if (removeBtn) {
      removeBtn.disabled = true;
      removeBtn.textContent = 'Removing...';
      const res = await fetch(`/api/lead-cart?id=${encodeURIComponent(removeBtn.dataset.cartId)}`, { method: 'DELETE' });
      msg.textContent = res.ok ? 'Package removed from cart.' : 'Could not remove package.';
      await refreshCartState();
      rerender();
    }
  });
  sections.addEventListener('input', (event) => {
    const input = event.target.closest('.lead-qty-input');
    if (!input) return;
    const card = event.target.closest('.lead-package-card');
    if (card) syncCardQuantity(card);
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
