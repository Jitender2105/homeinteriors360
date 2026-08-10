<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1>Designer Login Accounts</h1>
    <p class="muted-line">Map each interior designer login to one professional profile. Designers will only see leads and quotations assigned to that profile.</p>

    <form id="designerAccountForm" class="admin-card" style="margin-bottom:16px;">
      <input type="hidden" name="id" />
      <div class="budget-grid">
        <input name="username" placeholder="Username" required />
        <input name="email" type="email" placeholder="Email" />
      </div>
      <div class="budget-grid">
        <select name="pro_id" required>
          <option value="">Select professional profile</option>
          <?php foreach ($professionals as $pro): ?>
            <option value="<?= (int)$pro['id'] ?>"><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
        <input name="password" type="password" placeholder="Password (required for new account)" autocomplete="new-password" />
      </div>
      <label><input type="checkbox" name="is_active" checked /> Active</label>
      <div class="admin-links">
        <button type="submit" class="btn-primary">Save Designer Account</button>
        <button type="button" class="btn-muted" id="designerAccountReset">Reset</button>
        <a class="btn-link" href="/designer/login" target="_blank" rel="noopener">Open Designer Login</a>
      </div>
      <p id="designerAccountMsg" class="form-message"></p>
    </form>

    <div class="table-shell">
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Professional</th>
            <th>City</th>
            <th>Status</th>
            <th>Updated</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($accounts as $account): ?>
            <tr data-account='<?= htmlspecialchars(json_encode($account, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
              <td><?= htmlspecialchars((string)$account['username'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($account['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($account['professional_name'] ?? 'Not mapped'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($account['professional_city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= !empty($account['is_active']) ? 'Active' : 'Inactive' ?></td>
              <td><?= htmlspecialchars((string)($account['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><button type="button" class="btn-link edit-designer-account">Edit</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('designerAccountForm');
  const msg = document.getElementById('designerAccountMsg');
  const reset = document.getElementById('designerAccountReset');

  document.querySelectorAll('.edit-designer-account').forEach((button) => {
    button.addEventListener('click', () => {
      const account = JSON.parse(button.closest('tr').dataset.account || '{}');
      form.elements.id.value = account.id || '';
      form.elements.username.value = account.username || '';
      form.elements.email.value = account.email || '';
      form.elements.pro_id.value = account.pro_id || '';
      form.elements.password.value = '';
      form.elements.is_active.checked = Number(account.is_active) === 1;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  reset.addEventListener('click', () => {
    form.reset();
    form.elements.id.value = '';
    form.elements.is_active.checked = true;
    msg.textContent = '';
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const fd = new FormData(form);
    const id = fd.get('id');
    fd.set('is_active', form.elements.is_active.checked ? '1' : '0');
    if (id) {
      fd.set('_method', 'PUT');
    }
    const response = await fetch(id ? `/api/admin/designer-accounts/${id}` : '/api/admin/designer-accounts', {
      method: 'POST',
      credentials: 'same-origin',
      body: fd
    });
    const data = await response.json();
    msg.className = `form-message ${response.ok ? 'ok' : 'error'}`;
    msg.textContent = response.ok ? 'Designer account saved.' : (data.error || 'Save failed');
    if (response.ok) {
      setTimeout(() => location.reload(), 500);
    }
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
