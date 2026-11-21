<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';
if (!defined('APP_KEY')) {
    define('APP_KEY', (string)($config['app']['key'] ?? 'change-this-app-key'));
}
if (!defined('RAZORPAY_KEY_ID')) {
    define('RAZORPAY_KEY_ID', (string)($config['razorpay']['key_id'] ?? ''));
}
if (!defined('RAZORPAY_KEY_SECRET')) {
    define('RAZORPAY_KEY_SECRET', (string)($config['razorpay']['key_secret'] ?? ''));
}
if (!defined('RAZORPAY_CURRENCY')) {
    define('RAZORPAY_CURRENCY', (string)($config['razorpay']['currency'] ?? 'INR'));
}
if (!defined('RAZORPAY_QUOTATION_PLAN_ID')) {
    define('RAZORPAY_QUOTATION_PLAN_ID', (string)($config['razorpay']['quotation_plan_id'] ?? ''));
}
if (!defined('RAZORPAY_DESIGNER_PRO_MONTHLY_PLAN_ID')) {
    define('RAZORPAY_DESIGNER_PRO_MONTHLY_PLAN_ID', (string)($config['razorpay']['designer_pro_monthly_plan_id'] ?? ''));
    define('RAZORPAY_DESIGNER_PRO_YEARLY_PLAN_ID', (string)($config['razorpay']['designer_pro_yearly_plan_id'] ?? ''));
    define('RAZORPAY_DESIGNER_FEATURED_MONTHLY_PLAN_ID', (string)($config['razorpay']['designer_featured_monthly_plan_id'] ?? ''));
    define('RAZORPAY_DESIGNER_FEATURED_YEARLY_PLAN_ID', (string)($config['razorpay']['designer_featured_yearly_plan_id'] ?? ''));
}
if (!defined('RAZORPAY_WEBHOOK_SECRET')) {
    define('RAZORPAY_WEBHOOK_SECRET', (string)($config['razorpay']['webhook_secret'] ?? ''));
}
require __DIR__ . '/Database.php';
require __DIR__ . '/QuotationCalculator.php';
require __DIR__ . '/LeadMatchingService.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/LocationLandingCatalog.php';
require __DIR__ . '/SitemapService.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/GoogleOAuthService.php';
require __DIR__ . '/Repositories/SiteRepository.php';

Database::init($config['db']);
