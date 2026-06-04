<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section lead-shop-page" data-reveal>
  <div class="container twin-grid lead-shop-layout">
    <div>
      <p class="eyebrow eyebrow-dark">Lead Cart</p>
      <h1>Your selected lead packages</h1>
      <p class="muted">Each item uses slab pricing: first 100 leads at ₹100, next 900 at ₹80, and above 1000 at ₹60 per lead.</p>
      <div class="lead-offer-strip lead-offer-strip-inline">
        <span>First-time offer</span>
        <strong>First 10 leads free</strong>
        <p>This applies only to your first successful lead purchase. Final eligibility is checked at checkout.</p>
      </div>
      <div id="cartItems" class="lead-cart-list"></div>
    </div>
    <aside class="lead-card lead-checkout-summary">
      <h2>Cart Total</h2>
      <div class="lead-total-lines">
        <span>Subtotal <strong id="cartSubtotal">₹0</strong></span>
        <span>Coupon Discount <strong id="cartDiscount">₹0</strong></span>
      </div>
      <div class="lead-package-count"><strong id="cartGrandTotal">₹0</strong><span>grand total</span></div>
      <p class="form-message" id="firstTimeMsg"></p>
      <a class="btn-primary lead-buy-now" href="/lead-checkout">Buy Now</a>
      <div class="lead-cart-secondary-actions">
        <a class="btn-link" href="/lead-marketplace">Add More Leads</a>
        <button class="btn-muted" id="clearCart" type="button">Clear Cart</button>
      </div>
      <div class="lead-coupon-panel">
        <h3>Apply Coupon</h3>
        <form id="couponForm" class="coupon-apply-form">
          <input name="code" placeholder="Coupon code" />
          <button class="btn-link" type="submit">Apply</button>
        </form>
        <div id="cartCouponSuggestions" class="lead-public-coupons lead-cart-coupons"></div>
        <button class="btn-muted" id="removeCoupon" type="button">Remove Coupon</button>
      </div>
      <p class="form-message" id="cartMsg"></p>
    </aside>
  </div>
</section>
<script>
(() => {
  const wrap = document.getElementById('cartItems');
  const total = document.getElementById('cartGrandTotal');
  const subtotal = document.getElementById('cartSubtotal');
  const discount = document.getElementById('cartDiscount');
  const firstTimeMsg = document.getElementById('firstTimeMsg');
  const msg = document.getElementById('cartMsg');
  const couponSuggestions = document.getElementById('cartCouponSuggestions');
  const couponForm = document.getElementById('couponForm');
  const money = (value) => `₹${Number(value || 0).toLocaleString('en-IN')}`;
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  async function load() {
    const res = await fetch('/api/lead-cart');
    const data = await res.json();
    subtotal.textContent = money(data.subtotal || 0);
    discount.textContent = `-${money(data.discount_amount || 0)}`;
    total.textContent = money(data.grand_total || 0);
    firstTimeMsg.className = data.first_time_eligible ? 'form-message ok' : 'form-message';
    firstTimeMsg.textContent = data.first_time_eligible ? 'First-time 10 free leads benefit is currently applied.' : 'First-time free leads benefit is not applicable for this buyer.';
    wrap.innerHTML = (data.items || []).map((item) => `
      <article class="lead-card lead-cart-item">
        <div><p class="eyebrow eyebrow-dark">${esc(item.date_filter.replaceAll('_', ' '))}</p><h2>${esc(item.filter_name)}</h2><p class="muted">${Number(item.lead_count).toLocaleString('en-IN')} leads</p></div>
        <div class="lead-slab-lines">${(item.pricing?.lines || []).map((line) => `<span>${esc(line.label)}: ${line.count} × ₹${line.rate} = ${money(line.amount)}</span>`).join('')}</div>
        <strong>${money(item.price_total)}</strong>
        <button class="btn-link remove-cart" data-id="${esc(item.id)}" type="button">Remove</button>
      </article>
    `).join('') || '<div class="lead-card"><h2>Your cart is empty.</h2><p class="muted">Choose lead packages from the marketplace.</p></div>';
  }
  wrap.addEventListener('click', async (event) => {
    const btn = event.target.closest('.remove-cart');
    if (!btn) return;
    await fetch(`/api/lead-cart?id=${encodeURIComponent(btn.dataset.id)}`, { method: 'DELETE' });
    load();
  });
  document.getElementById('clearCart').addEventListener('click', async () => {
    await fetch('/api/lead-cart?id=all', { method: 'DELETE' });
    msg.textContent = 'Cart cleared.';
    load();
  });
  async function applyCoupon(code) {
    const res = await fetch('/api/lead-cart/coupon', { method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({code}) });
    const data = await res.json();
    msg.className = res.ok ? 'form-message ok' : 'form-message error';
    msg.textContent = res.ok ? `Coupon ${data.coupon.code} applied.` : (data.error || 'Coupon failed.');
    load();
  }
  couponForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const code = new FormData(event.currentTarget).get('code');
    applyCoupon(code);
  });
  couponSuggestions.addEventListener('click', (event) => {
    const btn = event.target.closest('.lead-coupon-pill');
    if (!btn) return;
    couponForm.elements.code.value = btn.dataset.code || '';
    applyCoupon(btn.dataset.code || '');
  });
  document.getElementById('removeCoupon').addEventListener('click', async () => {
    await fetch('/api/lead-cart/coupon', { method: 'DELETE' });
    msg.textContent = 'Coupon removed.';
    load();
  });
  async function loadCoupons() {
    const res = await fetch('/api/lead-coupons/public');
    if (!res.ok) return;
    const data = await res.json();
    const coupons = data.coupons || [];
    couponSuggestions.innerHTML = coupons.slice(0, 4).map((coupon) => `
      <button type="button" class="lead-coupon-pill" data-code="${esc(coupon.code)}">
        <span>${esc(coupon.code)}</span>
        <strong>${esc(coupon.title)}</strong>
      </button>
    `).join('');
  }
  loadCoupons();
  load();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
