<?php
$title = $title ?? 'HomeInteriors360';
$active = $active ?? '';
$content = $content ?? [];
$metaDescription = $metaDescription ?? (string)($content['seo.home.description'] ?? 'HomeInteriors360 helps you find verified architects, interior designers, and contractors for your home project.');

$navHome = (string)($content['nav.home'] ?? 'Home');
$navDirectory = (string)($content['nav.directory'] ?? 'Find Professionals');
$navPricing = (string)($content['nav.pricing'] ?? 'Pricing');
$navCalculator = (string)($content['nav.calculator'] ?? 'Cost Calculator');
$navAdmin = (string)($content['nav.admin'] ?? 'Admin');
$rootPath = dirname(__DIR__, 3);
$styleCandidates = [
  $rootPath . '/public/assets/style.css',
  $rootPath . '/assets/style.css',
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
  <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:type" content="website" />
  <link rel="icon" href="/favicon.png" />
  <link rel="apple-touch-icon" href="/favicon.png" />
  <link rel="stylesheet" href="/assets/style.css?v=<?= htmlspecialchars($styleVersion, ENT_QUOTES, 'UTF-8') ?>" />
</head>
<body>
  <header class="site-header">
    <div class="container nav-shell">
      <a class="brand" href="/">
        <img src="/logo.png" alt="HomeInteriors360" onerror="this.style.display='none'" />
      </a>
      <button class="nav-toggle" id="navToggle" type="button" aria-expanded="false" aria-label="Open menu">☰</button>
      <nav class="nav-links">
        <a class="<?= $active === 'home' ? 'active' : '' ?>" href="/"><?= htmlspecialchars($navHome, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'directory' ? 'active' : '' ?>" href="/professionals"><?= htmlspecialchars($navDirectory, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'pricing' ? 'active' : '' ?>" href="/pricing"><?= htmlspecialchars($navPricing, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'calculator' ? 'active' : '' ?>" href="/cost-calculator"><?= htmlspecialchars($navCalculator, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'admin' ? 'active' : '' ?>" href="/admin"><?= htmlspecialchars($navAdmin, ENT_QUOTES, 'UTF-8') ?></a>
      </nav>
    </div>
  </header>
