<?php require __DIR__ . '/../partials/header.php'; ?>
<?php $value = static fn(string $key, mixed $fallback = ''): string => htmlspecialchars((string)($settings[$key] ?? $fallback), ENT_QUOTES, 'UTF-8'); ?>
<section class="section quotation-admin"><div class="container" data-reveal>
  <div class="admin-page-head"><div><p class="eyebrow">Quotation Builder</p><h1>Settings</h1><p class="muted-line">Control default GST, validity, fees, commission, support, and sharing copy.</p></div><a class="btn-link" href="/admin/quotations">All Quotations</a></div>
  <nav class="quotation-subnav"><a href="/admin/quotations">All Quotations</a><a href="/admin/quotations/create">Create Quotation</a><a href="/admin/proposal-templates">Proposal Templates</a><a href="/admin/quotation-rate-card">Rate Card</a><a href="/admin/quotation-packages">Package Master</a><a href="/admin/quotation-settings">Settings</a></nav>
  <form id="settingsForm" class="admin-card quote-master-form">
    <div class="budget-grid"><input name="default_gst_percentage" type="number" step="0.01" placeholder="Default GST %" value="<?= $value('default_gst_percentage', 18) ?>"><input name="default_proposal_validity_days" type="number" placeholder="Validity days" value="<?= $value('default_proposal_validity_days', 15) ?>"></div>
    <div class="budget-grid"><input name="default_design_fee_percentage" type="number" step="0.01" placeholder="Design fee %" value="<?= $value('default_design_fee_percentage', 3) ?>"><input name="default_project_management_fee_percentage" type="number" step="0.01" placeholder="Project management fee %" value="<?= $value('default_project_management_fee_percentage', 5) ?>"></div>
    <div class="budget-grid"><input name="default_platform_commission_percentage" type="number" step="0.01" placeholder="Platform commission %" value="<?= $value('default_platform_commission_percentage', 5) ?>"><input name="default_city" placeholder="Default city" value="<?= $value('default_city', 'Gurgaon') ?>"></div>
    <div class="budget-grid"><input name="support_phone" placeholder="Support phone" value="<?= $value('support_phone', '+91-9540573661') ?>"><input name="support_email" placeholder="Support email" value="<?= $value('support_email', 'jitender@homeinteriors360.com') ?>"></div>
    <input name="company_address" placeholder="Company address" value="<?= $value('company_address', 'Delhi NCR') ?>">
    <textarea name="default_payment_schedule" rows="3" placeholder="Payment schedule JSON"><?= htmlspecialchars(json_encode($settings['default_payment_schedule'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></textarea>
    <textarea name="default_whatsapp_message" rows="3" placeholder="WhatsApp message"><?= $value('default_whatsapp_message') ?></textarea>
    <textarea name="default_quote_terms" rows="4" placeholder="Default quote terms"><?= $value('default_quote_terms') ?></textarea>
    <button class="btn-primary" type="submit">Save Settings</button><p id="settingsMsg" class="form-message"></p>
  </form>
</div></section>
<script>
document.getElementById('settingsForm').onsubmit = async event => {
  event.preventDefault();
  const form = event.currentTarget;
  try { JSON.parse(form.elements.default_payment_schedule.value || '[]'); } catch (err) { document.getElementById('settingsMsg').textContent='Payment schedule JSON is invalid.'; document.getElementById('settingsMsg').className='form-message error'; return; }
  const response = await fetch('/api/quotation-settings', {method:'POST', body:new FormData(form)});
  const data = await response.json();
  const msg = document.getElementById('settingsMsg');
  msg.className = `form-message ${response.ok ? 'ok' : 'error'}`;
  msg.textContent = response.ok ? 'Settings saved.' : (data.error || 'Save failed');
};
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
