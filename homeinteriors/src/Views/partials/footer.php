<?php
$content = $content ?? [];
$tagline = (string)($content['footer.tagline'] ?? 'Designed for modern homes across Delhi NCR');
$copy = (string)($content['footer.copy'] ?? 'HomeInteriors360. All rights reserved.');
?>
<footer class="site-footer">
  <div class="container footer-shell">
    <img src="/logo.png" alt="HomeInteriors360" class="footer-logo" />
    <p class="footer-tagline"><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></p>
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
