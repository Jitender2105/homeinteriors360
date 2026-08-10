<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$quote = $quote ?? [];
$items = $quote['items'] ?? [];
if (!$items) {
  $items = [[
    'room_name' => 'Modular kitchen',
    'category' => 'Modular kitchen',
    'item_name' => 'Base and wall cabinets',
    'unit_type' => 'Per running ft',
    'quantity' => 10,
    'rate' => 18500,
    'gst_percentage' => 18,
    'include_in_proposal' => 1,
  ]];
}
$settings = $settings ?? [];
$selectedPackage = (string)($quote['package_id'] ?? '');
$selectedTemplate = (string)($quote['template_id'] ?? '');
$selectedDesigner = (string)($quote['designer_id'] ?? '');
$portalBase = $portalBase ?? '/admin';
$isDesignerPortal = !empty($isDesignerPortal);
$field = static fn(string $key, mixed $fallback = ''): string => htmlspecialchars((string)($quote[$key] ?? $fallback), ENT_QUOTES, 'UTF-8');
?>
<section class="section quotation-admin">
  <div class="container" data-reveal>
    <div class="admin-page-head">
      <div>
        <p class="eyebrow">Quotation Builder</p>
        <h1><?= !empty($quote['id']) ? 'Edit Quotation' : 'Create Quotation' ?></h1>
        <p class="muted-line">Build room-wise scope, pricing, taxes, fees, and payment schedule.</p>
      </div>
      <a class="btn-link" href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations">All Quotations</a>
    </div>
    <nav class="quotation-subnav">
      <a href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations">All Quotations</a>
      <a href="<?= htmlspecialchars($portalBase, ENT_QUOTES, 'UTF-8') ?>/quotations/create">Create Quotation</a>
      <?php if (!$isDesignerPortal): ?>
        <a href="/admin/proposal-templates">Proposal Templates</a>
        <a href="/admin/quotation-rate-card">Rate Card</a>
        <a href="/admin/quotation-packages">Package Master</a>
        <a href="/admin/quotation-settings">Settings</a>
      <?php else: ?>
        <a href="/designer/leads">My Leads</a>
      <?php endif; ?>
    </nav>

    <form id="quotationForm" class="quote-builder-form">
      <input type="hidden" name="id" value="<?= $field('id') ?>">
      <input type="hidden" name="lead_id" value="<?= $field('lead_id') ?>">
      <input type="hidden" name="items_json" id="quoteItemsJson">
      <input type="hidden" name="payment_schedule_json" id="quoteScheduleJson">
      <div class="quote-builder-main">
        <div class="quote-form-stack">
          <article class="admin-card quote-step">
            <p class="eyebrow">Step 1</p>
            <h2>Client and project details</h2>
            <div class="budget-grid"><input name="client_name" placeholder="Client name *" value="<?= $field('client_name') ?>" required><input name="client_phone" placeholder="Mobile number *" value="<?= $field('client_phone') ?>" required></div>
            <div class="budget-grid"><input name="client_email" type="email" placeholder="Email" value="<?= $field('client_email') ?>"><input name="city" placeholder="City *" value="<?= $field('city', $settings['default_city'] ?? 'Gurgaon') ?>" required></div>
            <div class="budget-grid"><input name="locality" placeholder="Locality" value="<?= $field('locality') ?>"><input name="society_name" placeholder="Society / project name" value="<?= $field('society_name') ?>"></div>
            <div class="budget-grid">
              <select name="property_type" required><?php foreach (['Apartment','Builder floor','Villa','Independent house','Commercial','Office','Retail'] as $option): ?><option value="<?= $option ?>" <?= $field('property_type', 'Apartment') === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select>
              <select name="bhk"><?php foreach (['1BHK','2BHK','3BHK','4BHK','5BHK','Villa','Custom'] as $option): ?><option value="<?= $option ?>" <?= $field('bhk', '3BHK') === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select>
            </div>
            <div class="budget-grid"><input name="carpet_area" type="number" step="0.01" placeholder="Carpet area sq.ft." value="<?= $field('carpet_area') ?>"><input name="builtup_area" type="number" step="0.01" placeholder="Built-up area sq.ft." value="<?= $field('builtup_area') ?>"></div>
            <div class="budget-grid">
              <select name="possession_status"><?php foreach (['Ready to move','Under construction','Renovation','Resale'] as $option): ?><option value="<?= $option ?>" <?= $field('possession_status', 'Ready to move') === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select>
              <input name="budget_range" placeholder="Budget range" value="<?= $field('budget_range') ?>">
            </div>
            <div class="budget-grid"><input name="expected_start_date" type="date" value="<?= $field('expected_start_date') ?>"><input name="expected_handover_date" type="date" value="<?= $field('expected_handover_date') ?>"></div>
            <div class="budget-grid">
              <select name="design_style"><?php foreach (['Modern','Minimal','Luxury','Scandinavian','Indian','Contemporary','Boho','Japandi','Classic'] as $option): ?><option value="<?= $option ?>" <?= $field('design_style', 'Modern') === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select>
              <select name="scope_type"><?php foreach (['Full home','Partial home','Kitchen only','Wardrobe only','Renovation','Turnkey'] as $option): ?><option value="<?= $option ?>" <?= $field('scope_type', 'Full home') === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select>
            </div>
            <?php if ($isDesignerPortal): ?>
              <input type="hidden" name="designer_id" value="<?= htmlspecialchars((string)$selectedDesigner, ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
              <select name="designer_id"><option value="">Assign designer</option><?php foreach ($professionals as $pro): ?><option value="<?= (int)$pro['id'] ?>" <?= $selectedDesigner === (string)$pro['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
            <?php endif; ?>
            <textarea name="internal_notes" rows="2" placeholder="Internal notes"><?= $field('internal_notes') ?></textarea>
          </article>

          <article class="admin-card quote-step">
            <p class="eyebrow">Step 2</p>
            <h2>Package selection</h2>
            <div class="quote-package-grid">
              <?php foreach ($packages as $index => $package): ?>
                <?php $checked = $selectedPackage === (string)$package['id'] || ($selectedPackage === '' && $index === 1); ?>
                <label class="quote-package-option">
                  <input type="radio" name="package_id" value="<?= (int)$package['id'] ?>" <?= $checked ? 'checked' : '' ?> required>
                  <strong><?= htmlspecialchars((string)$package['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <span><?= htmlspecialchars((string)$package['material_grade'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$package['warranty_years'], ENT_QUOTES, 'UTF-8') ?> year warranty</span>
                  <small><?= htmlspecialchars((string)$package['timeline_range'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$package['supervision_level'], ENT_QUOTES, 'UTF-8') ?></small>
                </label>
              <?php endforeach; ?>
            </div>
            <select name="template_id"><option value="">Proposal template</option><?php foreach ($templates as $template): ?><option value="<?= (int)$template['id'] ?>" <?= $selectedTemplate === (string)$template['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$template['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
          </article>

          <article class="admin-card quote-step">
            <div class="admin-section-title"><div><p class="eyebrow">Step 3</p><h2>Room and scope items</h2></div><button class="btn-muted" id="addQuoteItem" type="button">Add item</button></div>
            <div id="quoteItemRows" class="quote-item-rows"></div>
          </article>

          <article class="admin-card quote-step">
            <p class="eyebrow">Step 4</p>
            <h2>Fees, taxes, validity, and notes</h2>
            <div class="budget-grid"><input name="discount_amount" type="number" step="0.01" placeholder="Discount amount" value="<?= $field('discount_amount', '0') ?>"><input name="discount_percentage" type="number" step="0.01" placeholder="Discount %" value="<?= $field('discount_percentage', '0') ?>"></div>
            <div class="budget-grid"><input name="site_visit_fee" type="number" step="0.01" placeholder="Site visit fee" value="<?= $field('site_visit_fee', '0') ?>"><input name="gst_percentage" type="number" step="0.01" placeholder="GST %" value="<?= $field('gst_percentage', $settings['default_gst_percentage'] ?? 18) ?>"></div>
            <div class="budget-grid"><input name="design_fee_percentage" type="number" step="0.01" placeholder="Design fee %" value="<?= htmlspecialchars((string)($settings['default_design_fee_percentage'] ?? 3), ENT_QUOTES, 'UTF-8') ?>"><input name="project_management_fee_percentage" type="number" step="0.01" placeholder="Project management fee %" value="<?= htmlspecialchars((string)($settings['default_project_management_fee_percentage'] ?? 5), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="budget-grid"><input name="valid_until" type="date" value="<?= $field('valid_until', date('Y-m-d', strtotime('+15 days'))) ?>"><select name="status"><?php foreach (['draft','ready_for_review','sent_to_client','accepted','rejected'] as $status): ?><option value="<?= $status ?>" <?= $field('status', 'draft') === $status ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $status)) ?></option><?php endforeach; ?></select></div>
            <textarea name="client_notes" rows="3" placeholder="Client-facing proposal note"><?= $field('client_notes') ?></textarea>
            <div id="scheduleRows" class="quote-schedule-rows"></div>
            <button class="btn-muted" type="button" id="addScheduleRow">Add payment milestone</button>
          </article>
        </div>

        <aside class="quote-total-panel">
          <span>Quote total</span>
          <strong id="quoteFinal">₹0</strong>
          <dl>
            <div><dt>Subtotal</dt><dd id="quoteSubtotal">₹0</dd></div>
            <div><dt>Fees</dt><dd id="quoteFees">₹0</dd></div>
            <div><dt>GST</dt><dd id="quoteGst">₹0</dd></div>
            <div><dt>Discount</dt><dd id="quoteDiscount">₹0</dd></div>
          </dl>
          <button class="btn-primary" type="submit"><?= !empty($quote['id']) ? 'Save Quotation' : 'Create Quotation' ?></button>
          <p class="form-message" id="quoteFormMessage"></p>
        </aside>
      </div>
    </form>
  </div>
</section>
<script>
(() => {
  const existingItems = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
  const defaultSchedule = <?= json_encode($quote['payment_schedule'] ?? $settings['default_payment_schedule'] ?? [['label'=>'Booking amount','percentage'=>10],['label'=>'After design freeze','percentage'=>40],['label'=>'Before material dispatch','percentage'=>40],['label'=>'Before handover','percentage'=>10]], JSON_UNESCAPED_UNICODE) ?>;
  const rows = document.getElementById('quoteItemRows');
  const scheduleRows = document.getElementById('scheduleRows');
  const form = document.getElementById('quotationForm');
  const msg = document.getElementById('quoteFormMessage');
  const money = value => new Intl.NumberFormat('en-IN', {style:'currency', currency:'INR', maximumFractionDigits:0}).format(Number(value || 0));
  const esc = value => String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  const rooms = ['Living room','Dining area','Modular kitchen','Master bedroom','Bedroom 2','Bedroom 3','Kids room','Guest bedroom','Foyer','Balcony','Bathroom','Pooja room','Study room','Utility area','Passage','Custom room'];
  const categories = ['Modular kitchen','Wardrobe','TV unit','Bed with storage','Study table','Crockery unit','Shoe rack','Vanity','Pooja unit','False ceiling','Painting','Wallpaper','Electrical work','Lighting','Flooring','Civil work','Plumbing','Glass work','Partition','Loose furniture','Soft furnishings','Smart home','Appliances','Decor','Other custom work'];
  const units = ['Per sq ft','Per running ft','Per unit','Lump sum','Per point','Per room','Per day','Per visit'];
  function optionList(values, selected) { return values.map(v => `<option value="${esc(v)}" ${String(selected || '') === v ? 'selected' : ''}>${esc(v)}</option>`).join(''); }
  function addItem(item = {}) {
    const row = document.createElement('div');
    row.className = 'quote-item-row';
    row.innerHTML = `<select data-key="room_name">${optionList(rooms, item.room_name)}</select><select data-key="category">${optionList(categories, item.category)}</select><input data-key="item_name" placeholder="Item name" value="${esc(item.item_name)}"><select data-key="unit_type">${optionList(units, item.unit_type || 'Per unit')}</select><input data-key="quantity" type="number" step="0.01" min="0.01" placeholder="Qty" value="${esc(item.quantity || 1)}"><input data-key="length" type="number" step="0.01" placeholder="L" value="${esc(item.length || 0)}"><input data-key="width" type="number" step="0.01" placeholder="W" value="${esc(item.width || 0)}"><input data-key="height" type="number" step="0.01" placeholder="H" value="${esc(item.height || 0)}"><input data-key="rate" type="number" step="0.01" placeholder="Rate" value="${esc(item.rate || 0)}"><input data-key="vendor_cost" type="number" step="0.01" placeholder="Vendor cost" value="${esc(item.vendor_cost || 0)}"><input data-key="discount_amount" type="number" step="0.01" placeholder="Discount" value="${esc(item.discount_amount || 0)}"><input data-key="gst_percentage" type="number" step="0.01" placeholder="GST %" value="${esc(item.gst_percentage || 18)}"><label class="switch-row"><input data-key="include_in_proposal" type="checkbox" ${Number(item.include_in_proposal ?? 1) ? 'checked' : ''}> Include</label><label class="switch-row"><input data-key="is_manual_override" type="checkbox" ${Number(item.is_manual_override || 0) ? 'checked' : ''}> Manual Override</label><input data-key="description" placeholder="Description" value="${esc(item.description)}"><input data-key="notes" placeholder="Notes" value="${esc(item.notes)}"><strong class="quote-line-total">₹0</strong><button class="btn-link" type="button">Remove</button>`;
    row.querySelector('button').onclick = () => { row.remove(); recalc(); };
    row.querySelectorAll('input,select').forEach(input => input.addEventListener('input', recalc));
    rows.appendChild(row);
    recalc();
  }
  function addSchedule(item = {}) {
    const row = document.createElement('div');
    row.className = 'quote-schedule-row';
    row.innerHTML = `<input data-key="label" placeholder="Milestone" value="${esc(item.label)}"><input data-key="percentage" type="number" step="0.01" placeholder="%" value="${esc(item.percentage || 0)}"><input data-key="amount" type="number" step="0.01" placeholder="Fixed amount" value="${esc(item.amount || '')}"><button class="btn-link" type="button">Remove</button>`;
    row.querySelector('button').onclick = () => { row.remove(); recalc(); };
    row.querySelectorAll('input').forEach(input => input.addEventListener('input', recalc));
    scheduleRows.appendChild(row);
  }
  function serialize(container) {
    return [...container.children].map(row => Object.fromEntries([...row.querySelectorAll('[data-key]')].map(input => [input.dataset.key, input.type === 'checkbox' ? (input.checked ? 1 : 0) : input.value]))).filter(item => Object.values(item).some(value => value !== '' && value !== '0'));
  }
  function lineAmount(item) {
    const qty = Number(item.quantity || 0), rate = Number(item.rate || 0), disc = Number(item.discount_amount || 0);
    return Math.max(0, qty * rate - disc);
  }
  function recalc() {
    const items = serialize(rows);
    let subtotal = 0, gst = 0;
    [...rows.children].forEach((row, i) => {
      const amount = lineAmount(items[i] || {});
      subtotal += amount;
      gst += amount * Number((items[i] || {}).gst_percentage || form.elements.gst_percentage.value || 18) / 100;
      row.querySelector('.quote-line-total').textContent = money(amount);
    });
    const fees = subtotal * (Number(form.elements.design_fee_percentage.value || 0) + Number(form.elements.project_management_fee_percentage.value || 0)) / 100 + Number(form.elements.site_visit_fee.value || 0);
    const discount = Number(form.elements.discount_amount.value || 0) + subtotal * Number(form.elements.discount_percentage.value || 0) / 100;
    const final = Math.max(0, subtotal + fees + gst - discount);
    document.getElementById('quoteSubtotal').textContent = money(subtotal);
    document.getElementById('quoteFees').textContent = money(fees);
    document.getElementById('quoteGst').textContent = money(gst);
    document.getElementById('quoteDiscount').textContent = money(discount);
    document.getElementById('quoteFinal').textContent = money(final);
  }
  existingItems.forEach(addItem);
  defaultSchedule.forEach(addSchedule);
  document.getElementById('addQuoteItem').onclick = () => addItem({room_name:'Living room', category:'TV unit', item_name:'Custom item', quantity:1, unit_type:'Per unit', gst_percentage:18, include_in_proposal:1});
  document.getElementById('addScheduleRow').onclick = () => addSchedule({label:'Milestone', percentage:0});
  form.querySelectorAll('input,select,textarea').forEach(input => input.addEventListener('input', recalc));
  form.addEventListener('submit', async event => {
    event.preventDefault();
    document.getElementById('quoteItemsJson').value = JSON.stringify(serialize(rows));
    document.getElementById('quoteScheduleJson').value = JSON.stringify(serialize(scheduleRows));
    const fd = new FormData(form);
    const id = fd.get('id');
    if (id) fd.set('_method', 'PUT');
    const response = await fetch(id ? `/api/quotations/${id}` : '/api/quotations', {method:'POST', body:fd});
    const data = await response.json();
    msg.className = `form-message ${response.ok ? 'ok' : 'error'}`;
    msg.textContent = response.ok ? 'Quotation saved.' : (data.error || 'Could not save quotation.');
    if (response.ok) setTimeout(() => location.href = data.redirect_url || `/admin/quotations/${id}`, 350);
  });
  recalc();
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
