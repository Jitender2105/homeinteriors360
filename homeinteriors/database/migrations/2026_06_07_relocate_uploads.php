<?php

declare(strict_types=1);

$documentRoot = dirname((string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
if ($documentRoot === '' || !is_file($documentRoot . '/index.php')) {
    throw new RuntimeException('Unable to determine the public document root.');
}

$sourceRoot = $documentRoot . '/public/uploads';
$targetRoot = $documentRoot . '/uploads';
$moved = [];
$skipped = [];

if (is_dir($sourceRoot)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $entry) {
        $relative = substr($entry->getPathname(), strlen($sourceRoot) + 1);
        $target = $targetRoot . '/' . $relative;
        if ($entry->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0775, true);
            }
            continue;
        }

        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        if (is_file($target)) {
            $skipped[] = $relative;
            continue;
        }
        if (!rename($entry->getPathname(), $target) && !copy($entry->getPathname(), $target)) {
            throw new RuntimeException('Failed to relocate upload: ' . $relative);
        }
        $moved[] = $relative;
    }
}

return [
    'source' => $sourceRoot,
    'target' => $targetRoot,
    'moved' => $moved,
    'skipped' => $skipped,
];
