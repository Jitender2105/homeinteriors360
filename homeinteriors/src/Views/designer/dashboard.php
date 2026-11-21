<?php require __DIR__ . '/../partials/header.php'; ?>
<?php
$subscription = $subscription ?? ['is_expired' => false, 'show_warning' => false, 'trial_available' => true, 'message' => ''];
$createBlocked = !empty($createBlocked);
$canCreate = !empty($subscription['can_create']);
$buyer = $buyer ?? null;
$freeLeadEligible = !empty($freeLeadEligible);
$registrationLeadDelivery = $registrationLeadDelivery ?? ['assigned_count' => 0, 'is_complete' => false];
$profileChecklist = $profileChecklist ?? ['completion_percent' => 0, 'missing' => [], 'profile_active' => false, 'verification_status' => 'UNVERIFIED', 'listing_tier' => 'FREE'];
$membership = $membership ?? ['code' => 'free', 'name' => 'Free Listing', 'status' => 'free', 'billing_cycle' => 'monthly'];
$membershipPlans = is_array($membershipPlans ?? null) ? $membershipPlans : [];
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
        <button class="btn-primary quotation-locked-button" type="button" data-open-subscription-modal>Locked: Create Quotation</button>
      <?php endif; ?>
    </div>
    <section class="designer-membership-dashboard" aria-labelledby="designerMembershipTitle">
      <div class="designer-membership-dashboard-copy">
        <p class="eyebrow">YOUR MEMBERSHIP</p>
        <h2 id="designerMembershipTitle"><?= htmlspecialchars((string)$membership['name'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= ($membership['code'] ?? 'free') === 'free' ? 'Your profile is ready for approval. Upgrade when you want priority matching, monthly lead benefits, and stronger visibility.' : 'Your membership is active. Keep your profile and portfolio complete to make every enquiry count.' ?></p>
        <div class="designer-membership-metrics">
          <span><b><?= (int)($membership['lead_discount_percentage'] ?? 0) ?>%</b> lead benefit</span>
          <span><b><?= (int)($membership['bonus_lead_credits'] ?? 0) ?></b> bonus credits</span>
          <span><b><?= (int)($membership['featured_locality_slots'] ?? 0) ?></b> featured localities</span>
        </div>
      </div>
      <a class="btn-primary" href="/interior-designer-registration#designerMembership"><?= ($membership['code'] ?? 'free') === 'free' ? 'Upgrade membership' : 'Manage membership' ?></a>
    </section>
    <?php if (($membership['code'] ?? 'free') === 'free'): ?>
      <section class="designer-membership-upsell">
        <div><p class="eyebrow">GROW WITH HOMEINTERIORS360</p><h2>Get matched first. Show up stronger.</h2><p>Pro gives your team priority lead alerts and monthly bonus credits. Pro + Featured puts your listing in front of homeowners searching in your selected locality.</p></div>
        <div class="designer-membership-upsell-actions"><a class="btn-primary" href="/interior-designer-registration#designerMembership">Compare membership plans</a><span>Cancel recurring billing from Razorpay anytime.</span></div>
      </section>
    <?php endif; ?>
    <nav class="quotation-subnav"><a href="/designer">Dashboard</a><a href="/designer/profile">My Profile</a><a href="/designer/portfolio-onboarding">My Portfolio</a><a href="/designer/leads">My Leads</a><a href="/designer/alert-preferences">Alert Preferences</a><a href="/designer/quotations">My Quotations</a><?php if ($canCreate): ?><a href="/designer/quotations/create">Create Quotation</a><?php else: ?><button class="quotation-nav-lock" type="button" data-open-subscription-modal>Locked: Create Quotation</button><?php endif; ?><a href="/api/auth/logout">Logout</a></nav>
    <?php if ($createBlocked || !empty($subscription['is_expired']) || !empty($subscription['show_warning']) || !empty($subscription['trial_available'])): ?>
      <div class="admin-card subscription-alert <?= !empty($subscription['is_expired']) ? 'expired' : 'warning' ?>">
        <div>
          <strong><?= htmlspecialchars((string)($subscription['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
          <p class="muted-line"><?= !empty($subscription['is_expired']) ? 'You can continue accessing your dashboard, leads, and existing quotations. New quotation creation is locked until you authorise a ₹999/month recurring payment.' : (!empty($subscription['trial_available']) ? 'Create a quotation to unlock a 15-day free trial with a ₹2 standing-instruction authorisation. ₹999/month starts after the trial.' : 'Set up monthly payment early to keep proposal creation running without interruption.') ?></p>
        </div>
        <?php if (!empty($subscription['trial_available'])): ?>
          <button class="btn-primary" type="button" data-open-subscription-modal>Authorise and start trial</button>
        <?php else: ?>
          <button class="btn-primary" type="button" data-open-subscription-modal><?= !empty($subscription['is_expired']) ? 'Set up ₹999/month' : 'Set up monthly payment' ?></button>
        <?php endif; ?>
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
        <a class="btn-primary" href="/designer/profile">Update profile</a>
        <a class="btn-muted" href="/designer/portfolio-onboarding">Update portfolio</a>
        <a class="btn-muted" href="/professionals">View public directory</a>
      </div>
    </article>
    <div class="designer-dashboard-actions">
      <article class="admin-card designer-benefit-card">
        <p class="eyebrow">Lead benefit</p>
        <h2><?= (int)($registrationLeadDelivery['assigned_count'] ?? 0) > 0 ? (int)$registrationLeadDelivery['assigned_count'] . ' registration leads added to your dashboard.' : 'Buy more verified homeowner leads.' ?></h2>
        <p class="muted-line"><?= (int)($registrationLeadDelivery['assigned_count'] ?? 0) > 0 ? 'Your registration benefit has been automatically matched to available leads in your service city. Open My Leads to review them.' : (!empty($registrationLeadDelivery['is_complete']) ? 'Your registration lead benefit has already been delivered. Paid lead packages are available from the marketplace.' : 'We are matching your registration benefit to available leads in your city. Please check back shortly.') ?></p>
        <div class="admin-links">
          <a class="btn-primary" href="<?= (int)($registrationLeadDelivery['assigned_count'] ?? 0) > 0 ? '/designer/leads' : '/lead-marketplace' ?>"><?= (int)($registrationLeadDelivery['assigned_count'] ?? 0) > 0 ? 'View my registration leads' : 'Buy more leads' ?></a>
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
        <h2><?= !empty($subscription['is_expired']) ? 'Your Builder is locked.' : 'Create branded proposal PDFs for clients.' ?></h2>
        <p class="muted-line"><?= !empty($subscription['is_expired']) ? 'Your free 15-day access has ended. Authorise ₹999/month recurring payment to unlock new quotations while retaining access to your existing workspace.' : (!empty($subscription['trial_available']) ? 'Authorise the standing instruction today and get 15 days free. ₹999/month begins only after the trial.' : 'Create itemised quotations, PDF proposals, and WhatsApp-ready proposal links during your trial.') ?></p>
        <?php if (!empty($subscription['is_expired'])): ?>
          <button class="btn-primary" type="button" data-open-subscription-modal>Set up ₹999/month</button>
        <?php else: ?>
          <?php if (!empty($subscription['trial_available'])): ?>
            <button class="btn-primary" type="button" data-open-subscription-modal>Authorise and start trial</button>
          <?php else: ?>
            <a class="btn-primary" href="/designer/quotations/create">Create quotation</a>
          <?php endif; ?>
        <?php endif; ?>
      </article>
    </div>
  </div>
</section>
<dialog class="subscription-trial-dialog" id="subscriptionTrialDialog" aria-labelledby="subscriptionTrialTitle">
  <div class="subscription-trial-dialog-inner">
    <div class="subscription-trial-dialog-head">
      <div>
        <p class="eyebrow">BOQ/ Quotation Builder</p>
        <h2 id="subscriptionTrialTitle"><?= !empty($subscription['trial_available']) ? 'Create your first quotation free for 15 days.' : 'Restore quotation access.' ?></h2>
      </div>
      <button class="dialog-close" type="button" data-close-subscription-modal aria-label="Close">&times;</button>
    </div>
    <?php if (!empty($subscription['trial_available'])): ?>
      <div class="subscription-trial-highlight">15 DAYS FREE</div>
      <p>Authorize your standing instruction with a ₹2 payment today, then create client-ready quotations and branded PDF proposals for 15 days at no ₹999 subscription charge.</p>
      <dl class="subscription-trial-terms">
        <div><dt>Today</dt><dd>₹2 standing-instruction authorization</dd></div>
        <div><dt>After day 15</dt><dd>₹999/month automatic payment begins</dd></div>
        <div><dt>If payment fails</dt><dd>Your quotation subscription is cancelled and new quotation creation is locked.</dd></div>
      </dl>
      <button class="btn-primary" type="button" data-trial-confirm>Pay ₹2 and start free trial</button>
    <?php else: ?>
      <p>Your free trial or previous subscription has ended. Set up a ₹999/month standing instruction to unlock new quotations.</p>
      <button class="btn-primary" type="button" data-mandate-confirm>Set up ₹999/month</button>
    <?php endif; ?>
  </div>
</dialog>
<script>
(() => {
  const dialog = document.getElementById('subscriptionTrialDialog');
  const loadRazorpay = () => new Promise((resolve, reject) => {
    if (window.Razorpay) return resolve();
    const script = document.createElement('script');
    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
    script.onload = resolve;
    script.onerror = () => reject(new Error('Unable to load Razorpay checkout.'));
    document.head.appendChild(script);
  });
  const openSubscriptionCheckout = async (button, endpoint) => {
    button.disabled = true;
    try {
      const response = await fetch(endpoint, { method: 'POST' });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || 'Could not start monthly payment setup.');
      await loadRazorpay();
      const checkout = new Razorpay({
        key: data.key_id,
        subscription_id: data.subscription_id,
        name: data.name || 'HomeInteriors360',
        description: data.description || 'Quotation Builder monthly membership',
        handler: async (payment) => {
          const verify = await fetch('/api/designer/quotation-subscription/verify', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payment) });
          const result = await verify.json();
          if (!verify.ok) throw new Error(result.error || 'Payment verification failed.');
          window.location.href = result.redirect_url || '/designer/quotations/create';
        },
        modal: { ondismiss: () => { button.disabled = false; } },
      });
      checkout.on('payment.failed', (event) => { alert(event.error?.description || 'Recurring payment setup failed.'); button.disabled = false; });
      checkout.open();
    } catch (error) {
      alert(error.message || 'Could not start monthly payment setup.');
      button.disabled = false;
    }
  };
  document.querySelectorAll('[data-open-subscription-modal]').forEach((button) => button.addEventListener('click', () => dialog?.showModal()));
  document.querySelectorAll('[data-close-subscription-modal]').forEach((button) => button.addEventListener('click', () => dialog?.close()));
  document.querySelectorAll('[data-mandate-confirm]').forEach((button) => button.addEventListener('click', () => openSubscriptionCheckout(button, '/api/designer/quotation-subscription')));
  document.querySelectorAll('[data-trial-confirm]').forEach((button) => button.addEventListener('click', () => openSubscriptionCheckout(button, '/api/designer/quotation-trial')));
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
