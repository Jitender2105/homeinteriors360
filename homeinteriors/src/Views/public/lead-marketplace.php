<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="studio-hero lead-shop-hero" style="--hero-bg:url('https://images.unsplash.com/photo-1600607688969-a5bfcd646154?auto=format&fit=crop&w=1500&q=85');">
  <div class="container studio-hero-inner" data-reveal>
    <p class="eyebrow">Lead Marketplace</p>
    <h1>Buy filtered homeowner leads with full context.</h1>
    <p class="hero-subtitle">Choose leads by city, society, budget, type of work, and date range. Pricing is slab-based and transparent before checkout.</p>
  </div>
</section>

<section class="section lead-band" data-reveal>
  <div class="container lead-band-grid">
    <div>
      <p class="eyebrow eyebrow-dark">Filter Inventory</p>
      <h2>Live lead counts for architects and interior designers.</h2>
      <p>Counts refresh by date range and every card can be added to your cart as a purchasable lead package.</p>
    </div>
    <div class="lead-card lead-card-flat lead-filter-card">
      <div class="hero-lead-grid">
        <label><span>Date Filter</span><select id="leadDateFilter"><option value="today">Today</option><option value="last_7_days">Last 7 days</option><option value="last_30_days" selected>Last 30 days</option><option value="custom">Custom date range</option></select></label>
        <label><span>Start Date</span><input id="leadStartDate" type="date" disabled></label>
        <label><span>End Date</span><input id="leadEndDate" type="date" disabled></label>
        <a class="btn-primary hero-lead-submit" href="/lead-cart">Open Cart</a>
      </div>
      <p class="form-message" id="leadMarketMsg"></p>
    </div>
  </div>
</section>

<section class="section" data-reveal>
  <div class="container">
    <div class="section-head section-head-wide">
      <p class="eyebrow eyebrow-dark">Available Packages</p>
      <h2>Grouped by the filters professionals actually buy.</h2>
      <p>City, society, budget, work type, and mixed combinations are generated from available lead data.</p>
    </div>
    <div id="leadSections" class="lead-market-sections"></div>
  </div>
</section>

<script>
(() => {
  const sections = document.getElementById('leadSections');
  const msg = document.getElementById('leadMarketMsg');
  const dateFilter = document.getElementById('leadDateFilter');
  const start = document.getElementById('leadStartDate');
  const end = document.getElementById('leadEndDate');
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

  function render(items) {
    const grouped = items.reduce((acc, item) => {
      (acc[item.section] ||= []).push(item);
      return acc;
    }, {});
    sections.innerHTML = sectionOrder.filter((name) => grouped[name]?.length).map((name) => `
      <div class="lead-package-section">
        <div class="section-head"><h2>${esc(name)}</h2><p>${grouped[name].length} available combinations</p></div>
        <div class="lead-package-grid">
          ${grouped[name].map((item) => `
            <article class="lead-package-card" data-item='${esc(JSON.stringify(item))}'>
              <p class="eyebrow eyebrow-dark">${esc(item.date_filter.replaceAll('_', ' '))}</p>
              <h3>${esc(item.filter_name)}</h3>
              <div class="lead-package-count"><strong>${Number(item.lead_count).toLocaleString('en-IN')}</strong><span>matching leads</span></div>
              <p class="muted">Estimated package price ${money(item.price_total)}</p>
              <button type="button" class="btn-primary add-lead-cart">Add to Cart</button>
            </article>
          `).join('')}
        </div>
      </div>
    `).join('') || '<div class="lead-card"><h2>No matching lead packages yet.</h2><p class="muted">Try another date range.</p></div>';
  }

  async function load() {
    msg.textContent = 'Loading lead counts...';
    const res = await fetch(`/api/lead-marketplace/counts?${params()}`);
    const data = await res.json();
    render(data.items || []);
    msg.textContent = `${(data.items || []).length} packages found.`;
  }

  dateFilter.addEventListener('change', () => {
    const custom = dateFilter.value === 'custom';
    start.disabled = !custom;
    end.disabled = !custom;
    load();
  });
  start.addEventListener('change', load);
  end.addEventListener('change', load);

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
  load();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
