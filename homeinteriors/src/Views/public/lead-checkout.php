<?php
require __DIR__ . '/../partials/header.php';
$buyerLoggedIn = !empty($buyer);
?>
<section class="section lead-shop-page" data-reveal>
  <div class="container checkout-flow-shell">
    <div>
      <p class="eyebrow eyebrow-dark">Checkout</p>
      <h1>Login or create your buyer account.</h1>
      <p class="muted">First confirm your buyer account. After login/signup, your cart total and first-time eligibility will be calculated before payment.</p>
      <div class="lead-offer-strip lead-offer-strip-inline">
        <span>Launch Offer</span>
        <strong>First 10 leads free</strong>
        <p>Shown only at checkout after account verification. Final eligibility depends on buyer purchase history.</p>
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
<section id="checkoutSummarySection" class="section section-tight <?= $buyerLoggedIn ? '' : 'checkout-summary-hidden' ?>" data-reveal>
  <div class="container">
    <div class="checkout-summary-topline">
      <p class="eyebrow eyebrow-dark">Step 2</p>
      <h2>Review cart and pay</h2>
    </div>
    <div id="checkoutCart" class="lead-cart-list"></div>
  </div>
</section>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(() => {
  const form = document.getElementById('checkoutForm');
  const msg = document.getElementById('checkoutMsg');
  const cartWrap = document.getElementById('checkoutCart');
  const summarySection = document.getElementById('checkoutSummarySection');
  let buyerReady = <?= $buyerLoggedIn ? 'true' : 'false' ?>;
  const money = (value) => `₹${Number(value || 0).toLocaleString('en-IN')}`;
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  function revealSummary() {
    summarySection.classList.remove('checkout-summary-hidden');
    summarySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  async function renderCart() {
    const res = await fetch('/api/lead-cart');
    const data = await res.json();
    const offerText = data.first_time_eligible ? 'First-time 10 free leads benefit is applied.' : 'First-time free leads benefit is not applicable for this buyer.';
    cartWrap.innerHTML = `<div class="section-head"><h2>Checkout Summary</h2><p>Subtotal ${money(data.subtotal || 0)} · Discount -${money(data.discount_amount || 0)} · Grand total ${money(data.grand_total || 0)}</p><p class="form-message">${offerText}</p></div>` + ((data.items || []).map((item) => `<article class="lead-card lead-cart-item"><h2>${esc(item.filter_name)}</h2><p class="muted">${item.lead_count} leads</p><strong>${money(item.price_total)}</strong></article>`).join('') || '<div class="lead-card"><h2>Cart is empty.</h2><a class="btn-link" href="/lead-marketplace">Choose packages</a></div>');
  }
  async function ensureBuyer(buyer) {
    const res = await fetch('/api/create-order', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ buyer, account_only: true }) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Could not confirm account.');
    buyerReady = true;
    await renderCart();
    revealSummary();
    return data.buyer || buyer;
  }
  document.getElementById('buyerLoginOnly').addEventListener('click', async () => {
    const fd = Object.fromEntries(new FormData(form).entries());
    const res = await fetch('/api/buyer/login', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ phone: fd.phone, password: fd.password }) });
    const data = await res.json();
    msg.className = res.ok ? 'form-message ok' : 'form-message error';
    msg.textContent = res.ok ? 'Logged in. Review cart below, then continue to payment.' : (data.error || 'Login failed');
    if (res.ok) {
      buyerReady = true;
      await renderCart();
      revealSummary();
    }
  });
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    msg.className = 'form-message';
    msg.textContent = 'Creating secure order...';
    const buyer = Object.fromEntries(new FormData(form).entries());
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    if (!buyerReady) {
      try {
        msg.textContent = 'Confirming buyer account...';
        await ensureBuyer(buyer);
        msg.className = 'form-message ok';
        msg.textContent = 'Account confirmed. Review cart below, then click Continue to Razorpay again.';
      } catch (error) {
        msg.className = 'form-message error';
        msg.textContent = error.message || 'Could not confirm account.';
      } finally {
        submitBtn.disabled = false;
      }
      return;
    }
    const res = await fetch('/api/create-order', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ buyer }) });
    const order = await res.json();
    if (!res.ok) {
      msg.className = 'form-message error';
      msg.textContent = order.error || 'Could not create order.';
      submitBtn.disabled = false;
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
        const verify = await fetch('/api/verify-payment', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ ...response, context: 'lead_marketplace' }) });
        const verified = await verify.json();
        if (verify.ok) window.location.href = verified.redirect_url || '/lead-dashboard?payment=success';
        else { submitBtn.disabled = false; msg.className = 'form-message error'; msg.textContent = verified.error || 'Payment verification failed.'; }
      },
      modal: { ondismiss: () => { submitBtn.disabled = false; msg.textContent = 'Payment cancelled.'; } }
    });
    rzp.on('payment.failed', (response) => {
      submitBtn.disabled = false;
      msg.className = 'form-message error';
      msg.textContent = response.error?.description || 'Payment failed. Please try again.';
    });
    rzp.open();
  });
  if (buyerReady) renderCart();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
