<?php
$title = $title ?? 'HomeInteriors360';
$active = $active ?? '';
$content = $content ?? [];

$navHome = (string)($content['nav.home'] ?? 'Home');
$navDirectory = (string)($content['nav.directory'] ?? 'Find Professionals');
$navCalculator = (string)($content['nav.calculator'] ?? 'Cost Calculator');
$navAdmin = (string)($content['nav.admin'] ?? 'Admin');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/assets/style.css" />
</head>
<body>
  <header class="site-header">
    <div class="container nav-shell">
      <a class="brand" href="/">
        <img src="/logo.png" alt="HomeInteriors360" onerror="this.style.display='none'" />
      </a>
      <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-label="Open menu">☰</button>
      <nav class="nav-links">
        <a class="<?= $active === 'home' ? 'active' : '' ?>" href="/"><?= htmlspecialchars($navHome, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'directory' ? 'active' : '' ?>" href="/professionals"><?= htmlspecialchars($navDirectory, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'calculator' ? 'active' : '' ?>" href="/cost-calculator"><?= htmlspecialchars($navCalculator, ENT_QUOTES, 'UTF-8') ?></a>
        <a class="<?= $active === 'admin' ? 'active' : '' ?>" href="/admin"><?= htmlspecialchars($navAdmin, ENT_QUOTES, 'UTF-8') ?></a>
      </nav>
    </div>
  </header>
