<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section container">
  <h2>Interior Designer Microsites</h2>
  <div class="card" style="margin-bottom:16px;">
    <h3 style="margin-top:0;">Create Designer</h3>
    <form id="designer-form" class="grid grid-2" style="gap:10px;">
      <input name="full_name" placeholder="Full name" required>
      <input name="slug" placeholder="Slug (optional)">
      <input name="profile_title" placeholder="Profile title">
      <input name="profile_image" placeholder="Profile image URL">
      <input name="years_experience" type="number" placeholder="Years experience" value="0">
      <input name="total_projects" type="number" placeholder="Total projects" value="0">
      <textarea name="bio" placeholder="Bio" style="grid-column:1/-1"></textarea>
      <button type="submit" style="grid-column:1/-1">Create Designer</button>
      <p id="designer-msg"></p>
    </form>
  </div>
  <div class="card">
    <h3 style="margin-top:0;">Designers</h3>
    <div id="designer-list" class="grid" style="gap:10px"></div>
  </div>
</section>
<script>
(async () => {
  const list = document.getElementById('designer-list');
  const msg = document.getElementById('designer-msg');
  const form = document.getElementById('designer-form');

  async function load() {
    const res = await fetch('/api/admin/interior-designers');
    const data = await res.json();
    list.innerHTML = (data.designers || []).map(d => `
      <div class="card">
        <strong>${d.full_name}</strong><br>
        <span class="muted">/designer/${d.slug}</span>
      </div>
    `).join('');
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form).entries());
    const res = await fetch('/api/admin/interior-designers', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
    const data = await res.json();
    msg.textContent = res.ok ? 'Created successfully' : (data.error || 'Failed');
    msg.className = res.ok ? 'success' : 'error';
    if (res.ok) { form.reset(); load(); }
  });

  load();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
