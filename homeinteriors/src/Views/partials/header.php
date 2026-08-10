<?php
$title = $title ?? 'HomeInteriors360';
$active = $active ?? '';
$content = $content ?? [];
$metaDescription = $metaDescription ?? (string)($content['seo.home.description'] ?? 'HomeInteriors360 helps you find verified architects, interior designers, and contractors for your home project.');
$canonicalUrl = $canonicalUrl ?? absoluteUrl(canonicalPath((string)($_SERVER['REQUEST_URI'] ?? '/')));
$metaRobots = $metaRobots ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
$ogImage = $ogImage ?? absoluteUrl('/logo.png');
$structuredData = $structuredData ?? [];
$metaModifiedTime = $metaModifiedTime ?? date('c', file_exists($_SERVER['SCRIPT_FILENAME'] ?? '') ? (int)filemtime((string)$_SERVER['SCRIPT_FILENAME']) : time());
$defaultStructuredData = [
  [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'HomeInteriors360',
    'url' => absoluteUrl('/'),
    'logo' => absoluteUrl('/logo.png'),
    'contactPoint' => [
      '@type' => 'ContactPoint',
      'telephone' => '+91-9540573661',
      'contactType' => 'customer support',
      'areaServed' => 'IN',
      'availableLanguage' => ['en', 'hi'],
    ],
  ],
  [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'HomeInteriors360',
    'url' => absoluteUrl('/'),
    'potentialAction' => [
      '@type' => 'SearchAction',
      'target' => absoluteUrl('/professionals') . '?city={search_term_string}',
      'query-input' => 'required name=search_term_string',
    ],
  ],
];
$structuredData = array_merge($defaultStructuredData, (array)$structuredData);

$navHome = (string)($content['nav.home'] ?? 'Home');
$navDirectory = (string)($content['nav.directory'] ?? 'Find Professionals');
$navPricing = (string)($content['nav.pricing'] ?? 'Pricing');
$navLeads = (string)($content['nav.leads'] ?? 'Buy Leads');
$navCalculator = (string)($content['nav.calculator'] ?? 'Cost Calculator');
$navDesignIdeas = (string)($content['nav.design_ideas'] ?? 'Design Ideas');
$navVisualizer = (string)($content['nav.visualizer'] ?? 'AI Visualizer');
$navAdmin = (string)($content['nav.admin'] ?? 'Admin');
$rootPath = dirname(__DIR__, 3);
$styleCandidates = [
  $rootPath . '/assets/style.css',
  $rootPath . '/public/assets/style.css',
];
$styleVersion = '1';
foreach ($styleCandidates as $stylePath) {
  if (is_file($stylePath)) {
    $styleVersion = (string) filemtime($stylePath);
    break;
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="robots" content="<?= htmlspecialchars((string)$metaRobots, ENT_QUOTES, 'UTF-8') ?>" />
  <link rel="canonical" href="<?= htmlspecialchars((string)$canonicalUrl, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= htmlspecialchars((string)$canonicalUrl, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:image" content="<?= htmlspecialchars((string)$ogImage, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:site_name" content="HomeInteriors360" />
  <meta property="og:updated_time" content="<?= htmlspecialchars((string)$metaModifiedTime, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="last-modified" content="<?= htmlspecialchars((string)$metaModifiedTime, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="twitter:image" content="<?= htmlspecialchars((string)$ogImage, ENT_QUOTES, 'UTF-8') ?>" />
  <meta name="theme-color" content="#111827" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-title" content="HomeInteriors360" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <link rel="manifest" href="/manifest.json" />
  <link rel="icon" href="/favicon.png" />
  <link rel="apple-touch-icon" href="/favicon.png" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
  <link rel="stylesheet" href="/assets/style.css?v=<?= htmlspecialchars($styleVersion, ENT_QUOTES, 'UTF-8') ?>" />
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => navigator.serviceWorker.register('/service-worker.js').catch(() => {}));
    }
  </script>
  <?php foreach ((array)$structuredData as $schema): ?>
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <?php endforeach; ?>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-TQX5N264');</script>
  <!-- End Google Tag Manager -->
</head>
<body>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TQX5N264"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  <div class="designer-top-strip">
    <div class="container designer-top-strip-inner">
      <span>Register now as interior designer, get professional portfolio and 10 free leads.</span>
      <a href="/interior-designer-registration">Register Now</a>
    </div>
  </div>
  <header class="site-header">
    <div class="container nav-shell">
      <a class="brand" href="/">
        <img src="/logo.png" alt="HomeInteriors360" onerror="this.style.display='none'" />
      </a>
      <button class="nav-toggle" id="navToggle" type="button" aria-expanded="false" aria-label="Open menu">☰</button>
      <nav class="nav-links">
        <a class="<?= $active === 'home' ? 'active' : '' ?>" href="/"><?= htmlspecialchars($navHome, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'directory' ? 'active' : '' ?>" href="/professionals"><?= htmlspecialchars($navDirectory, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'design-ideas' ? 'active' : '' ?>" href="/design-ideas"><?= htmlspecialchars($navDesignIdeas, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'visualizer' ? 'active' : '' ?>" href="/ai-room-visualizer"><?= htmlspecialchars($navVisualizer, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'pricing' ? 'active' : '' ?>" href="/pricing"><?= htmlspecialchars($navPricing, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'leads' ? 'active' : '' ?>" href="/lead-marketplace"><?= htmlspecialchars($navLeads, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'calculator' ? 'active' : '' ?>" href="/cost-calculator"><?= htmlspecialchars($navCalculator, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'admin' ? 'active' : '' ?>" href="/admin"><?= htmlspecialchars($navAdmin, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="nav-designer-register <?= canonicalPath((string)($_SERVER['REQUEST_URI'] ?? '/')) === '/interior-designer-registration' ? 'active' : '' ?>" href="/interior-designer-registration">Register as Designer</a>
        <a class="nav-cta <?= canonicalPath((string)($_SERVER['REQUEST_URI'] ?? '/')) === '/home-interior-hire-a-designer' ? 'active' : '' ?>" href="/home-interior-hire-a-designer">Hire a Designer</a>
      </nav>
    </div>
  </header>
