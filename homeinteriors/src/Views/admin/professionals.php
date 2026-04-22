<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1>Professionals Manager</h1>
    <p class="muted-line">Create and manage complete professional profiles. Use comma-separated values for multi-value fields.</p>

    <form id="professionalForm" class="admin-card" style="margin-bottom:16px;" enctype="multipart/form-data">
      <input type="hidden" name="id" />
      <input type="hidden" name="current_profile_pic" />
      <input type="hidden" name="current_cover_photo" />
      <div class="budget-grid">
        <input name="full_name" placeholder="Full Name" required />
        <input name="slug" placeholder="Slug (unique)" required />
      </div>
      <div class="budget-grid">
        <select name="role"><option>Architect</option><option selected>Designer</option><option>Contractor</option></select>
        <input name="city" placeholder="City" />
      </div>
      <div class="budget-grid">
        <label class="file-field">
          <span>Profile Image Upload</span>
          <input type="file" name="profile_pic" accept="image/*" />
        </label>
        <label class="file-field">
          <span>Cover Image Upload</span>
          <input type="file" name="cover_photo" accept="image/*" />
        </label>
      </div>
      <div class="budget-grid">
        <div class="image-preview-shell"><img id="profilePicPreview" alt="Profile preview" /></div>
        <div class="image-preview-shell"><img id="coverPicPreview" alt="Cover preview" /></div>
      </div>
      <div class="budget-grid">
        <input name="primary_work_type" placeholder="Primary Work Type" />
        <input name="primary_work_area" placeholder="Primary Work Area" />
      </div>
      <div class="budget-grid">
        <input name="years_experience" type="number" placeholder="Years Experience" />
        <input name="projects_delivered" type="number" placeholder="Projects Delivered" />
      </div>
      <div class="budget-grid">
        <input name="rating" type="number" step="0.1" min="0" max="5" placeholder="Rating" />
        <input name="response_time_hours" type="number" placeholder="Response Time (hours)" />
      </div>
      <div class="budget-grid">
        <input name="starting_price" type="number" placeholder="Starting Price" />
        <input name="consultation_fee" type="number" placeholder="Consultation Fee" />
      </div>
      <div class="budget-grid">
        <input name="min_project_value" type="number" placeholder="Min Project Value" />
        <input name="max_project_value" type="number" placeholder="Max Project Value" />
      </div>
      <input name="specialization" placeholder="Specialization" />
      <textarea name="profile_description" rows="2" placeholder="Profile Description"></textarea>
      <textarea name="bio" rows="2" placeholder="Bio"></textarea>
      <textarea name="why_work_with_me" rows="2" placeholder="Why Work With Me"></textarea>
      <input name="service_areas" placeholder="Service Areas (comma separated)" />
      <input name="materials_json" placeholder="Materials Used (comma separated)" />
      <input name="offerings_json" placeholder="Offerings (comma separated)" />
      <input name="design_styles_json" placeholder="Design Styles (comma separated)" />
      <input name="languages_json" placeholder="Languages (comma separated)" />
      <input name="certifications_json" placeholder="Certifications (comma separated)" />
      <div class="budget-grid">
        <label><input type="checkbox" name="verification_status" /> Verified</label>
        <label><input type="checkbox" name="is_active" checked /> Active</label>
      </div>
      <div class="admin-links">
        <button type="submit" class="btn-primary">Save Professional</button>
        <button type="button" class="btn-muted" id="professionalReset">Reset</button>
      </div>
      <p id="professionalMsg" class="form-message"></p>
    </form>

    <div class="table-shell">
      <table>
        <thead>
          <tr>
            <th>Name</th><th>Role</th><th>City</th><th>Work Type</th><th>Work Area</th><th>Experience</th><th>Projects</th><th>Rating</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="professionalRows">
          <?php foreach ($professionals as $pro): ?>
            <tr data-prof='<?= htmlspecialchars(json_encode($pro, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
              <td><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$pro['role'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($pro['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($pro['primary_work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($pro['primary_work_area'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)($pro['years_experience'] ?? 0) ?></td>
              <td><?= (int)($pro['projects_delivered_computed'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string)($pro['rating'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <button type="button" class="btn-link edit-prof">Edit</button>
                <button type="button" class="btn-link del-prof" data-id="<?= (int)$pro['id'] ?>">Delete</button>
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
  const form = document.getElementById('professionalForm');
  const msg = document.getElementById('professionalMsg');
  const resetBtn = document.getElementById('professionalReset');
  const profilePreview = document.getElementById('profilePicPreview');
  const coverPreview = document.getElementById('coverPicPreview');

  const parseCsv = (v) => String(v || '').split(',').map(x => x.trim()).filter(Boolean);
  const stringifyCsv = (v) => {
    try {
      const arr = Array.isArray(v) ? v : JSON.parse(v || '[]');
      return Array.isArray(arr) ? arr.join(', ') : '';
    } catch { return ''; }
  };

  function fillForm(pro) {
    for (const [k, val] of Object.entries(pro)) {
      const el = form.elements[k];
      if (!el) continue;
      if (el.type === 'checkbox') {
        el.checked = Number(val) === 1;
      } else {
        el.value = val ?? '';
      }
    }
    form.elements.service_areas.value = stringifyCsv(pro.service_areas);
    form.elements.materials_json.value = stringifyCsv(pro.materials_json);
    form.elements.offerings_json.value = stringifyCsv(pro.offerings_json);
    form.elements.design_styles_json.value = stringifyCsv(pro.design_styles_json);
    form.elements.languages_json.value = stringifyCsv(pro.languages_json);
    form.elements.certifications_json.value = stringifyCsv(pro.certifications_json);
    form.elements.current_profile_pic.value = pro.profile_pic || '';
    form.elements.current_cover_photo.value = pro.cover_photo || '';
    profilePreview.src = pro.profile_pic || '';
    coverPreview.src = pro.cover_photo || '';
  }

  document.querySelectorAll('.edit-prof').forEach((btn) => {
    btn.addEventListener('click', () => {
      const tr = btn.closest('tr');
      const pro = JSON.parse(tr.dataset.prof || '{}');
      fillForm(pro);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  document.querySelectorAll('.del-prof').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete professional?')) return;
      const res = await fetch(`/api/admin/professionals/${btn.dataset.id}`, { method: 'DELETE', credentials: 'same-origin' });
      if (res.ok) location.reload();
    });
  });

  resetBtn.addEventListener('click', () => {
    form.reset();
    form.elements.current_profile_pic.value = '';
    form.elements.current_cover_photo.value = '';
    profilePreview.removeAttribute('src');
    coverPreview.removeAttribute('src');
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const id = fd.get('id');
    fd.set('verification_status', form.elements.verification_status.checked ? '1' : '0');
    fd.set('is_active', form.elements.is_active.checked ? '1' : '0');
    fd.set('service_areas', parseCsv(fd.get('service_areas')).join(', '));
    fd.set('materials_json', parseCsv(fd.get('materials_json')).join(', '));
    fd.set('offerings_json', parseCsv(fd.get('offerings_json')).join(', '));
    fd.set('design_styles_json', parseCsv(fd.get('design_styles_json')).join(', '));
    fd.set('languages_json', parseCsv(fd.get('languages_json')).join(', '));
    fd.set('certifications_json', parseCsv(fd.get('certifications_json')).join(', '));
    if (id) {
      fd.set('_method', 'PUT');
    }

    const url = id ? `/api/admin/professionals/${id}` : '/api/admin/professionals';
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
