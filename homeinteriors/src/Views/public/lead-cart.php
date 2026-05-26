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
        <p>The free leads are automatically included in every package price shown below.</p>
      </div>
      <div id="cartItems" class="lead-cart-list"></div>
    </div>
    <aside class="lead-card lead-checkout-summary">
      <h2>Cart Total</h2>
      <div class="lead-package-count"><strong id="cartGrandTotal">₹0</strong><span>grand total</span></div>
      <a class="btn-primary" href="/lead-checkout">Buy Now</a>
      <a class="btn-link" href="/lead-marketplace">Add More Leads</a>
      <button class="btn-muted" id="clearCart" type="button">Clear Cart</button>
      <p class="form-message" id="cartMsg"></p>
    </aside>
  </div>
</section>
<script>
(() => {
  const wrap = document.getElementById('cartItems');
  const total = document.getElementById('cartGrandTotal');
  const msg = document.getElementById('cartMsg');
  const money = (value) => `₹${Number(value || 0).toLocaleString('en-IN')}`;
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  async function load() {
    const res = await fetch('/api/lead-cart');
    const data = await res.json();
    total.textContent = money(data.grand_total || 0);
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
  load();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
