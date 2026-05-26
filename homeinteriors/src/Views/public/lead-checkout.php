<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section lead-shop-page" data-reveal>
  <div class="container lead-band-grid">
    <div>
      <p class="eyebrow eyebrow-dark">Checkout</p>
      <h1>Login or create your buyer account.</h1>
      <p class="muted">Use your mobile number for future login. Razorpay opens for paid orders; eligible ₹0 first-time orders complete instantly.</p>
      <div class="lead-offer-strip lead-offer-strip-inline">
        <span>Launch Offer</span>
        <strong>First 10 leads free</strong>
        <p>Valid only on your first successful lead purchase. The final order total is recalculated after login/signup.</p>
      </div>
      <?php if (!$razorpayConfigured): ?><p class="form-message error">Razorpay keys are not configured on this server yet.</p><?php endif; ?>
      <?php if ($buyer): ?><p class="form-message ok">Logged in as <?= htmlspecialchars((string)$buyer['name'], ENT_QUOTES, 'UTF-8') ?>.</p><?php endif; ?>
    </div>
    <div class="lead-card lead-card-flat">
      <form id="checkoutForm" class="stack-form hero-lead-form">
        <div class="hero-lead-grid">
          <label><span>Name</span><input name="name" placeholder="Name" value="<?= htmlspecialchars((string)($buyer['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></label>
          <label><span>Email</span><input name="email" type="email" placeholder="Email" value="<?= htmlspecialchars((string)($buyer['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></label>
          <label><span>Mobile Number</span><input name="phone" placeholder="Mobile number" value="<?= htmlspecialchars((string)($buyer['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></label>
          <label><span>Password</span><input name="password" type="password" placeholder="Password" required></label>
        </div>
        <button class="btn-primary hero-lead-submit" type="submit">Continue to Razorpay</button>
        <button class="btn-link" type="button" id="buyerLoginOnly">Login Only</button>
        <p class="form-message" id="checkoutMsg"></p>
      </form>
    </div>
  </div>
</section>
<section class="section section-tight" data-reveal>
  <div class="container"><div id="checkoutCart" class="lead-cart-list"></div></div>
</section>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(() => {
  const form = document.getElementById('checkoutForm');
  const msg = document.getElementById('checkoutMsg');
  const cartWrap = document.getElementById('checkoutCart');
  const money = (value) => `₹${Number(value || 0).toLocaleString('en-IN')}`;
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  async function renderCart() {
    const res = await fetch('/api/lead-cart');
    const data = await res.json();
    const offerText = data.first_time_eligible ? 'First-time 10 free leads benefit is applied.' : 'First-time free leads benefit is not applicable for this buyer.';
    cartWrap.innerHTML = `<div class="section-head"><h2>Checkout Summary</h2><p>Subtotal ${money(data.subtotal || 0)} · Discount -${money(data.discount_amount || 0)} · Grand total ${money(data.grand_total || 0)}</p><p class="form-message">${offerText}</p></div>` + ((data.items || []).map((item) => `<article class="lead-card lead-cart-item"><h2>${esc(item.filter_name)}</h2><p class="muted">${item.lead_count} leads</p><strong>${money(item.price_total)}</strong></article>`).join('') || '<div class="lead-card"><h2>Cart is empty.</h2><a class="btn-link" href="/lead-marketplace">Choose packages</a></div>');
  }
  document.getElementById('buyerLoginOnly').addEventListener('click', async () => {
    const fd = Object.fromEntries(new FormData(form).entries());
    const res = await fetch('/api/buyer/login', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ phone: fd.phone, password: fd.password }) });
    const data = await res.json();
    msg.className = res.ok ? 'form-message ok' : 'form-message error';
    msg.textContent = res.ok ? 'Logged in. You can continue payment or open dashboard.' : (data.error || 'Login failed');
    if (res.ok) renderCart();
  });
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    msg.className = 'form-message';
    msg.textContent = 'Creating secure order...';
    const buyer = Object.fromEntries(new FormData(form).entries());
    const res = await fetch('/api/lead-orders/create', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ buyer }) });
    const order = await res.json();
    if (!res.ok) {
      msg.className = 'form-message error';
      msg.textContent = order.error || 'Could not create order.';
      return;
    }
    if (order.free_checkout) {
      msg.className = 'form-message ok';
      msg.textContent = 'Free lead package activated. Redirecting to dashboard...';
      window.location.href = order.redirect_url || '/lead-dashboard?payment=free';
      return;
    }
    const rzp = new Razorpay({
      key: order.key_id,
      amount: order.amount,
      currency: order.currency,
      name: 'HomeInteriors360',
      description: 'Filtered interior design leads',
      order_id: order.order_id,
      prefill: { name: order.buyer?.name || buyer.name, email: order.buyer?.email || buyer.email, contact: order.buyer?.phone || buyer.phone },
      handler: async (response) => {
        msg.textContent = 'Verifying payment...';
        const verify = await fetch('/api/lead-orders/verify', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(response) });
        const verified = await verify.json();
        if (verify.ok) window.location.href = verified.redirect_url || '/lead-dashboard?payment=success';
        else { msg.className = 'form-message error'; msg.textContent = verified.error || 'Payment verification failed.'; }
      },
      modal: { ondismiss: () => { msg.textContent = 'Payment cancelled.'; } }
    });
    rzp.open();
  });
  renderCart();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
