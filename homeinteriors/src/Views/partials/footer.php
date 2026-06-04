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
    return;
  }
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
})();
</script>
</body>
</html>
