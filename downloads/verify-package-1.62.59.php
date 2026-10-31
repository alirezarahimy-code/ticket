<?php
declare(strict_types=1);

// ابزار فقط‌خواندنی خط فرمان؛ فایل ZIP باید پیش‌تر با SHA-256 مورد اعتماد بررسی شود.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if ($argc !== 3 || !class_exists('ZipArchive')) {
    fwrite(STDERR, "Usage: php verify-package-1.62.59.php APP_ROOT PACKAGE.zip (requires ZipArchive)\n");
    exit(2);
}
$root = realpath($argv[1]);
if ($root === false || !is_dir($root)) {
    fwrite(STDERR, "Invalid application directory.\n");
    exit(2);
}
$zip = new ZipArchive();
if ($zip->open($argv[2]) !== true) {
    fwrite(STDERR, "Cannot open ZIP.\n");
    exit(2);
}
$manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
if (!is_array($manifest) || ($manifest['version'] ?? '') !== '1.62.59' || !is_array($manifest['files'] ?? null)) {
    fwrite(STDERR, "Invalid or unexpected manifest.\n");
    $zip->close();
    exit(2);
}
$failed = false;
$seen = [];
foreach ($manifest['files'] as $relative) {
    if (!is_string($relative) || $relative === '' || str_contains($relative, '..') || str_contains($relative, '\\') || str_starts_with($relative, '/')) {
        fwrite(STDERR, "Invalid package path.\n");
        $failed = true;
        continue;
    }
    if (isset($seen[$relative])) {
        fwrite(STDERR, "Duplicate package path: $relative\n");
        $failed = true;
        continue;
    }
    $seen[$relative] = true;
    $contents = $zip->getFromName('files/' . $relative);
    $file = realpath($root . DIRECTORY_SEPARATOR . $relative);
    if ($contents === false || $file === false || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file) || !is_readable($file)) {
        echo "MISSING/UNREADABLE: $relative\n";
        $failed = true;
        continue;
    }
    $hash = hash_file('sha256', $file);
    if ($hash === false || !hash_equals(hash('sha256', $contents), $hash)) {
        echo "DIFFERENT: $relative\n";
        $failed = true;
    } else {
        echo "OK: $relative\n";
    }
}
if (!isset($seen['VERSION']) || trim((string) $zip->getFromName('files/VERSION')) !== '1.62.59') {
    fwrite(STDERR, "Invalid VERSION in package.\n");
    $failed = true;
}
$zip->close();
echo $failed ? "Verification failed.\n" : "All listed package files match.\n";
exit($failed ? 1 : 0);
