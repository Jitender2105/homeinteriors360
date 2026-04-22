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
