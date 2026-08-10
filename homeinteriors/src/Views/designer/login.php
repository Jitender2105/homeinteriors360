<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container narrow" data-reveal>
    <form id="designerLoginForm" class="admin-card">
      <p class="eyebrow">Interior designer portal</p>
      <h1>Designer Login</h1>
      <p class="muted-line">Access only your assigned leads, quotations, and proposal generator.</p>
      <input name="username" type="email" required placeholder="Email address" />
      <input name="password" type="password" required placeholder="Password" />
      <button class="btn-primary" type="submit">Login to Designer Portal</button>
      <p class="form-message" id="designerLoginMsg"></p>
      <p class="muted-line">New interior designer? <a href="/interior-designer-registration">Register and get 10 free leads</a>.</p>
    </form>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('designerLoginForm');
  const msg = document.getElementById('designerLoginMsg');
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const response = await fetch('/api/auth/login', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(Object.fromEntries(new FormData(form).entries()))
    });
    const data = await response.json();
    if (response.ok) {
      window.location.href = (data.user && data.user.role === 'designer') ? '/designer' : '/admin';
      return;
    }
    msg.className = 'form-message error';
    msg.textContent = data.error || 'Login failed';
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
