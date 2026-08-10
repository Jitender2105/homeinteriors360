<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section quotation-admin"><div class="container">
  <div class="admin-page-head"><div><p class="eyebrow">Quotation Builder</p><h1>Rate Card</h1><p class="muted-line">Manage editable city, package, material, and category-wise rates. Showing <?= count($rateCards) ?> rates.</p></div><a class="btn-link" href="/admin/quotations">All Quotations</a></div>
  <nav class="quotation-subnav"><a href="/admin/quotations">All Quotations</a><a href="/admin/quotations/create">Create Quotation</a><a href="/admin/proposal-templates">Proposal Templates</a><a href="/admin/quotation-rate-card">Rate Card</a><a href="/admin/quotation-packages">Package Master</a><a href="/admin/quotation-settings">Settings</a></nav>
  <form class="admin-card quote-master-form" method="get" action="/admin/quotation-rate-card">
    <div class="budget-grid">
      <input name="q" value="<?= htmlspecialchars((string)($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Search item, category, material or brand">
      <input name="city" value="<?= htmlspecialchars((string)($_GET['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="City">
    </div>
    <div class="budget-grid">
      <select name="designer_id">
        <option value="">All designers and global rates</option>
        <?php foreach (($professionals ?? []) as $pro): ?>
          <option value="<?= (int)$pro['id'] ?>" <?= (string)($_GET['designer_id'] ?? '') === (string)$pro['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
      <select name="package_id">
        <option value="">All packages</option>
        <?php foreach ($packages as $package): ?>
          <option value="<?= (int)$package['id'] ?>" <?= (string)($_GET['package_id'] ?? '') === (string)$package['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$package['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="budget-grid">
      <input name="category" value="<?= htmlspecialchars((string)($_GET['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Category">
    </div>
    <div class="admin-links">
      <button class="btn-primary" type="submit">Filter Rates</button>
      <a class="btn-muted" href="/admin/quotation-rate-card">Clear Filters</a>
    </div>
  </form>
  <form id="rateForm" class="admin-card quote-master-form">
    <input type="hidden" name="id"><div class="budget-grid"><input name="city" placeholder="City *" required><select name="package_id"><option value="">Any package</option><?php foreach ($packages as $package): ?><option value="<?= (int)$package['id'] ?>"><?= htmlspecialchars((string)$package['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="budget-grid"><select name="designer_id"><option value="">Global rate for all designers</option><?php foreach (($professionals ?? []) as $pro): ?><option value="<?= (int)$pro['id'] ?>"><?= htmlspecialchars((string)$pro['full_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><input name="material_grade" placeholder="Material grade"></div>
    <div class="budget-grid"><input name="category" placeholder="Category *" required><input name="item_name" placeholder="Item name *" required></div>
    <div class="budget-grid"><input name="material" placeholder="Material"><input name="finish" placeholder="Finish"></div>
    <div class="budget-grid"><input name="brand" placeholder="Brand"><select name="unit_type"><?php foreach (['Per sq ft','Per running ft','Per unit','Lump sum','Per point','Per room','Per day','Per visit'] as $unit): ?><option value="<?= $unit ?>"><?= $unit ?></option><?php endforeach; ?></select></div>
    <div class="budget-grid"><input name="base_rate" type="number" step="0.01" placeholder="Base rate"><input name="min_rate" type="number" step="0.01" placeholder="Minimum rate"></div>
    <div class="budget-grid"><input name="max_rate" type="number" step="0.01" placeholder="Maximum rate"><input name="vendor_cost" type="number" step="0.01" placeholder="Vendor cost"></div>
    <div class="budget-grid"><input name="client_selling_rate" type="number" step="0.01" placeholder="Client selling rate"><input name="margin_percentage" type="number" step="0.01" placeholder="Margin %"></div>
    <div class="budget-grid"><input name="gst_percentage" type="number" step="0.01" placeholder="GST %"><input name="effective_from" type="date"></div>
    <div class="budget-grid"><input name="effective_to" type="date"></div>
    <label class="switch-row"><input type="checkbox" name="is_active" value="1" checked> Active</label>
    <div class="admin-links"><button class="btn-primary" type="submit">Save Rate</button><button class="btn-muted" type="button" id="rateReset">Reset</button></div><p id="rateMsg" class="form-message"></p>
  </form>
  <div class="table-shell"><table><thead><tr><th>City</th><th>Designer</th><th>Package</th><th>Category</th><th>Item</th><th>Material</th><th>Unit</th><th>Rate</th><th>Vendor</th><th>Status</th><th>Action</th></tr></thead><tbody><?php if (!$rateCards): ?><tr><td colspan="11">No rate cards found. Add a rate above or clear filters.</td></tr><?php endif; ?><?php foreach ($rateCards as $rate): ?><tr data-row='<?= htmlspecialchars(json_encode($rate), ENT_QUOTES, 'UTF-8') ?>'><td><?= htmlspecialchars((string)$rate['city'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)($rate['designer_name'] ?? 'Global'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)($rate['package_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$rate['category'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$rate['item_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$rate['material'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$rate['unit_type'], ENT_QUOTES, 'UTF-8') ?></td><td>₹<?= number_format((float)$rate['base_rate'], 0) ?></td><td>₹<?= number_format((float)$rate['vendor_cost'], 0) ?></td><td><?= !empty($rate['is_active']) ? 'Active' : 'Inactive' ?></td><td><button class="btn-link edit-master" type="button">Edit</button></td></tr><?php endforeach; ?></tbody></table></div>
</div></section>
<script>
(() => { const form=document.getElementById('rateForm'), msg=document.getElementById('rateMsg'); document.querySelectorAll('.edit-master').forEach(btn=>btn.onclick=()=>{const row=JSON.parse(btn.closest('tr').dataset.row); Object.entries(row).forEach(([k,v])=>{const i=form.elements[k]; if(!i)return; if(i.type==='checkbox')i.checked=Number(v)===1; else i.value=v??'';}); scrollTo({top:0,behavior:'smooth'});}); document.getElementById('rateReset').onclick=()=>form.reset(); form.onsubmit=async e=>{e.preventDefault(); const fd=new FormData(form); const id=fd.get('id'); if(id)fd.set('_method','PUT'); const r=await fetch(id?`/api/rate-cards/${id}`:'/api/rate-cards',{method:'POST',body:fd}); const d=await r.json(); msg.className=`form-message ${r.ok?'ok':'error'}`; msg.textContent=r.ok?'Rate saved.':(d.error||'Save failed'); if(r.ok)setTimeout(()=>location.reload(),400);}; })();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
