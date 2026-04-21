<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';
if (!defined('APP_KEY')) {
    define('APP_KEY', (string)($config['app']['key'] ?? 'change-this-app-key'));
}
require __DIR__ . '/Database.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/Repositories/SiteRepository.php';

Database::init($config['db']);
Auth::start();
