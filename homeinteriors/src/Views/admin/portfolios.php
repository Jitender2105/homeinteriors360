<?php
$standardOptions = $standardOptions ?? [];
$optionList = static fn(string $key): array => $standardOptions[$key] ?? [];
require __DIR__ . '/../partials/header.php';
?>
<section class="section">
  <div class="container" data-reveal>
    <h1>Portfolio Manager</h1>
    <p class="muted-line">Upload complete portfolio details for each professional. Images now upload directly from the browser.</p>

    <form id="portfolioForm" class="admin-card" style="margin-bottom:16px;" enctype="multipart/form-data">
      <input type="hidden" name="id" />
      <input type="hidden" name="current_media_json" />
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
        <select name="work_type">
          <option value="">Type of Work</option>
          <?php foreach ($optionList('work_types') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
        <select name="area_of_work">
          <option value="">Area of Work</option>
          <?php foreach ($optionList('portfolio_areas') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="budget-grid">
        <input name="location" placeholder="Location" />
        <select name="bhk_type">
          <option value="">BHK Type</option>
          <?php foreach ($optionList('bhk_types') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="budget-grid">
        <input name="total_cost" type="number" placeholder="Cost" />
        <input name="year_completed" type="number" placeholder="Year Completed" />
      </div>
      <div class="budget-grid">
        <input name="timeline_months" type="number" placeholder="Timeline (Months)" />
        <input name="project_duration_label" placeholder="Duration Label (e.g. 16 weeks)" />
      </div>
      <select name="design_style">
        <option value="">Design Style</option>
        <?php foreach ($optionList('design_styles') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
      </select>
      <div class="budget-grid">
        <input name="team_size" type="number" placeholder="Team Size" />
        <input name="warranty_years" type="number" placeholder="Warranty (Years)" />
      </div>
      <label class="standard-select-label"><span>Materials</span><select name="materials_json" class="standard-multi-select" multiple><?php foreach ($optionList('materials') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="file-field">
        <span>Upload Portfolio Images</span>
        <input type="file" name="media_files[]" accept="image/*" multiple />
      </label>
      <div id="portfolioMediaPreview" class="image-preview-strip"></div>
      <input name="video_url" placeholder="Video URL (optional)" />
      <div class="budget-grid">
        <input name="testimonial_client_name" placeholder="Testimonial Customer Name" />
        <input name="testimonial_rating" type="number" min="1" max="5" placeholder="Testimonial Rating" />
      </div>
      <textarea name="testimonial_text" rows="2" placeholder="Customer Testimonial"></textarea>
      <div class="budget-grid">
        <select name="moderation_status">
          <option value="">Moderation Status</option>
          <?php foreach ($optionList('portfolio_moderation_statuses') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', $option))), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
        <select name="project_verification_status">
          <option value="">Project Verification</option>
          <?php foreach ($optionList('portfolio_verification_statuses') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', $option))), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="budget-grid">
        <label><input type="checkbox" name="is_featured" /> Featured public work</label>
      </div>
      <textarea name="moderation_notes" rows="2" placeholder="Moderation notes for admin use"></textarea>
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
            <th>Project</th><th>Professional</th><th>Type</th><th>Area</th><th>Cost</th><th>Location</th><th>Status</th><th>Featured</th><th>Actions</th>
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
              <td><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', (string)($item['moderation_status'] ?? 'APPROVED')))), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= !empty($item['is_featured']) ? 'Yes' : 'No' ?></td>
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
  const mediaPreview = document.getElementById('portfolioMediaPreview');

  const esc = (value) => String(value ?? '').replace(/[&<>\"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  const parseCsv = (v) => String(v || '').split(',').map(x => x.trim()).filter(Boolean);
  const parseArray = (v) => {
    try {
      const arr = Array.isArray(v) ? v : JSON.parse(v || '[]');
      return Array.isArray(arr) ? arr : [];
    } catch {
      return parseCsv(v);
    }
  };
  const stringifyCsv = (v) => {
    try {
      const arr = Array.isArray(v) ? v : JSON.parse(v || '[]');
      return Array.isArray(arr) ? arr.join(', ') : '';
    } catch { return ''; }
  };
  const setMulti = (name, value) => {
    const el = form.elements[name];
    if (!el) return;
    const values = new Set(parseCsv(stringifyCsv(value)));
    Array.from(el.options || []).forEach((option) => {
      option.selected = values.has(option.value);
    });
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
      window.jQuery(el).trigger('change.select2');
    }
  };
  const selectedMulti = (name) => {
    const el = form.elements[name];
    return Array.from((el && el.selectedOptions) || []).map((option) => option.value).join(', ');
  };

  function fillForm(item) {
    for (const [name, value] of Object.entries(item)) {
      const el = form.elements[name];
      if (!el) continue;
      if (el.type === 'file') continue;
      if (el.type === 'checkbox') {
        el.checked = Number(value) === 1;
        continue;
      }
      if (value !== undefined && value !== null) {
        el.value = value;
      }
    }
    form.elements.current_media_json.value = JSON.stringify(parseArray(item.media_json));
    setMulti('materials_json', item.materials_json);
    mediaPreview.innerHTML = parseArray(item.media_json).map((src) => `<img src="${esc(src)}" alt="portfolio">`).join('');
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

  resetBtn.addEventListener('click', () => {
    form.reset();
    form.elements.current_media_json.value = '';
    mediaPreview.innerHTML = '';
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const id = fd.get('id');
    fd.set('materials_json', selectedMulti('materials_json'));
    fd.set('is_featured', form.elements.is_featured.checked ? '1' : '0');
    if (id) {
      fd.set('_method', 'PUT');
    }

    const url = id ? `/api/admin/portfolios/${id}` : '/api/admin/portfolios';
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      body: fd
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
