<?php
$standardOptions = $standardOptions ?? [];
$googleIdentity = is_array($googleIdentity ?? null) ? $googleIdentity : null;
$googleConnected = !empty($googleIdentity['email']);
$googleConfigured = !empty($googleConfigured);
$optionList = static fn(string $key): array => $standardOptions[$key] ?? [];
$heroImage = 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1500&q=85';
$membershipPlans = is_array($membershipPlans ?? null) ? $membershipPlans : [];
$growthCards = [
  ['title' => 'Create a trusted profile', 'text' => 'Register your firm, logo, service cities, work type, specializations, pricing range, experience, and operating details for admin approval.', 'cta' => 'Start profile', 'href' => '#designerRegisterForm'],
  ['title' => 'Upload real portfolio work', 'text' => 'Add multiple completed projects with photos, room type, location, budget, materials, style, and project story so homeowners can judge your work clearly.', 'cta' => 'Add portfolio after signup', 'href' => '#designerRegisterForm'],
  ['title' => 'Receive 3 free leads', 'text' => 'Your dashboard receives a one-time first lead benefit after registration, helping you test the platform before buying more enquiries.', 'cta' => 'Claim free leads', 'href' => '#designerRegisterForm'],
  ['title' => 'Buy matched homeowner leads', 'text' => 'Use the lead marketplace to choose enquiries by city, society, budget, room type, requirement, recency, and available quantity.', 'cta' => 'View marketplace', 'href' => '/lead-marketplace'],
  ['title' => 'Build branded proposals', 'text' => 'Use Quotation Builder + Proposal Generator to create room-wise estimates, GST, discounts, payment milestones, PDF proposals, and WhatsApp-ready sharing.', 'cta' => 'View launch offer', 'href' => '/pricing#designer-quotation-builder'],
  ['title' => 'Stay inactive until approved', 'text' => 'Your public listing stays hidden until admin approves it, so incomplete profiles do not appear on the website.', 'cta' => 'Register safely', 'href' => '#designerRegisterForm'],
];
$helpSteps = [
  ['label' => '01', 'title' => 'Register your firm', 'text' => 'Add login, contact, city, service regions, and standard business fields in a clean step-by-step flow.'],
  ['label' => '02', 'title' => 'Complete professional proof', 'text' => 'Upload logo, cover image, service summary, certifications, materials, languages, FAQs, and process details.'],
  ['label' => '03', 'title' => 'Add portfolio projects', 'text' => 'After signup, upload more than one portfolio work with photos and project details for review.'],
  ['label' => '04', 'title' => 'Get approved and grow', 'text' => 'Once admin activates your profile, homeowners can view your listing and you can manage leads from your dashboard.'],
];
$comparisonRows = [
  ['label' => 'Profile quality', 'platform' => 'Structured fields, approved listing, logo, experience, service area and portfolio.', 'manual' => 'Scattered social posts or forwarded photos.'],
  ['label' => 'Lead access', 'platform' => '3 free leads once, then cart-based lead buying by preference.', 'manual' => 'Random enquiries without filters.'],
  ['label' => 'Sales documents', 'platform' => 'Quotation Builder with branded proposal PDFs and revision history.', 'manual' => 'Manual spreadsheets and repeated formatting.'],
  ['label' => 'Trust building', 'platform' => 'Portfolio gallery, reviews, materials, styles and project metadata.', 'manual' => 'Hard for homeowners to compare credibility.'],
];
require __DIR__ . '/../partials/header.php';
?>
<section class="designer-membership-hero" id="designerMembership">
  <div class="container">
    <p class="designer-membership-kicker">FOR INTERIOR PROFESSIONALS</p>
    <h1>Pay only for real homeowners. No sales calls. No yearly lock-in.</h1>
    <p class="designer-membership-lede">Create a trusted profile, get 3 free leads once, then choose the visibility and lead benefits that suit your firm.</p>
    <div class="designer-membership-switch" role="group" aria-label="Billing frequency">
      <button type="button" class="active" data-membership-cycle="monthly">Monthly</button>
      <button type="button" data-membership-cycle="yearly">Yearly <span>Save more</span></button>
    </div>
    <div class="designer-membership-grid">
      <?php foreach ($membershipPlans as $plan): ?>
        <article class="designer-membership-card <?= $plan['code'] === 'pro' ? 'recommended' : '' ?>" data-plan-card data-plan-code="<?= htmlspecialchars((string)$plan['code'], ENT_QUOTES, 'UTF-8') ?>" data-plan-cycle="<?= htmlspecialchars((string)$plan['billing_cycle'], ENT_QUOTES, 'UTF-8') ?>">
          <?php if ($plan['code'] === 'pro'): ?><span class="designer-membership-badge">MOST POPULAR</span><?php endif; ?>
          <p><?= htmlspecialchars((string)$plan['name'], ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= (int)$plan['price_paise'] === 0 ? 'Free' : '₹' . number_format((int)$plan['price_paise'] / 100) ?><small><?= (int)$plan['price_paise'] === 0 ? '' : '/' . ($plan['billing_cycle'] === 'yearly' ? 'yr' : 'mo') ?></small></h2>
          <span class="designer-membership-note"><?= $plan['code'] === 'free' ? 'Start with your free profile' : 'Cancel anytime from Razorpay' ?></span>
          <ul><?php foreach (($plan['features'] ?? []) as $feature): ?><li><?= htmlspecialchars((string)$feature, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
          <button type="button" class="<?= $plan['code'] === 'free' ? 'btn-muted' : 'btn-primary' ?>" data-select-membership="<?= htmlspecialchars((string)$plan['code'], ENT_QUOTES, 'UTF-8') ?>" data-select-cycle="<?= htmlspecialchars((string)$plan['billing_cycle'], ENT_QUOTES, 'UTF-8') ?>"><?= $plan['code'] === 'free' ? 'List your business free' : 'Choose ' . htmlspecialchars((string)$plan['name'], ENT_QUOTES, 'UTF-8') ?></button>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="designer-membership-reassurance">Your professional profile remains subject to HomeInteriors360 approval. Paid membership improves access and visibility; it does not guarantee project conversion.</p>
  </div>
</section>
<section class="hire-hero designer-register-hero designer-register-hero-steps" style="--hire-hero-image:url('<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>');">
  <div class="container designer-register-grid designer-register-grid-steps" data-reveal>
    <aside class="designer-register-aside">
      <p class="eyebrow">Interior designer registration</p>
      <h1>Grow your interior business on HomeInteriors360.</h1>
      <p class="hero-subtitle">Create a professional profile, upload portfolio work, unlock 3 free leads, buy matched enquiries, and build client-ready proposals from one dashboard.</p>
      <div class="designer-register-progress" aria-label="Registration progress">
        <button type="button" class="active" data-step-jump="1"><strong>01</strong><span>Firm login</span></button>
        <button type="button" data-step-jump="2"><strong>02</strong><span>Business profile</span></button>
        <button type="button" data-step-jump="3"><strong>03</strong><span>Media and details</span></button>
      </div>
      <div class="designer-offer-card">
        <span>Launch benefit</span>
        <strong>3 free leads once</strong>
        <p>After registration, your designer dashboard gets the first-time lead benefit and a Quotation Builder upgrade block.</p>
      </div>
      <div class="designer-register-benefits">
        <span>Portfolio page</span>
        <span>Designer login</span>
        <span>Admin approval</span>
        <span>Proposal tools</span>
      </div>
      <div class="designer-register-login-cta">
        <p class="muted-line">Already registered?</p>
        <a class="btn-primary" href="/designer/login">Designer Login</a>
      </div>
    </aside>

    <form id="designerRegisterForm" class="designer-register-form designer-register-step-form" enctype="multipart/form-data">
      <input type="hidden" name="role" value="Designer">
      <input type="hidden" name="pro_id" value="">
      <input type="hidden" name="source" value="<?= htmlspecialchars((string)($_GET['source'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="membership_plan" value="free">
      <input type="hidden" name="membership_cycle" value="monthly">
      <?php if ($googleConnected): ?><input type="hidden" name="google_signup" value="1"><?php endif; ?>
      <?php if (!empty($_GET['google_error'])): ?><p class="form-message error"><?= htmlspecialchars((string)$_GET['google_error'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

      <fieldset class="designer-form-step active" data-form-step="1">
        <div class="designer-step-head">
          <span>Step 1 of 3</span>
          <h2>Firm and login details</h2>
          <p>This step is saved first and creates your inactive designer profile.</p>
        </div>
        <?php if ($googleConnected): ?>
          <div class="google-signup-connected"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google"><div><strong>Google account connected</strong><small><?= htmlspecialchars((string)$googleIdentity['email'], ENT_QUOTES, 'UTF-8') ?></small></div></div>
        <?php elseif ($googleConfigured): ?>
          <a class="google-signup-button" href="/auth/google/designer<?= !empty($_GET['source']) ? '?source=' . rawurlencode((string)$_GET['source']) : '' ?>"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="">Continue with Google</a>
          <p class="google-signup-divider"><span>or register with email</span></p>
        <?php else: ?>
          <button class="google-signup-button" type="button" disabled aria-disabled="true"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="">Continue with Google</button>
          <p class="google-signup-unavailable">Google sign-up will be available once the secure Google OAuth keys are connected.</p>
        <?php endif; ?>
        <div class="budget-grid">
          <input name="full_name" required placeholder="Firm / designer name *" value="<?= htmlspecialchars((string)($googleIdentity['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          <input name="email" type="email" required placeholder="Login email *" value="<?= htmlspecialchars((string)($googleIdentity['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" <?= $googleConnected ? 'readonly' : '' ?>>
        </div>
        <div class="budget-grid">
          <input name="phone" required placeholder="Mobile number *">
          <?php if ($googleConnected): ?><div class="google-password-note">Your Google account will be used to sign in securely.</div><?php else: ?><input name="password" type="password" required minlength="8" placeholder="Create password *"><?php endif; ?>
        </div>
        <div class="budget-grid">
          <input name="office_address" placeholder="Office address / location">
          <select name="city" required>
            <option value="">City *</option>
            <?php foreach ($optionList('cities') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="designer-step-actions">
          <button class="btn-primary" type="button" data-save-step="1">Save and continue</button>
        </div>
      </fieldset>

      <fieldset class="designer-form-step" data-form-step="2" hidden>
        <div class="designer-step-head">
          <span>Step 2 of 3</span>
          <h2>Business profile</h2>
          <p>Add standardized work type, pricing, experience, and service coverage.</p>
        </div>
        <div class="budget-grid">
          <select name="primary_work_type">
            <option value="">Primary work type</option>
            <?php foreach ($optionList('work_types') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
          </select>
          <select name="specialization">
            <option value="">Specialization</option>
            <?php foreach ($optionList('specializations') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="budget-grid">
          <select name="primary_work_area">
            <option value="">State / service region</option>
            <?php foreach ($optionList('service_regions') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
          </select>
          <input name="years_experience" type="number" min="0" placeholder="Years of experience">
        </div>
        <div class="budget-grid">
          <input name="projects_delivered" type="number" min="0" placeholder="Projects delivered">
          <input name="response_time_hours" type="number" min="0" placeholder="Response time in hours">
        </div>
        <div class="budget-grid">
          <input name="starting_price" type="number" min="0" placeholder="Starting price">
          <input name="consultation_fee" type="number" min="0" placeholder="Consultation fee">
        </div>
        <div class="budget-grid">
          <input name="min_project_value" type="number" min="0" placeholder="Minimum project value">
          <input name="max_project_value" type="number" min="0" placeholder="Maximum project value">
        </div>
        <label class="standard-select-label"><span>Service areas</span><select name="service_areas[]" class="standard-multi-select" multiple><?php foreach ($optionList('service_regions') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label class="standard-select-label"><span>Offerings</span><select name="offerings_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('offerings') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <div class="designer-step-actions">
          <button class="btn-muted" type="button" data-prev-step="1">Back</button>
          <button class="btn-primary" type="button" data-save-step="2">Save and continue</button>
        </div>
      </fieldset>

      <fieldset class="designer-form-step" data-form-step="3" hidden>
        <div class="designer-step-head">
          <span>Step 3 of 3</span>
          <h2>Media and profile details</h2>
          <p>Upload brand visuals and write the profile copy that admin will review.</p>
        </div>
        <div class="budget-grid">
          <label class="file-field"><span>Firm logo / profile image</span><input type="file" name="profile_pic" accept="image/*"></label>
          <label class="file-field"><span>Cover image</span><input type="file" name="cover_photo" accept="image/*"></label>
        </div>
        <div class="budget-grid">
          <input name="founded_year" type="number" min="1900" max="<?= (int)date('Y') ?>" placeholder="Founded year">
          <input name="team_size" type="number" min="0" placeholder="Team size">
        </div>
        <div class="budget-grid">
          <input name="client_count" type="number" min="0" placeholder="Client / project count">
          <input name="office_hours" placeholder="Office hours">
        </div>
        <div class="budget-grid">
          <input name="website_url" placeholder="Website URL">
          <input name="google_business_url" placeholder="Google Business URL">
        </div>
        <textarea name="service_summary" rows="2" placeholder="Service summary"></textarea>
        <textarea name="profile_description" rows="2" placeholder="Profile description"></textarea>
        <textarea name="bio" rows="2" placeholder="Bio"></textarea>
        <textarea name="why_work_with_me" rows="2" placeholder="Why should homeowners work with you?"></textarea>
        <label class="standard-select-label"><span>Materials used</span><select name="materials_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('materials') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label class="standard-select-label"><span>Design styles</span><select name="design_styles_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('design_styles') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label class="standard-select-label"><span>Languages</span><select name="languages_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('languages') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label class="standard-select-label"><span>Certifications</span><select name="certifications_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('certifications') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <textarea name="process_steps_json" rows="3" placeholder="Process steps, one per line. Use Title | Description if needed."></textarea>
        <textarea name="awards_json" rows="3" placeholder="Awards / highlights, one per line"></textarea>
        <textarea name="faq_json" rows="3" placeholder="FAQs, one per line: Question | Answer"></textarea>
        <label class="lead-consent"><input type="checkbox" name="consent" value="1" required checked><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
        <div class="designer-step-actions">
          <button class="btn-muted" type="button" data-prev-step="2">Back</button>
          <button class="btn-primary" type="button" data-save-step="3" data-final-step="1">Finish registration</button>
        </div>
      </fieldset>

      <p id="designerRegisterMsg" class="form-message"></p>
    </form>
  </div>
</section>

<section class="section hire-budget-section designer-growth-section" data-reveal>
  <div class="container">
    <div class="section-head section-head-row">
      <div>
        <p class="eyebrow eyebrow-dark">How we help you</p>
        <h2>Everything you need to present, sell, and grow.</h2>
        <p>HomeInteriors360 gives interior designers, architects, modular kitchen firms, and turnkey contractors a cleaner way to build credibility and access relevant homeowner enquiries.</p>
      </div>
      <a class="btn-link" href="#designerRegisterForm">REGISTER NOW</a>
    </div>
    <div class="designer-growth-grid">
      <?php foreach ($growthCards as $card): ?>
        <a class="designer-growth-card" href="<?= htmlspecialchars($card['href'], ENT_QUOTES, 'UTF-8') ?>">
          <span><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></span>
          <p><?= htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8') ?></p>
          <strong><?= htmlspecialchars($card['cta'], ENT_QUOTES, 'UTF-8') ?></strong>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-tight hire-edge-section designer-help-section" data-reveal>
  <div class="container">
    <div class="section-head">
      <p class="eyebrow eyebrow-dark">Built for professionals</p>
      <h2>Not just registration. A complete sales support layer.</h2>
      <p>The platform helps you move from an unstructured enquiry to a sharper sales conversation with profile proof, lead filters, and proposal tools.</p>
    </div>
    <div class="hire-edge-table">
      <div class="hire-edge-head"><span></span><strong>HomeInteriors360</strong><strong>Manual growth</strong></div>
      <?php foreach ($comparisonRows as $row): ?>
        <div class="hire-edge-row">
          <span><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <p><?= htmlspecialchars($row['platform'], ENT_QUOTES, 'UTF-8') ?></p>
          <p><?= htmlspecialchars($row['manual'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="hire-center-cta"><a class="btn-primary" href="#designerRegisterForm">CREATE DESIGNER PROFILE</a></div>
  </div>
</section>

<section class="section designer-process-section" data-reveal>
  <div class="container">
    <div class="section-head">
      <p class="eyebrow eyebrow-dark">Onboarding flow</p>
      <h2>Go from signup to approved profile in 4 steps.</h2>
    </div>
    <div class="hire-step-grid">
      <?php foreach ($helpSteps as $step): ?>
        <article class="hire-step-card designer-process-card">
          <strong><?= htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8') ?></strong>
          <h3><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars($step['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-tight hire-custom-section designer-launch-section" data-reveal>
  <div class="container hire-custom-grid">
    <div>
      <p class="eyebrow eyebrow-dark">Launch offer</p>
      <h2>Start Proposal Builder free for 15 days.</h2>
      <p>Use the optional Quotation Builder + Proposal Generator to create branded PDF proposals with item-wise pricing, room-wise scope, GST, discounts, milestones, terms, and WhatsApp-ready sharing.</p>
      <div class="designer-trial-strip"><strong>15 DAYS FREE</strong><span>Create a polished proposal for a real client before paying anything.</span></div>
      <p class="price-fineprint">Standing instruction is authorised first. ₹999/month begins only after day 15. MRP ₹2,999/month.</p>
      <a class="btn-primary" href="/pricing#designer-quotation-builder">Build my first proposal free</a>
    </div>
    <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1200&q=85" alt="Interior proposal and quotation planning" loading="lazy" decoding="async">
  </div>
</section>

<dialog class="membership-checkout-dialog" id="membershipCheckoutDialog">
  <form method="dialog" class="membership-checkout-shell" id="membershipCheckoutForm">
    <div><p class="eyebrow">MEMBERSHIP CHECKOUT</p><h2>Confirm billing details</h2><p>Your profile has been saved. Complete these details, then securely authorise your recurring plan in Razorpay.</p></div>
    <div class="budget-grid"><input name="business_name" required placeholder="Business name *"><input name="owner_name" required placeholder="Owner name *"></div>
    <div class="budget-grid"><input name="whatsapp_phone" required inputmode="numeric" placeholder="WhatsApp number *"><input name="invoice_email" required type="email" placeholder="Invoice email *"></div>
    <div class="budget-grid"><input name="gstin" placeholder="GSTIN (optional)"><input name="featured_locality" placeholder="Featured locality (Pro + Featured)"></div>
    <label class="lead-consent"><input name="renewal_consent" type="checkbox" required><span>I understand that this recurring plan auto-renews until I cancel it through Razorpay.</span></label>
    <div class="designer-step-actions"><button class="btn-muted" type="button" data-close-membership-checkout>Not now</button><button class="btn-primary" type="submit">Continue to secure payment</button></div>
    <p class="form-message" id="membershipCheckoutMessage"></p>
  </form>
</dialog>

<script>
(() => {
  const membershipPlans = document.querySelectorAll('[data-plan-card]');
  const membershipCycleButtons = document.querySelectorAll('[data-membership-cycle]');
  const switchCycle = (cycle) => {
    membershipCycleButtons.forEach((button) => button.classList.toggle('active', button.dataset.membershipCycle === cycle));
    membershipPlans.forEach((card) => { card.style.display = card.dataset.planCycle === cycle ? 'flex' : 'none'; });
  };
  membershipCycleButtons.forEach((button) => button.addEventListener('click', () => switchCycle(button.dataset.membershipCycle)));
  document.querySelectorAll('[data-select-membership]').forEach((button) => button.addEventListener('click', () => {
    const formTarget = document.getElementById('designerRegisterForm');
    formTarget.elements.membership_plan.value = button.dataset.selectMembership || 'free';
    formTarget.elements.membership_cycle.value = button.dataset.selectCycle || 'monthly';
    formTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }));
  const form = document.getElementById('designerRegisterForm');
  const membershipDialog = document.getElementById('membershipCheckoutDialog');
  const membershipCheckoutForm = document.getElementById('membershipCheckoutForm');
  const membershipCheckoutMessage = document.getElementById('membershipCheckoutMessage');
  const loadRazorpay = () => new Promise((resolve, reject) => {
    if (window.Razorpay) return resolve();
    const script = document.createElement('script'); script.src = 'https://checkout.razorpay.com/v1/checkout.js'; script.onload = resolve; script.onerror = () => reject(new Error('Unable to load Razorpay checkout.')); document.head.appendChild(script);
  });
  const msg = document.getElementById('designerRegisterMsg');
  const steps = Array.from(form.querySelectorAll('[data-form-step]'));
  const navSteps = Array.from(document.querySelectorAll('[data-step-jump]'));
  let maxSavedStep = 0;

  const showStep = (step) => {
    steps.forEach((panel) => {
      const active = panel.dataset.formStep === String(step);
      panel.hidden = !active;
      panel.classList.toggle('active', active);
    });
    navSteps.forEach((item) => {
      item.classList.toggle('active', item.dataset.stepJump === String(step));
      item.classList.toggle('complete', Number(item.dataset.stepJump) < step || Number(item.dataset.stepJump) <= maxSavedStep);
    });
  };

  const validateStep = (step) => {
    const panel = form.querySelector(`[data-form-step="${step}"]`);
    for (const field of panel.querySelectorAll('input, select, textarea')) {
      if (!field.checkValidity()) {
        field.reportValidity();
        return false;
      }
    }
    return true;
  };

  const saveStep = async (step, isFinal = false) => {
    if (!validateStep(step)) return;
    const button = form.querySelector(`[data-save-step="${step}"]`);
    if (button) button.disabled = true;
    msg.className = 'form-message';
    msg.textContent = step === 1 ? 'Saving firm details...' : 'Saving this step...';

    const fd = new FormData(form);
    fd.set('registration_step', String(step));
    if (isFinal) fd.set('final_step', '1');

    try {
      const response = await fetch('/api/interior-designer-registration', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.error || 'Could not save this step. Please try again.');
      }

      form.elements.pro_id.value = data.pro_id || form.elements.pro_id.value;
      maxSavedStep = Math.max(maxSavedStep, step);
      msg.className = 'form-message ok';
      msg.textContent = isFinal ? 'Registration complete. Opening portfolio upload...' : `Step ${step} saved.`;
      if (isFinal) {
        if (form.elements.membership_plan.value !== 'free') {
          membershipCheckoutForm.elements.business_name.value = form.elements.full_name.value;
          membershipCheckoutForm.elements.owner_name.value = form.elements.full_name.value;
          membershipCheckoutForm.elements.whatsapp_phone.value = form.elements.phone.value;
          membershipCheckoutForm.elements.invoice_email.value = form.elements.email.value;
          membershipDialog.showModal();
          return;
        }
        setTimeout(() => { window.location.href = data.redirect_url || '/designer/portfolio-onboarding'; }, 500);
        return;
      }
      showStep(Math.min(step + 1, 3));
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
      msg.className = 'form-message error';
      msg.textContent = error instanceof Error ? error.message : 'Could not save this step. Please try again.';
    } finally {
      if (button && !isFinal) button.disabled = false;
    }
  };

  form.querySelectorAll('[data-save-step]').forEach((button) => {
    button.addEventListener('click', () => saveStep(Number(button.dataset.saveStep), button.dataset.finalStep === '1'));
  });
  form.querySelectorAll('[data-prev-step]').forEach((button) => {
    button.addEventListener('click', () => showStep(Number(button.dataset.prevStep)));
  });
  navSteps.forEach((button) => {
    button.addEventListener('click', () => {
      const target = Number(button.dataset.stepJump);
      if (target <= maxSavedStep + 1) showStep(target);
    });
  });
  document.querySelector('[data-close-membership-checkout]')?.addEventListener('click', () => { membershipDialog.close(); window.location.href = '/designer/portfolio-onboarding'; });
  membershipCheckoutForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const submit = membershipCheckoutForm.querySelector('button[type="submit"]'); submit.disabled = true;
    try {
      const details = Object.fromEntries(new FormData(membershipCheckoutForm));
      details.plan_code = form.elements.membership_plan.value;
      details.billing_cycle = form.elements.membership_cycle.value;
      details.renewal_consent = membershipCheckoutForm.elements.renewal_consent.checked;
      const response = await fetch('/api/designer/membership/checkout', {method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify(details)});
      const payload = await response.json(); if (!response.ok) throw new Error(payload.error || 'Could not start secure payment.');
      await loadRazorpay();
      const checkout = new Razorpay({key:payload.key_id,subscription_id:payload.subscription_id,name:payload.name,description:payload.description,handler: async (payment) => {
        const verified = await fetch('/api/designer/membership/verify',{method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify(payment)}); const result = await verified.json(); if (!verified.ok) throw new Error(result.error || 'Payment verification failed.'); window.location.href=result.redirect_url || '/designer';
      },modal:{ondismiss:()=>{submit.disabled=false;}}}); checkout.open();
    } catch (error) { membershipCheckoutMessage.className='form-message error'; membershipCheckoutMessage.textContent=error.message || 'Could not start secure payment.'; submit.disabled=false; }
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
