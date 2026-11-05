<?php
declare(strict_types=1);

/** Behavioral ZIP-entry tests; requires PHP ext-zip. */
const APP_ROOT = __DIR__ . '/..';
function db() { throw new RuntimeException('Database is not used by this test.'); }
function save_audit(...$args): void {}
function app_error_log(string $message): bool { return true; }
function cfg(string $key, mixed $default = null): mixed { return $default; }
require APP_ROOT . '/system-update.php';

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "FAIL: ext-zip is required for archive validation tests.\n");
    exit(1);
}

function make_test_zip(array $entries, ?string $symlinkName = null): string
{
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-zip-test-' . bin2hex(random_bytes(8)) . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Could not create test ZIP.');
    }
    foreach ($entries as $name => $contents) {
        if (!$zip->addFromString($name, $contents)) {
            throw new RuntimeException('Could not add test ZIP entry: ' . $name);
        }
    }
    if ($symlinkName !== null) {
        if (!method_exists($zip, 'setExternalAttributesName')
            || !$zip->setExternalAttributesName($symlinkName, ZipArchive::OPSYS_UNIX, (0120777 << 16))) {
            $zip->close();
            @unlink($path);
            throw new RuntimeException('Could not mark test ZIP entry as a symlink.');
        }
    }
    $zip->close();
    return $path;
}

function assert_zip_rejected(array $entries, string $label, ?string $symlinkName = null): void
{
    $path = make_test_zip($entries, $symlinkName);
    try {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) { throw new RuntimeException('Could not reopen test ZIP.'); }
        try {
            system_update_validate_zip_entries($zip);
            throw new RuntimeException('Expected ZIP rejection: ' . $label);
        } catch (RuntimeException $e) {
            if (str_starts_with($e->getMessage(), 'Expected ZIP rejection:')) { throw $e; }
            echo "PASS rejected: {$label}\n";
        } finally {
            $zip->close();
        }
    } finally {
        @unlink($path);
    }
}

$valid = make_test_zip([
    'manifest.json' => '{"version":"1.62.83","files":["VERSION"]}',
    'files/VERSION' => "1.62.83\n",
    'migrations/032-audit-hardening.sql' => '-- no-op test migration',
]);
try {
    $zip = new ZipArchive();
    if ($zip->open($valid) !== true) { throw new RuntimeException('Could not reopen valid test ZIP.'); }
    try {
        system_update_validate_zip_entries($zip);
        echo "PASS accepts valid package layout\n";
    } finally { $zip->close(); }
} finally { @unlink($valid); }

assert_zip_rejected(['manifest.json' => '{}', 'files/../escape.php' => 'x'], 'parent traversal');
assert_zip_rejected(['manifest.json' => '{}', 'files/Readme.md' => 'a', 'files/readme.md' => 'b'], 'case-insensitive duplicate');
assert_zip_rejected(['manifest.json' => '{}', 'files/node' => 'file', 'files/node/child.txt' => 'child'], 'file/directory collision');
assert_zip_rejected(['manifest.json' => '{}', 'files/link' => 'target'], 'symbolic link', 'files/link');

echo "All ZIP entry validation tests passed.\n";
