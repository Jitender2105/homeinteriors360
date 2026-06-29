<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <div class="admin-page-head">
      <div><p class="eyebrow">Real estate marketplace</p><h1>Property Project Manager</h1><p class="muted-line">Manage buy and rental projects, inventory, media, floor plans, prices, amenities, and SEO.</p></div>
      <a class="btn-link" href="/properties" target="_blank">View marketplace</a>
    </div>

    <form id="propertyProjectForm" class="admin-card property-admin-form" enctype="multipart/form-data">
      <input type="hidden" name="id">
      <input type="hidden" name="media_json">
      <input type="hidden" name="units_json">
      <input type="hidden" name="floor_plans_json">
      <div class="property-admin-section">
        <h2>Project identity</h2>
        <div class="budget-grid"><input name="project_name" placeholder="Project name *" required><input name="slug" placeholder="Unique URL slug *" required></div>
        <div class="budget-grid"><select name="listing_for" required><option value="buy">For sale</option><option value="rent">For rent</option><option value="both">Sale and rent</option></select><input name="property_type" placeholder="Property type, e.g. Apartment *" required></div>
        <div class="budget-grid"><input name="project_status" placeholder="Status, e.g. Ready to move"><input name="builder_name" placeholder="Builder / developer"></div>
        <div class="budget-grid"><input name="rera_number" placeholder="RERA registration"><input name="possession_date" type="date"></div>
        <textarea name="short_description" rows="2" placeholder="Short listing summary"></textarea>
        <textarea name="description" rows="5" placeholder="Full project description"></textarea>
      </div>

      <div class="property-admin-section">
        <h2>Location</h2>
        <input name="address" placeholder="Full address">
        <div class="budget-grid"><input name="locality" placeholder="Locality"><input name="city" placeholder="City *" required></div>
        <div class="budget-grid"><input name="state" placeholder="State"><input name="pincode" placeholder="PIN code"></div>
        <div class="budget-grid"><input name="latitude" type="number" step="any" placeholder="Latitude"><input name="longitude" type="number" step="any" placeholder="Longitude"></div>
      </div>

      <div class="property-admin-section">
        <h2>Pricing and project scale</h2>
        <div class="budget-grid"><input name="price_min" type="number" min="0" placeholder="Sale price from"><input name="price_max" type="number" min="0" placeholder="Sale price to"></div>
        <div class="budget-grid"><input name="rent_min" type="number" min="0" placeholder="Monthly rent from"><input name="rent_max" type="number" min="0" placeholder="Monthly rent to"></div>
        <div class="budget-grid"><input name="price_per_sqft" type="number" min="0" placeholder="Price per sq.ft."><input name="total_area_acres" type="number" min="0" step="0.01" placeholder="Project area in acres"></div>
        <div class="budget-grid"><input name="area_min" type="number" min="0" placeholder="Minimum unit area"><input name="area_max" type="number" min="0" placeholder="Maximum unit area"></div>
        <div class="budget-grid"><input name="total_units" type="number" min="0" placeholder="Total units"><input name="total_towers" type="number" min="0" placeholder="Total towers"></div>
      </div>

      <div class="property-admin-section">
        <div class="admin-section-title"><h2>Configurations and inventory</h2><button class="btn-muted" type="button" id="addUnit">Add configuration</button></div>
        <div id="unitRows" class="property-admin-rows"></div>
      </div>

      <div class="property-admin-section">
        <h2>Media and floor plans</h2>
        <label class="file-field"><span>Upload project photos</span><input type="file" name="project_images[]" accept="image/*" multiple></label>
        <div class="admin-section-title"><h3>Existing / remote media URLs</h3><button class="btn-muted" type="button" id="addMedia">Add media URL</button></div>
        <div id="mediaRows" class="property-admin-rows"></div>
        <label class="file-field"><span>Upload floor plan images</span><input type="file" name="floor_plan_files[]" accept="image/*" multiple></label>
        <div class="admin-section-title"><h3>Existing / remote floor plans</h3><button class="btn-muted" type="button" id="addFloorPlan">Add floor plan URL</button></div>
        <div id="floorPlanRows" class="property-admin-rows"></div>
        <div class="budget-grid"><input name="video_url" placeholder="YouTube/Vimeo embed URL"><input name="brochure_url" placeholder="Brochure URL"></div>
      </div>

      <div class="property-admin-section">
        <h2>Highlights and SEO</h2>
        <textarea name="amenities_json" rows="2" placeholder="Amenities, comma separated"></textarea>
        <textarea name="highlights_json" rows="2" placeholder="Project highlights, comma separated"></textarea>
        <textarea name="nearby_json" rows="2" placeholder="Nearby places and distances, comma separated"></textarea>
        <input name="meta_title" placeholder="SEO title">
        <textarea name="meta_description" rows="2" placeholder="SEO description"></textarea>
        <div class="admin-check-row"><label><input type="checkbox" name="is_featured" value="1"> Featured project</label><label><input type="checkbox" name="is_active" value="1" checked> Published</label></div>
      </div>

      <div class="admin-links"><button class="btn-primary" type="submit">Save project</button><button class="btn-muted" type="button" id="propertyProjectReset">Reset</button></div>
      <p class="form-message" id="propertyProjectMessage"></p>
    </form>

    <div class="table-shell">
      <table><thead><tr><th>Project</th><th>For</th><th>Location</th><th>Type</th><th>Price from</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody><?php foreach ($projects as $project): ?><tr>
        <td><strong><?= htmlspecialchars((string)$project['project_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small><?= htmlspecialchars((string)$project['builder_name'], ENT_QUOTES, 'UTF-8') ?></small></td>
        <td><?= htmlspecialchars(strtoupper((string)$project['listing_for']), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(trim((string)$project['locality'] . ', ' . (string)$project['city'], ', '), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string)$project['property_type'], ENT_QUOTES, 'UTF-8') ?></td>
        <td>₹<?= number_format((float)(($project['listing_for'] ?? '') === 'rent' ? $project['rent_min'] : $project['price_min']), 0) ?></td>
        <td><?= !empty($project['is_active']) ? 'Published' : 'Draft' ?></td>
        <td><button class="btn-link edit-property-project" type="button" data-id="<?= (int)$project['id'] ?>">Edit</button><button class="btn-link delete-property-project" type="button" data-id="<?= (int)$project['id'] ?>">Delete</button></td>
      </tr><?php endforeach; ?></tbody></table>
    </div>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('propertyProjectForm');
  const unitRows = document.getElementById('unitRows');
  const mediaRows = document.getElementById('mediaRows');
  const floorRows = document.getElementById('floorPlanRows');
  const message = document.getElementById('propertyProjectMessage');
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  const csv = value => Array.isArray(value) ? value.join(', ') : String(value || '');

  function unitRow(item = {}) {
    const row = document.createElement('div');
    row.className = 'property-admin-row property-unit-admin-row';
    row.innerHTML = `<input data-key="unit_name" placeholder="Unit name *" value="${esc(item.unit_name)}"><input data-key="bhk_type" placeholder="BHK" value="${esc(item.bhk_type)}"><input data-key="unit_type" placeholder="Unit type" value="${esc(item.unit_type)}"><input data-key="carpet_area" type="number" placeholder="Carpet sq.ft." value="${esc(item.carpet_area)}"><input data-key="builtup_area" type="number" placeholder="Built-up sq.ft." value="${esc(item.builtup_area)}"><input data-key="bathrooms" type="number" placeholder="Baths" value="${esc(item.bathrooms)}"><input data-key="balconies" type="number" placeholder="Balconies" value="${esc(item.balconies)}"><input data-key="furnishing" placeholder="Furnishing" value="${esc(item.furnishing)}"><input data-key="sale_price" type="number" placeholder="Sale price" value="${esc(item.sale_price)}"><input data-key="monthly_rent" type="number" placeholder="Monthly rent" value="${esc(item.monthly_rent)}"><input data-key="maintenance_amount" type="number" placeholder="Maintenance" value="${esc(item.maintenance_amount)}"><input data-key="available_units" type="number" placeholder="Available" value="${esc(item.available_units)}"><button type="button" class="icon-remove" title="Remove configuration">×</button>`;
    row.querySelector('.icon-remove').onclick = () => row.remove();
    unitRows.appendChild(row);
  }
  function mediaRow(item = {}) {
    const row = document.createElement('div');
    row.className = 'property-admin-row property-media-admin-row';
    row.innerHTML = `<input data-key="media_url" placeholder="Image or video URL" value="${esc(item.media_url)}"><select data-key="media_type"><option value="image">Image</option><option value="video" ${item.media_type === 'video' ? 'selected' : ''}>Video</option></select><input data-key="title" placeholder="Caption" value="${esc(item.title)}"><input data-key="category" placeholder="Category" value="${esc(item.category)}"><label><input data-key="is_cover" type="checkbox" ${Number(item.is_cover) ? 'checked' : ''}> Cover</label><button type="button" class="icon-remove" title="Remove media">×</button>`;
    row.querySelector('.icon-remove').onclick = () => row.remove();
    mediaRows.appendChild(row);
  }
  function floorRow(item = {}) {
    const row = document.createElement('div');
    row.className = 'property-admin-row property-floor-admin-row';
    row.innerHTML = `<input data-key="title" placeholder="Floor plan title" value="${esc(item.title)}"><input data-key="image_url" placeholder="Floor plan image URL" value="${esc(item.image_url)}"><input data-key="area_label" placeholder="Area label" value="${esc(item.area_label)}"><input data-key="price_label" placeholder="Price label" value="${esc(item.price_label)}"><button type="button" class="icon-remove" title="Remove floor plan">×</button>`;
    row.querySelector('.icon-remove').onclick = () => row.remove();
    floorRows.appendChild(row);
  }
  function serializeRows(container) {
    return [...container.querySelectorAll('.property-admin-row')].map(row => Object.fromEntries([...row.querySelectorAll('[data-key]')].map(input => [input.dataset.key, input.type === 'checkbox' ? (input.checked ? 1 : 0) : input.value]))).filter(item => Object.values(item).some(Boolean));
  }
  function reset() {
    form.reset(); form.elements.id.value = ''; unitRows.innerHTML = ''; mediaRows.innerHTML = ''; floorRows.innerHTML = ''; unitRow();
  }
  document.getElementById('addUnit').onclick = () => unitRow();
  document.getElementById('addMedia').onclick = () => mediaRow();
  document.getElementById('addFloorPlan').onclick = () => floorRow();
  document.getElementById('propertyProjectReset').onclick = reset;
  document.querySelectorAll('.edit-property-project').forEach(button => button.onclick = async () => {
    const response = await fetch(`/api/admin/property-projects/${button.dataset.id}`);
    const data = await response.json();
    if (!response.ok) return;
    const project = data.project;
    Object.entries(project).forEach(([key, value]) => {
      const input = form.elements[key];
      if (!input || ['units_json','media_json','floor_plans_json'].includes(key)) return;
      if (input.type === 'checkbox') input.checked = Number(value) === 1;
      else input.value = ['amenities_json','highlights_json','nearby_json'].includes(key) ? csv(value) : (value ?? '');
    });
    form.elements.id.value = project.id;
    unitRows.innerHTML = ''; mediaRows.innerHTML = ''; floorRows.innerHTML = '';
    (project.units || []).forEach(unitRow); (project.media || []).forEach(mediaRow); (project.floor_plans || []).forEach(floorRow);
    window.scrollTo({top: 0, behavior: 'smooth'});
  });
  document.querySelectorAll('.delete-property-project').forEach(button => button.onclick = async () => {
    if (!confirm('Delete this property project and all related inventory, media, floor plans, and enquiries?')) return;
    const response = await fetch(`/api/admin/property-projects/${button.dataset.id}`, {method:'DELETE'});
    if (response.ok) location.reload();
  });
  form.addEventListener('submit', async event => {
    event.preventDefault();
    form.elements.units_json.value = JSON.stringify(serializeRows(unitRows));
    form.elements.media_json.value = JSON.stringify(serializeRows(mediaRows));
    form.elements.floor_plans_json.value = JSON.stringify(serializeRows(floorRows));
    const fd = new FormData(form);
    const id = fd.get('id');
    if (id) fd.set('_method', 'PUT');
    const response = await fetch(id ? `/api/admin/property-projects/${id}` : '/api/admin/property-projects', {method:'POST', body:fd});
    const data = await response.json();
    message.className = `form-message ${response.ok ? 'ok' : 'error'}`;
    message.textContent = response.ok ? 'Project saved successfully.' : (data.error || 'Could not save project.');
    if (response.ok) setTimeout(() => location.reload(), 500);
  });
  reset();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
