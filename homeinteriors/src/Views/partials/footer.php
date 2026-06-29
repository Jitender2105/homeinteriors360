<?php
$content = $content ?? [];
$tagline = (string)($content['footer.tagline'] ?? 'Designed for modern homes across Delhi NCR');
$copy = (string)($content['footer.copy'] ?? 'HomeInteriors360. All rights reserved.');
$footerLinks = [
  ['label' => 'Contact Us', 'href' => '/contact-us'],
  ['label' => 'Privacy Policy', 'href' => '/privacy-policy'],
  ['label' => 'Terms and Conditions', 'href' => '/terms-and-conditions'],
  ['label' => 'Shipping Policy', 'href' => '/shipping-policy'],
  ['label' => 'Pricing Details', 'href' => '/pricing-details'],
  ['label' => 'Cancellation and Refunds Policy', 'href' => '/cancellation-and-refunds-policy'],
];
$seoLinks = [
  ['label' => 'Buy or Rent Property', 'href' => '/properties'],
  ['label' => 'Interior Design Ideas', 'href' => '/design-ideas'],
  ['label' => 'Kitchen Design Ideas', 'href' => '/design-ideas/kitchen-designs'],
  ['label' => 'Bedroom Design Ideas', 'href' => '/design-ideas/bedroom-designs'],
  ['label' => 'Interior Design Leads', 'href' => '/interior-design-leads'],
  ['label' => 'Interior Designer Leads', 'href' => '/interior-designer-leads'],
  ['label' => 'Buy Interior Design Leads', 'href' => '/buy-interior-design-leads'],
  ['label' => 'Interior Leads Provider in India', 'href' => '/interior-leads-provider-india'],
  ['label' => 'Interior Designer Leads in Delhi NCR', 'href' => '/interior-designer-leads-delhi-ncr'],
  ['label' => 'Interior Designer Leads in Gurgaon', 'href' => '/interior-designer-leads-gurgaon'],
];
?>
<footer class="site-footer">
  <div class="container footer-shell">
    <img src="/logo.png" alt="HomeInteriors360" class="footer-logo" />
    <p class="footer-tagline"><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></p>
    <nav class="footer-links footer-seo-links" aria-label="Interior lead generation pages">
      <?php foreach ($seoLinks as $link): ?>
        <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></a>
      <?php endforeach; ?>
    </nav>
    <nav class="footer-links" aria-label="Company policies">
      <?php foreach ($footerLinks as $link): ?>
        <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></a>
      <?php endforeach; ?>
    </nav>
    <p class="footer-copy">&copy; <?= date('Y') ?> <?= htmlspecialchars($copy, ENT_QUOTES, 'UTF-8') ?></p>
  </div>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(() => {
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.querySelectorAll('.nav-links a');
  if (navToggle) {
    navToggle.addEventListener('click', () => {
      const open = document.body.classList.toggle('menu-open');
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    navLinks.forEach((link) => {
      link.addEventListener('click', () => {
        document.body.classList.remove('menu-open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth > 760) {
        document.body.classList.remove('menu-open');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  const nodes = document.querySelectorAll('[data-reveal]');
  if (!('IntersectionObserver' in window)) {
    nodes.forEach((el) => el.classList.add('in'));
  } else {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    nodes.forEach((el, index) => {
      el.style.transitionDelay = `${Math.min(index * 60, 300)}ms`;
      observer.observe(el);
    });
  }

  const societyWidgets = document.querySelectorAll('[data-society-lookup]');
  if (!societyWidgets.length) return;

  const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));

  if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
    window.jQuery('.select2-society-field').each(function initSocietySelect2() {
      const select = window.jQuery(this);
      const form = this.closest('form');
      select.select2({
        width: '100%',
        placeholder: select.data('placeholder') || 'Select or search society',
        allowClear: true,
        tags: true,
        ajax: {
          url: '/api/societies',
          dataType: 'json',
          delay: 200,
          data(params) {
            const city = form?.querySelector('[name="city"]')?.value || '';
            return { q: params.term || '', city };
          },
          processResults(data) {
            const societies = Array.isArray(data.societies) ? data.societies : [];
            return {
              results: societies.map((society) => {
                const name = String(society.name || '');
                const city = String(society.city || '');
                return {
                  id: name,
                  text: city ? `${name} (${city})` : name,
                  societyName: name,
                };
              }),
            };
          },
          cache: true,
        },
        createTag(params) {
          const term = String(params.term || '').trim();
          if (!term) return null;
          return { id: term, text: `Other: ${term}`, societyName: term, newTag: true };
        },
        templateSelection(item) {
          return item.societyName || item.id || item.text;
        },
      });

      form?.addEventListener('reset', () => {
        setTimeout(() => select.val(null).trigger('change'), 0);
      });
    });
    return;
  }

  const loadSocieties = async (term = '', city = '') => {
    const params = new URLSearchParams();
    if (term) params.set('q', term);
    if (city) params.set('city', city);
    const response = await fetch(`/api/societies?${params.toString()}`);
    const data = response.ok ? await response.json() : { societies: [] };
    return Array.isArray(data.societies) ? data.societies : [];
  };
  const renderSocietyOptions = (widget, societies) => {
    const select = widget.querySelector('.society-select');
    if (!select) return;
    const current = select.value;
    select.innerHTML = '<option value="">Select or search society</option>' + societies.map((society) => {
      const name = String(society.name || '');
      const city = String(society.city || '');
      const label = city ? `${name} (${city})` : name;
      return `<option value="${escapeHtml(name)}">${escapeHtml(label)}</option>`;
    }).join('');
    if ([...select.options].some((option) => option.value === current)) {
      select.value = current;
    }
  };

  societyWidgets.forEach(async (widget) => {
    const select = widget.querySelector('.society-select');
    const form = widget.closest('form');
    if (!select) return;
    const city = form?.querySelector('[name="city"]')?.value || '';
    const societies = await loadSocieties('', city);
    renderSocietyOptions(widget, societies);
  });
})();
</script>
</body>
</html>
