<?php

declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

$root = dirname(__DIR__);
loadEnv($root . '/.env');
loadEnv($root . '/.env.local');

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'interiordesign360',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Interior Design 360',
        'base_url' => getenv('APP_BASE_URL') ?: '',
        'key' => getenv('APP_KEY') ?: 'change-this-app-key',
    ],
    'razorpay' => [
        'key_id' => getenv('RAZORPAY_KEY_ID') ?: '',
        'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: '',
        'currency' => getenv('RAZORPAY_CURRENCY') ?: 'INR',
        'quotation_plan_id' => getenv('RAZORPAY_QUOTATION_PLAN_ID') ?: '',
        'designer_pro_monthly_plan_id' => getenv('RAZORPAY_DESIGNER_PRO_MONTHLY_PLAN_ID') ?: '',
        'designer_pro_yearly_plan_id' => getenv('RAZORPAY_DESIGNER_PRO_YEARLY_PLAN_ID') ?: '',
        'designer_featured_monthly_plan_id' => getenv('RAZORPAY_DESIGNER_FEATURED_MONTHLY_PLAN_ID') ?: '',
        'designer_featured_yearly_plan_id' => getenv('RAZORPAY_DESIGNER_FEATURED_YEARLY_PLAN_ID') ?: '',
        'webhook_secret' => getenv('RAZORPAY_WEBHOOK_SECRET') ?: '',
    ],
];
