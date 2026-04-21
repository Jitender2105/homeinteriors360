<?php $designerId = $designerId ?? null; ?>
<form id="lead-form" class="grid" style="gap:12px;">
  <div class="grid grid-2">
    <input name="name" placeholder="Full Name *" required>
    <input name="phone" placeholder="Contact Number *" required>
    <input name="email" placeholder="Email Address">
    <select name="work_type" required>
      <option value="">Select Project Type *</option>
      <?php foreach (($workTypes ?? []) as $w): ?>
        <option value="<?= htmlspecialchars($w['option_value']) ?>"><?= htmlspecialchars($w['option_value']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="budget" required>
      <option value="">Select Budget *</option>
      <?php foreach (($budgets ?? []) as $b): ?>
        <option value="<?= htmlspecialchars($b['option_value']) ?>"><?= htmlspecialchars($b['option_value']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="locality">
      <option value="">Select Locality</option>
      <?php foreach (($localities ?? []) as $l): ?>
        <option value="<?= htmlspecialchars($l['name']) ?>"><?= htmlspecialchars($l['name']) ?> (<?= htmlspecialchars($l['area_type']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <textarea name="message" rows="3" placeholder="Tell us about your vision..."></textarea>
  <?php if ($designerId): ?><input type="hidden" name="interior_designer_id" value="<?= (int)$designerId ?>"><?php endif; ?>
  <button type="submit">Request Consultation</button>
  <p id="lead-msg" class="muted"></p>
</form>
<script>
(() => {
  const form = document.getElementById('lead-form');
  if (!form) return;
  const msg = document.getElementById('lead-msg');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());
    const res = await fetch('/api/leads', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
    const json = await res.json();
    if (res.ok) {
      msg.textContent = 'Thank you. Our team will contact you shortly.';
      msg.className = 'success';
      form.reset();
    } else {
      msg.textContent = json.error || 'Failed to submit lead';
      msg.className = 'error';
    }
  });
})();
</script>
