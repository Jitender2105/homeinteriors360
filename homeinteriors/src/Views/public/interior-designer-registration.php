<?php
$standardOptions = $standardOptions ?? [];
$optionList = static fn(string $key): array => $standardOptions[$key] ?? [];
require __DIR__ . '/../partials/header.php';
?>
<section class="designer-register-hero">
  <div class="container designer-register-grid" data-reveal>
    <div>
      <p class="eyebrow">Interior designer registration</p>
      <h1>Create your professional profile and get 10 free leads.</h1>
      <p>Register your firm, upload your logo and business details, then add portfolio work. Your profile stays inactive until admin review, so incomplete profiles will not appear publicly.</p>
      <div class="designer-register-benefits">
        <span>Professional portfolio page</span>
        <span>10 free leads once</span>
        <span>Designer dashboard login</span>
        <span>Quotation builder upgrade</span>
      </div>
    </div>
    <form id="designerRegisterForm" class="designer-register-form" enctype="multipart/form-data">
      <input type="hidden" name="role" value="Designer">
      <h2>Firm and login details</h2>
      <div class="budget-grid">
        <input name="full_name" required placeholder="Firm / designer name *">
        <input name="email" type="email" required placeholder="Login email *">
      </div>
      <div class="budget-grid">
        <input name="phone" required placeholder="Mobile number *">
        <input name="password" type="password" required minlength="8" placeholder="Create password *">
      </div>
      <div class="budget-grid">
        <input name="office_address" placeholder="Office address / location">
        <select name="city" required>
          <option value="">City *</option>
          <?php foreach ($optionList('cities') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
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
        <label class="file-field"><span>Firm logo / profile image</span><input type="file" name="profile_pic" accept="image/*"></label>
        <label class="file-field"><span>Cover image</span><input type="file" name="cover_photo" accept="image/*"></label>
      </div>

      <h2>Professional profile details</h2>
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
      <label class="standard-select-label"><span>Service areas</span><select name="service_areas[]" class="standard-multi-select" multiple><?php foreach ($optionList('service_regions') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Materials used</span><select name="materials_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('materials') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Offerings</span><select name="offerings_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('offerings') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Design styles</span><select name="design_styles_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('design_styles') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Languages</span><select name="languages_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('languages') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <label class="standard-select-label"><span>Certifications</span><select name="certifications_json[]" class="standard-multi-select" multiple><?php foreach ($optionList('certifications') as $option): ?><option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
      <textarea name="process_steps_json" rows="3" placeholder="Process steps, one per line. Use Title | Description if needed."></textarea>
      <textarea name="awards_json" rows="3" placeholder="Awards / highlights, one per line"></textarea>
      <textarea name="faq_json" rows="3" placeholder="FAQs, one per line: Question | Answer"></textarea>
      <label class="lead-consent"><input type="checkbox" name="consent" value="1" required checked><span>I agree to the Privacy Policy and Terms & Conditions and consent to be contacted by phone, SMS, email, WhatsApp or RCS.</span></label>
      <button class="btn-primary" type="submit">Register and continue to portfolio</button>
      <p id="designerRegisterMsg" class="form-message"></p>
      <p class="muted-line">Already registered? <a href="/designer/login">Login as interior designer</a></p>
    </form>
  </div>
</section>
<script>
(() => {
  const form = document.getElementById('designerRegisterForm');
  const msg = document.getElementById('designerRegisterMsg');
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    msg.className = 'form-message';
    msg.textContent = 'Creating your profile...';
    const response = await fetch('/api/interior-designer-registration', {
      method: 'POST',
      credentials: 'same-origin',
      body: new FormData(form)
    });
    const data = await response.json();
    msg.className = `form-message ${response.ok ? 'ok' : 'error'}`;
    msg.textContent = response.ok ? 'Profile created. Continue with portfolio uploads.' : (data.error || 'Registration failed.');
    if (response.ok) {
      setTimeout(() => { window.location.href = data.redirect_url || '/designer/portfolio-onboarding'; }, 500);
    }
  });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
