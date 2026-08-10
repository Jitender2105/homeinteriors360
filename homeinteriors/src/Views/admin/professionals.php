<?php
$standardOptions = $standardOptions ?? [];
$optionList = static fn(string $key): array => $standardOptions[$key] ?? [];
require __DIR__ . '/../partials/header.php';
?>
<section class="section">
  <div class="container" data-reveal>
    <h1>Professionals Manager</h1>
    <p class="muted-line">Create and manage complete professional profiles using standardized dropdown values.</p>

    <form id="professionalForm" class="admin-card" style="margin-bottom:16px;" enctype="multipart/form-data">
      <input type="hidden" name="id" />
      <input type="hidden" name="current_profile_pic" />
      <input type="hidden" name="current_cover_photo" />
      <div class="budget-grid">
        <input name="full_name" placeholder="Full Name" required />
        <input name="slug" placeholder="Slug (unique)" required />
      </div>
      <div class="budget-grid">
        <select name="role"><?php foreach ($optionList('roles') as $option): ?><option <?= $option === 'Designer' ? 'selected' : '' ?> value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
        <select name="city">
          <option value="">Select City</option>
          <?php foreach ($optionList('cities') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
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
        <select name="primary_work_type">
          <option value="">Primary Work Type</option>
          <?php foreach ($optionList('work_types') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
        <select name="primary_work_area">
          <option value="">Primary Work Area</option>
          <?php foreach ($optionList('service_regions') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
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
      <div class="budget-grid">
        <input name="founded_year" type="number" placeholder="Founded Year" />
        <input name="team_size" type="number" placeholder="Team Size" />
      </div>
      <div class="budget-grid">
        <input name="client_count" type="number" placeholder="Client / Project Count" />
        <input name="office_hours" placeholder="Office Hours" />
      </div>
      <input name="office_address" placeholder="Office Address" />
      <div class="budget-grid">
        <input name="phone" placeholder="Office Phone" />
        <input name="email" placeholder="Office Email" />
      </div>
      <div class="budget-grid">
        <input name="website_url" placeholder="Website URL" />
        <input name="google_business_url" placeholder="Google Business URL" />
      </div>
      <select name="specialization">
        <option value="">Specialization</option>
        <?php foreach ($optionList('specializations') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
      </select>
      <textarea name="service_summary" rows="2" placeholder="Service Summary"></textarea>
      <textarea name="profile_description" rows="2" placeholder="Profile Description"></textarea>
      <textarea name="bio" rows="2" placeholder="Bio"></textarea>
      <textarea name="why_work_with_me" rows="2" placeholder="Why Work With Me"></textarea>
      <label class="standard-select-label"><span>Service Areas</span><select name="service_areas" class="standard-multi-select" multiple><?php foreach ($optionList('service_regions') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Materials Used</span><select name="materials_json" class="standard-multi-select" multiple><?php foreach ($optionList('materials') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Offerings</span><select name="offerings_json" class="standard-multi-select" multiple><?php foreach ($optionList('offerings') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Design Styles</span><select name="design_styles_json" class="standard-multi-select" multiple><?php foreach ($optionList('design_styles') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Languages</span><select name="languages_json" class="standard-multi-select" multiple><?php foreach ($optionList('languages') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Certifications</span><select name="certifications_json" class="standard-multi-select" multiple><?php foreach ($optionList('certifications') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <textarea name="process_steps_json" rows="3" placeholder="Process Steps (one per line, optional details after |)"></textarea>
      <textarea name="awards_json" rows="3" placeholder="Awards / Highlights (one per line)"></textarea>
      <textarea name="faq_json" rows="3" placeholder="FAQs (Question | Answer per line)"></textarea>
      <div class="budget-grid">
        <select name="verification_status_code">
          <option value="">Verification Status</option>
          <?php foreach ($optionList('verification_statuses') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', $option))), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
        <select name="listing_tier">
          <option value="">Listing Tier</option>
          <?php foreach ($optionList('listing_tiers') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucwords(strtolower($option)), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="budget-grid">
        <label><input type="checkbox" name="accepting_leads" checked /> Accepting Leads</label>
        <label><input type="checkbox" name="is_active" checked /> Active</label>
      </div>
      <textarea name="verification_notes" rows="2" placeholder="Verification notes for internal admin use"></textarea>
      <textarea name="suspension_reason" rows="2" placeholder="Suspension / inactive reason, if any"></textarea>
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
            <th>Name</th><th>Role</th><th>City</th><th>Work Type</th><th>Work Area</th><th>Experience</th><th>Projects</th><th>Verification</th><th>Listing</th><th>Leads</th><th>Actions</th>
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
              <td><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', (string)($pro['verification_status_code'] ?? (!empty($pro['verification_status']) ? 'PROFESSIONAL_VERIFIED' : 'UNVERIFIED'))))), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(ucwords(strtolower((string)($pro['listing_tier'] ?? (!empty($pro['is_premium']) ? 'PAID' : 'FREE')))), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= !isset($pro['accepting_leads']) || (int)$pro['accepting_leads'] === 1 ? 'Open' : 'Paused' ?></td>
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
  const stringifyLines = (v) => {
    try {
      const arr = Array.isArray(v) ? v : JSON.parse(v || '[]');
      return Array.isArray(arr) ? arr.join('\n') : '';
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

  function fillForm(pro) {
    for (const [k, val] of Object.entries(pro)) {
      const el = form.elements[k];
      if (!el) continue;
      if (el.type === 'file') continue;
      if (el.type === 'checkbox') {
        el.checked = Number(val) === 1;
      } else {
        el.value = val ?? '';
      }
    }
    setMulti('service_areas', pro.service_areas);
    setMulti('materials_json', pro.materials_json);
    setMulti('offerings_json', pro.offerings_json);
    setMulti('design_styles_json', pro.design_styles_json);
    setMulti('languages_json', pro.languages_json);
    setMulti('certifications_json', pro.certifications_json);
    form.elements.process_steps_json.value = stringifyLines(pro.process_steps_json);
    form.elements.awards_json.value = stringifyLines(pro.awards_json);
    form.elements.faq_json.value = stringifyLines(pro.faq_json);
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
    fd.set('verification_status', form.elements.verification_status_code.value === 'PROFESSIONAL_VERIFIED' ? '1' : '0');
    fd.set('is_premium', ['PAID', 'SPONSORED'].includes(form.elements.listing_tier.value) ? '1' : '0');
    fd.set('accepting_leads', form.elements.accepting_leads.checked ? '1' : '0');
    fd.set('is_active', form.elements.is_active.checked ? '1' : '0');
    fd.set('service_areas', selectedMulti('service_areas'));
    fd.set('materials_json', selectedMulti('materials_json'));
    fd.set('offerings_json', selectedMulti('offerings_json'));
    fd.set('design_styles_json', selectedMulti('design_styles_json'));
    fd.set('languages_json', selectedMulti('languages_json'));
    fd.set('certifications_json', selectedMulti('certifications_json'));
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
