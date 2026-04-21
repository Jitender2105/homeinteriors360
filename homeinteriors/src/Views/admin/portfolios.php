<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1>Portfolio Manager</h1>
    <p class="muted-line">Upload complete portfolio details for each professional. Use comma-separated image URLs and materials.</p>

    <form id="portfolioForm" class="admin-card" style="margin-bottom:16px;">
      <input type="hidden" name="id" />
      <div class="budget-grid">
        <select name="pro_id" required>
          <option value="">Select Professional</option>
          <?php foreach ($professionals as $pro): ?>
            <option value="<?= (int)$pro['id'] ?>"><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
        <input name="slug" placeholder="Portfolio Slug (unique)" required />
      </div>
      <input name="project_name" placeholder="Project Name" required />
      <textarea name="project_description" rows="2" placeholder="Project Description"></textarea>
      <div class="budget-grid">
        <input name="work_type" placeholder="Type of Work" />
        <input name="area_of_work" placeholder="Area of Work" />
      </div>
      <div class="budget-grid">
        <input name="location" placeholder="Location" />
        <input name="bhk_type" placeholder="BHK Type (1BHK/2BHK/3BHK/4BHK/Villa/Commercial)" />
      </div>
      <div class="budget-grid">
        <input name="total_cost" type="number" placeholder="Cost" />
        <input name="year_completed" type="number" placeholder="Year Completed" />
      </div>
      <div class="budget-grid">
        <input name="timeline_months" type="number" placeholder="Timeline (Months)" />
        <input name="project_duration_label" placeholder="Duration Label (e.g. 16 weeks)" />
      </div>
      <input name="design_style" placeholder="Design Style" />
      <div class="budget-grid">
        <input name="team_size" type="number" placeholder="Team Size" />
        <input name="warranty_years" type="number" placeholder="Warranty (Years)" />
      </div>
      <input name="materials_json" placeholder="Materials (comma separated)" />
      <textarea name="media_json" rows="2" placeholder="Image URLs (comma separated, 4-5 recommended)"></textarea>
      <input name="video_url" placeholder="Video URL (optional)" />
      <div class="budget-grid">
        <input name="testimonial_client_name" placeholder="Testimonial Customer Name" />
        <input name="testimonial_rating" type="number" min="1" max="5" placeholder="Testimonial Rating" />
      </div>
      <textarea name="testimonial_text" rows="2" placeholder="Customer Testimonial"></textarea>
      <div class="admin-links">
        <button type="submit" class="btn-primary">Save Portfolio</button>
        <button type="button" class="btn-muted" id="portfolioReset">Reset</button>
      </div>
      <p id="portfolioMsg" class="form-message"></p>
    </form>

    <div class="table-shell">
      <table>
        <thead>
          <tr>
            <th>Project</th><th>Professional</th><th>Type</th><th>Area</th><th>Cost</th><th>Location</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($portfolios as $item): ?>
            <tr data-portfolio='<?= htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
              <td><?= htmlspecialchars((string)$item['project_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$item['pro_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($item['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($item['area_of_work'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>₹<?= number_format((float)($item['total_cost'] ?? 0), 0) ?></td>
              <td><?= htmlspecialchars((string)($item['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <button type="button" class="btn-link edit-portfolio">Edit</button>
                <button type="button" class="btn-link del-portfolio" data-id="<?= (int)$item['id'] ?>">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('portfolioForm');
  const msg = document.getElementById('portfolioMsg');
  const resetBtn = document.getElementById('portfolioReset');

  const parseCsv = (v) => String(v || '').split(',').map(x => x.trim()).filter(Boolean);
  const stringifyCsv = (v) => {
    try {
      const arr = Array.isArray(v) ? v : JSON.parse(v || '[]');
      return Array.isArray(arr) ? arr.join(', ') : '';
    } catch { return ''; }
  };

  function fillForm(item) {
    Object.keys(form.elements).forEach((name) => {
      if (!form.elements[name]) return;
      if (item[name] !== undefined && item[name] !== null) {
        form.elements[name].value = item[name];
      }
    });
    form.elements.media_json.value = stringifyCsv(item.media_json);
    form.elements.materials_json.value = stringifyCsv(item.materials_json);
  }

  document.querySelectorAll('.edit-portfolio').forEach((btn) => {
    btn.addEventListener('click', () => {
      const tr = btn.closest('tr');
      const item = JSON.parse(tr.dataset.portfolio || '{}');
      fillForm(item);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  document.querySelectorAll('.del-portfolio').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete portfolio item?')) return;
      const res = await fetch(`/api/admin/portfolios/${btn.dataset.id}`, { method: 'DELETE', credentials: 'same-origin' });
      if (res.ok) location.reload();
    });
  });

  resetBtn.addEventListener('click', () => form.reset());

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const payload = Object.fromEntries(fd.entries());
    const id = payload.id;
    payload.media_json = parseCsv(payload.media_json);
    payload.materials_json = parseCsv(payload.materials_json);

    const url = id ? `/api/admin/portfolios/${id}` : '/api/admin/portfolios';
    const method = id ? 'PUT' : 'POST';
    const res = await fetch(url, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (res.ok) {
      msg.className = 'form-message ok';
      msg.textContent = 'Saved successfully';
      setTimeout(() => location.reload(), 500);
    } else {
      msg.className = 'form-message error';
      msg.textContent = data.error || 'Save failed';
    }
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
