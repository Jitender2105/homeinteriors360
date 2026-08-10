<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$subscription = $subscription ?? ['is_expired' => false, 'show_warning' => false, 'message' => ''];
$createBlocked = !empty($createBlocked);
$canCreate = empty($subscription['is_expired']);
$buyer = $buyer ?? null;
$freeLeadEligible = !empty($freeLeadEligible);
$profileChecklist = $profileChecklist ?? ['completion_percent' => 0, 'missing' => [], 'profile_active' => false, 'verification_status' => 'UNVERIFIED', 'listing_tier' => 'FREE'];
?>
<section class="section quotation-admin">
  <div class="container" data-reveal>
    <div class="admin-page-head">
      <div>
        <p class="eyebrow">Interior designer portal</p>
        <h1>My Workspace</h1>
        <p class="muted-line">Your assigned leads, quotations, and proposal generator.</p>
      </div>
      <?php if ($canCreate): ?>
        <a class="btn-primary" href="/designer/quotations/create">Create Quotation</a>
      <?php else: ?>
        <a class="btn-primary" href="/pricing#designer-quotation-builder">Renew Subscription</a>
      <?php endif; ?>
    </div>
    <nav class="quotation-subnav"><a href="/designer">Dashboard</a><a href="/designer/leads">My Leads</a><a href="/designer/quotations">My Quotations</a><?php if ($canCreate): ?><a href="/designer/quotations/create">Create Quotation</a><?php endif; ?><a href="/api/auth/logout">Logout</a></nav>
    <?php if ($createBlocked || !empty($subscription['is_expired']) || !empty($subscription['show_warning'])): ?>
      <div class="admin-card subscription-alert <?= !empty($subscription['is_expired']) ? 'expired' : 'warning' ?>">
        <div>
          <strong><?= htmlspecialchars((string)($subscription['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
          <p class="muted-line"><?= !empty($subscription['is_expired']) ? 'You can continue accessing your dashboard, leads, and existing quotations. New quotation creation is paused until renewal.' : 'Renew early to keep proposal creation running without interruption.' ?></p>
        </div>
        <a class="btn-primary" href="/pricing#designer-quotation-builder"><?= !empty($subscription['is_expired']) ? 'Renew Now' : 'Upgrade / Renew' ?></a>
      </div>
    <?php endif; ?>
    <div class="stats-grid">
      <article class="stat-card"><span>Assigned leads</span><strong><?= (int)($stats['leads'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>New leads</span><strong><?= (int)($stats['new_leads'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>My quotations</span><strong><?= (int)($stats['quotations'] ?? 0) ?></strong></article>
      <article class="stat-card"><span>Accepted value</span><strong>₹<?= number_format((float)($stats['accepted_value'] ?? 0), 0) ?></strong></article>
    </div>
    <article class="admin-card designer-profile-checklist">
      <div>
        <p class="eyebrow">Profile readiness</p>
        <h2><?= (int)($profileChecklist['completion_percent'] ?? 0) ?>% complete</h2>
        <p class="muted-line">
          Status: <?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', (string)($profileChecklist['verification_status'] ?? 'UNVERIFIED')))), ENT_QUOTES, 'UTF-8') ?>.
          Listing: <?= htmlspecialchars(ucwords(strtolower((string)($profileChecklist['listing_tier'] ?? 'FREE'))), ENT_QUOTES, 'UTF-8') ?>.
          <?= !empty($profileChecklist['profile_active']) ? 'Your profile is active.' : 'Admin activation is pending.' ?>
        </p>
      </div>
      <?php if (!empty($profileChecklist['missing'])): ?>
        <div class="profile-checklist-missing">
          <strong>Pending before public approval</strong>
          <ul>
            <?php foreach ($profileChecklist['missing'] as $missing): ?>
              <li><?= htmlspecialchars((string)$missing, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php else: ?>
        <p class="form-message ok">Profile basics are complete. Admin can review and activate the listing.</p>
      <?php endif; ?>
      <div class="admin-links">
        <a class="btn-primary" href="/designer/portfolio-onboarding">Update portfolio</a>
        <a class="btn-muted" href="/professionals">View public directory</a>
      </div>
    </article>
    <div class="designer-dashboard-actions">
      <article class="admin-card designer-benefit-card">
        <p class="eyebrow">Lead benefit</p>
        <h2><?= $freeLeadEligible ? '10 free leads are ready for your account.' : 'Buy more verified homeowner leads.' ?></h2>
        <p class="muted-line"><?= $freeLeadEligible ? 'Use the lead marketplace checkout with this designer account. The first-time 10 free leads benefit is available once and will not repeat on future purchases.' : 'Your first-time free lead benefit has already been used or is not available. Paid lead packages are still available from the marketplace.' ?></p>
        <div class="admin-links">
          <a class="btn-primary" href="/lead-marketplace"><?= $freeLeadEligible ? 'Choose free leads' : 'Buy more leads' ?></a>
          <a class="btn-muted" href="/lead-dashboard">Lead dashboard</a>
        </div>
      </article>
      <article class="admin-card designer-benefit-card">
        <p class="eyebrow">Professional portfolio</p>
        <h2>Complete your portfolio for admin approval.</h2>
        <p class="muted-line">Add project photos, work type, cost, materials, timeline, and testimonial details. Your public listing appears only after admin activates your profile.</p>
        <a class="btn-primary" href="/designer/portfolio-onboarding">Upload portfolio work</a>
      </article>
      <article class="admin-card designer-benefit-card">
        <p class="eyebrow">Quotation Builder</p>
        <h2>Create branded proposal PDFs for clients.</h2>
        <p class="muted-line">Purchase or renew Quotation Builder access to create itemised quotations, PDF proposals, and WhatsApp-ready proposal links.</p>
        <a class="btn-primary" href="/pricing#designer-quotation-builder">Buy Quotation Builder</a>
      </article>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
