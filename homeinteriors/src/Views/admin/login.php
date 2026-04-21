<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container narrow" data-reveal>
    <form id="adminLoginForm" class="admin-card">
      <h1><?= htmlspecialchars((string)($content['admin.login.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
      <input name="username" required placeholder="Username" />
      <input name="password" type="password" required placeholder="Password" />
      <button class="btn-primary" type="submit">Login</button>
      <p class="form-message" id="adminLoginMsg"></p>
    </form>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('adminLoginForm');
  const msg = document.getElementById('adminLoginMsg');
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = Object.fromEntries(new FormData(form).entries());
    const response = await fetch('/api/auth/login', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await response.json();
    if (response.ok) {
      window.location.href = '/admin';
      return;
    }
    msg.className = 'form-message error';
    msg.textContent = data.error || 'Login failed';
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
