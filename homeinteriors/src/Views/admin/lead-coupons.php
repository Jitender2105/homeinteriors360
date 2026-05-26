<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1>Lead Coupon Backend</h1>
    <p class="muted-line">Create percentage or flat discounts for lead purchase slabs. Hidden coupons can be shared privately; visible coupons show on the lead marketplace frontend.</p>

    <form id="couponForm" class="admin-card" style="margin-bottom:16px;">
      <input type="hidden" name="id" />
      <div class="budget-grid">
        <input name="code" required placeholder="Coupon Code e.g. GROWTH10" />
        <input name="title" required placeholder="Frontend Title" />
      </div>
      <textarea name="description" rows="2" placeholder="Description / marketing copy"></textarea>
      <div class="budget-grid">
        <select name="discount_type"><option value="percentage">Percentage Off</option><option value="flat">Flat Off</option></select>
        <input name="discount_value" type="number" step="0.01" min="0" required placeholder="Discount Value" />
      </div>
      <div class="budget-grid">
        <input name="min_leads" type="number" min="0" placeholder="Min Leads e.g. 100" />
        <input name="max_leads" type="number" min="0" placeholder="Max Leads e.g. 1000" />
      </div>
      <div class="budget-grid">
        <input name="min_order_amount" type="number" min="0" step="0.01" placeholder="Min Order Amount" />
        <input name="max_discount_amount" type="number" min="0" step="0.01" placeholder="Max Discount Cap" />
      </div>
      <div class="budget-grid">
        <label><span>Valid From</span><input name="valid_from" type="date" /></label>
        <label><span>Valid To</span><input name="valid_to" type="date" /></label>
      </div>
      <div class="budget-grid">
        <input name="usage_limit" type="number" min="0" placeholder="Usage Limit (blank unlimited)" />
        <label><input type="checkbox" name="show_on_frontend" /> Show on frontend</label>
        <label><input type="checkbox" name="is_active" checked /> Active</label>
      </div>
      <div class="admin-links">
        <button class="btn-primary" type="submit">Save Coupon</button>
        <button class="btn-muted" type="button" id="couponReset">Reset</button>
      </div>
      <p id="couponMsg" class="form-message"></p>
    </form>

    <div class="table-shell">
      <table>
        <thead><tr><th>Code</th><th>Discount</th><th>Lead Slab</th><th>Dates</th><th>Frontend</th><th>Usage</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($coupons as $coupon): ?>
            <tr data-coupon='<?= htmlspecialchars(json_encode($coupon, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
              <td><strong><?= htmlspecialchars((string)$coupon['code'], ENT_QUOTES, 'UTF-8') ?></strong><br><?= htmlspecialchars((string)$coupon['title'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$coupon['discount_type'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string)$coupon['discount_value'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($coupon['min_leads'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string)($coupon['max_leads'] ?? '∞'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($coupon['valid_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?> to <?= htmlspecialchars((string)($coupon['valid_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= !empty($coupon['show_on_frontend']) ? 'Visible' : 'Hidden' ?></td>
              <td><?= (int)$coupon['used_count'] ?> / <?= htmlspecialchars((string)($coupon['usage_limit'] ?? '∞'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= !empty($coupon['is_active']) ? 'Active' : 'Inactive' ?></td>
              <td><button type="button" class="btn-link edit-coupon">Edit</button><button type="button" class="btn-link delete-coupon" data-id="<?= (int)$coupon['id'] ?>">Delete</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('couponForm');
  const msg = document.getElementById('couponMsg');
  const fill = (coupon) => {
    Object.entries(coupon).forEach(([key, value]) => {
      const el = form.elements[key];
      if (!el) return;
      if (el.type === 'checkbox') el.checked = Number(value) === 1;
      else el.value = value ?? '';
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
  document.querySelectorAll('.edit-coupon').forEach((btn) => btn.addEventListener('click', () => fill(JSON.parse(btn.closest('tr').dataset.coupon || '{}'))));
  document.querySelectorAll('.delete-coupon').forEach((btn) => btn.addEventListener('click', async () => {
    if (!confirm('Delete coupon?')) return;
    const res = await fetch(`/api/admin/lead-coupons/${btn.dataset.id}`, { method: 'DELETE', credentials: 'same-origin' });
    if (res.ok) location.reload();
  }));
  document.getElementById('couponReset').addEventListener('click', () => form.reset());
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = Object.fromEntries(new FormData(form).entries());
    payload.show_on_frontend = form.elements.show_on_frontend.checked ? 1 : 0;
    payload.is_active = form.elements.is_active.checked ? 1 : 0;
    const id = payload.id || '';
    const res = await fetch(id ? `/api/admin/lead-coupons/${id}` : '/api/admin/lead-coupons', {
      method: id ? 'PUT' : 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
    });
    const data = await res.json();
    msg.className = res.ok ? 'form-message ok' : 'form-message error';
    msg.textContent = res.ok ? 'Coupon saved.' : (data.error || 'Save failed');
    if (res.ok) setTimeout(() => location.reload(), 500);
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
