<?php require __DIR__ . '/../partials/header.php'; ?>
<section class="section">
  <div class="container" data-reveal>
    <h1><?= htmlspecialchars((string)($content['admin.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="stats-grid">
      <article class="stat-card"><span>Total Leads</span><strong><?= (int)($counts['leads'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>New Leads</span><strong><?= (int)($counts['new_leads'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>Active Professionals</span><strong><?= (int)($counts['pros'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>Verified Professionals</span><strong><?= (int)($counts['verified_pros'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>Property Projects</span><strong><?= (int)($counts['property_projects'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>New Property Enquiries</span><strong><?= (int)($counts['property_enquiries'] ?? 0) ?></strong></article>
    </div>
    <div class="admin-links">
      <a class="btn-link" href="/admin/content"><?= htmlspecialchars((string)($content['admin.content.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
      <a class="btn-link" href="/admin/leads"><?= htmlspecialchars((string)($content['admin.leads.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
      <a class="btn-link" href="/admin/quotations">Quotation Builder</a>
      <a class="btn-link" href="/admin/quotation-rate-card">Rate Card</a>
      <a class="btn-link" href="/admin/proposal-templates">Proposal Templates</a>
      <a class="btn-link" href="/admin/professionals">Professionals Backend</a>
      <a class="btn-link" href="/admin/designer-accounts">Designer Login Accounts</a>
      <a class="btn-link" href="/admin/portfolios">Portfolio Backend</a>
      <a class="btn-link" href="/admin/property-projects">Property Projects</a>
      <a class="btn-link" href="/admin/property-enquiries">Property Enquiries</a>
      <a class="btn-link" href="/admin/url-aliases">Global URL Aliases</a>
      <a class="btn-link" href="/admin/design-ideas">Design Ideas</a>
      <a class="btn-link" href="/admin/design-idea-sections">Design Idea Sections</a>
      <a class="btn-link" href="/admin/design-idea-leads">Design Quote Leads</a>
      <a class="btn-link" href="/admin/lead-coupons">Lead Coupons</a>
      <a class="btn-link" href="/admin/pros"><?= htmlspecialchars((string)($content['admin.pros.title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
      <a class="btn-link" href="/api/auth/logout">Logout</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
