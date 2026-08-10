<?php
$standardOptions = $standardOptions ?? [];
$optionList = static fn(string $key): array => $standardOptions[$key] ?? [];
require __DIR__ . '/../partials/header.php';
?>
<section class="section quotation-admin">
  <div class="container" data-reveal>
    <div class="admin-page-head">
      <div>
        <p class="eyebrow">Portfolio onboarding</p>
        <h1>Upload your work.</h1>
        <p class="muted-line">Add one or more completed projects. Your professional profile and portfolio will go live after admin activation.</p>
      </div>
      <a class="btn-primary" href="/designer">Go to dashboard</a>
    </div>
    <nav class="quotation-subnav"><a href="/designer">Dashboard</a><a href="/designer/portfolio-onboarding">Portfolio</a><a href="/designer/leads">My Leads</a><a href="/designer/quotations">My Quotations</a><a href="/api/auth/logout">Logout</a></nav>

    <form id="designerPortfolioForm" class="admin-card designer-portfolio-form" enctype="multipart/form-data">
      <h2>Portfolio work details</h2>
      <input name="project_name" required placeholder="Project name *">
      <textarea name="project_description" rows="2" placeholder="Project description"></textarea>
      <div class="budget-grid">
        <select name="work_type">
          <option value="">Type of work</option>
          <?php foreach ($optionList('work_types') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
        <select name="area_of_work">
          <option value="">Area of work</option>
          <?php foreach ($optionList('portfolio_areas') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="budget-grid">
        <input name="location" placeholder="Location">
        <select name="bhk_type">
          <option value="">BHK type</option>
          <?php foreach ($optionList('bhk_types') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="budget-grid">
        <input name="total_cost" type="number" min="0" placeholder="Project cost">
        <input name="year_completed" type="number" min="1990" max="<?= (int)date('Y') ?>" placeholder="Year completed">
      </div>
      <div class="budget-grid">
        <input name="timeline_months" type="number" min="0" placeholder="Timeline in months">
        <input name="project_duration_label" placeholder="Duration label">
      </div>
      <select name="design_style">
        <option value="">Design style</option>
        <?php foreach ($optionList('design_styles') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
      </select>
      <div class="budget-grid">
        <input name="team_size" type="number" min="0" placeholder="Team size">
        <input name="warranty_years" type="number" min="0" placeholder="Warranty years">
      </div>
      <label class="standard-select-label"><span>Materials</span><select name="materials_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('materials') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="file-field"><span>Upload portfolio images</span><input type="file" name="media_files[]" accept="image/*" multiple></label>
      <input name="video_url" placeholder="Video URL">
      <div class="budget-grid">
        <input name="testimonial_client_name" placeholder="Client testimonial name">
        <input name="testimonial_rating" type="number" min="1" max="5" placeholder="Testimonial rating">
      </div>
      <textarea name="testimonial_text" rows="2" placeholder="Customer testimonial"></textarea>
      <div class="admin-links">
        <button class="btn-primary" type="submit">Save portfolio work</button>
        <button class="btn-muted" type="reset">Add another</button>
      </div>
      <p id="designerPortfolioMsg" class="form-message"></p>
    </form>

    <div class="table-shell">
      <table>
        <thead><tr><th>Project</th><th>Type</th><th>Location</th><th>Cost</th><th>Status</th><th>Date</th></tr></thead>
        <tbody id="designerPortfolioRows">
          <?php foreach (($portfolios ?? []) as $item): ?>
            <tr>
              <td><?= htmlspecialchars((string)$item['project_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($item['work_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($item['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td>₹<?= number_format((float)($item['total_cost'] ?? 0), 0) ?></td>
              <td><span class="trust-status-pill"><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', (string)($item['moderation_status'] ?? 'SUBMITTED')))), ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><?= htmlspecialchars(date('d M Y', strtotime((string)$item['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($portfolios)): ?><tr><td colspan="6">No portfolio work uploaded yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('designerPortfolioForm');
  const msg = document.getElementById('designerPortfolioMsg');
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    msg.className = 'form-message';
    msg.textContent = 'Saving portfolio work...';
    const response = await fetch('/api/designer/portfolios', {
      method: 'POST',
      credentials: 'same-origin',
      body: new FormData(form)
    });
    const data = await response.json();
    msg.className = `form-message ${response.ok ? 'ok' : 'error'}`;
    msg.textContent = response.ok ? 'Portfolio saved. You can add another work.' : (data.error || 'Could not save portfolio.');
    if (response.ok) {
      setTimeout(() => location.reload(), 600);
    }
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
