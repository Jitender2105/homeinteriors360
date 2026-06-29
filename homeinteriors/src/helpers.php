<?php

declare(strict_types=1);

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function requestJson(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function requestData(): array
{
    if (str_contains((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
        return requestJson();
    }
    return $_POST;
}

function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require dirname(__DIR__) . '/src/Views/' . $view . '.php';
}

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function appBaseUrl(): string
{
    $configured = trim((string)(getenv('APP_BASE_URL') ?: getenv('NEXT_PUBLIC_SITE_URL') ?: ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $host = (string)($_SERVER['HTTP_HOST'] ?? 'homeinteriors360.com');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
    return $scheme . '://' . $host;
}

function absoluteUrl(string $path = '/'): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return appBaseUrl() . '/' . ltrim($path, '/');
}

function canonicalPath(string $path): string
{
    $path = parse_url($path, PHP_URL_PATH) ?: '/';
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    return $path === '' ? '/' : $path;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
    $value = preg_replace('/\s+/', '-', $value) ?? '';
    $value = preg_replace('/-+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'item-' . time();
}

function appPublicRoot(): string
{
    $scriptFilename = (string)($_SERVER['SCRIPT_FILENAME'] ?? '');
    if ($scriptFilename !== '' && is_file($scriptFilename)) {
        $scriptDir = dirname($scriptFilename);
        if (is_file($scriptDir . '/index.php')) {
            return $scriptDir;
        }
    }

    $root = dirname(__DIR__);
    if (is_dir($root . '/public') && is_file($root . '/public/index.php')) {
        return $root . '/public';
    }
    return $root;
}

function ensureUploadDir(string $subdir): string
{
    $baseDir = rtrim(appPublicRoot(), '/');
    $dir = $baseDir . '/uploads/' . trim($subdir, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return $dir;
}

function publicUploadPath(string $subdir, string $filename): string
{
    return '/uploads/' . trim($subdir, '/') . '/' . ltrim($filename, '/');
}

function saveUploadedFile(array $fileInfo, string $subdir, ?string $existing = null): ?string
{
    if (empty($fileInfo) || !isset($fileInfo['error']) || (int)$fileInfo['error'] !== UPLOAD_ERR_OK) {
        return $existing;
    }

    $tmpName = (string)($fileInfo['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return $existing;
    }

    $original = (string)($fileInfo['name'] ?? 'file');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];
    if (!in_array($ext, $allowed, true)) {
        $ext = 'jpg';
    }

    $name = slugify(pathinfo($original, PATHINFO_FILENAME)) . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dir = ensureUploadDir($subdir);
    $target = $dir . '/' . $name;

    if (!move_uploaded_file($tmpName, $target)) {
        return $existing;
    }

    return publicUploadPath($subdir, $name);
}

function saveUploadedFiles(array $files, string $subdir, array $existing = []): array
{
    $stored = $existing;
    if (empty($files) || !isset($files['error'])) {
        return $stored;
    }

    $count = is_array($files['error']) ? count($files['error']) : 0;
    for ($i = 0; $i < $count; $i++) {
        $file = [
            'name' => $files['name'][$i] ?? '',
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0,
        ];
        $path = saveUploadedFile($file, $subdir, null);
        if ($path) {
            $stored[] = $path;
        }
    }

    return $stored;
}

function getJsonArrayField(array $data, string $key, array $fallback = []): array
{
    if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
        return $fallback;
    }
    $value = $data[$key];
    if (is_array($value)) {
        return array_values(array_filter(array_map('strval', $value), static fn(string $item): bool => trim($item) !== ''));
    }
    $decoded = json_decode((string)$value, true);
    if (is_array($decoded)) {
        return array_values(array_filter(array_map('strval', $decoded), static fn(string $item): bool => trim($item) !== ''));
    }
    return array_values(array_filter(array_map('trim', explode(',', (string)$value))));
}

function buyerUser(): ?array
{
    Auth::start();
    return isset($_SESSION['buyer_user']) && is_array($_SESSION['buyer_user']) ? $_SESSION['buyer_user'] : null;
}

function requireBuyer(): array
{
    $buyer = buyerUser();
    if (!$buyer) {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (str_starts_with($path, '/api/') || str_starts_with($path, '/lead-download/')) {
            jsonResponse(['error' => 'Buyer login required'], 401);
        }
        redirectTo('/lead-checkout');
    }
    return $buyer;
}

function setBuyerSession(array $buyer): void
{
    Auth::start();
    $_SESSION['buyer_user'] = [
        'id' => (int)$buyer['id'],
        'name' => (string)$buyer['name'],
        'email' => (string)($buyer['email'] ?? ''),
        'phone' => (string)$buyer['phone'],
    ];
}

function clearBuyerSession(): void
{
    Auth::start();
    unset($_SESSION['buyer_user']);
}

function razorpayConfigured(): bool
{
    return defined('RAZORPAY_KEY_ID') && defined('RAZORPAY_KEY_SECRET') && RAZORPAY_KEY_ID !== '' && RAZORPAY_KEY_SECRET !== '';
}

function razorpayCreateOrder(int $amountPaise, string $receipt, array $notes = [], ?string $currency = null): array
{
    if (!razorpayConfigured()) {
        throw new RuntimeException('Razorpay gateway is not configured.');
    }
    if ($amountPaise < 100) {
        throw new RuntimeException('Razorpay order amount must be at least 100 paise.');
    }

    $payload = [
        'amount' => $amountPaise,
        'currency' => strtoupper($currency ?: (defined('RAZORPAY_CURRENCY') ? RAZORPAY_CURRENCY : 'INR')),
        'receipt' => substr($receipt, 0, 40),
        'notes' => $notes,
    ];

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    if (!$ch) {
        throw new RuntimeException('Failed to initialize Razorpay request.');
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        $message = 'Razorpay order creation failed' . ($error !== '' ? ': ' . $error : '.');
        throw new RuntimeException($message, $status === 401 ? 401 : 500);
    }
    $data = json_decode((string)$response, true);
    if (!is_array($data) || empty($data['id'])) {
        throw new RuntimeException('Invalid Razorpay order response.');
    }
    return $data;
}

function razorpaySignatureIsValid(string $orderId, string $paymentId, string $signature): bool
{
    if (!razorpayConfigured() || $signature === '') {
        return false;
    }
    $generated = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
    return hash_equals($generated, $signature);
}
