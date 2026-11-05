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


function package_test_rejects(string $zipPath, string $label): void
{
    try {
        system_update_read_package($zipPath);
        throw new RuntimeException('Expected package rejection: ' . $label);
    } catch (RuntimeException $error) {
        if (str_starts_with($error->getMessage(), 'Expected package rejection:')) { throw $error; }
        echo "PASS rejected package: {$label}\n";
    }
}

$versionContents = "1.62.83\n";
$manifest = json_encode([
    'version' => '1.62.83',
    'requires_version' => '1.62.82',
    'migrations' => [],
    'files' => ['VERSION'],
    'sha256' => ['VERSION' => hash('sha256', $versionContents)],
], JSON_UNESCAPED_UNICODE);
$validPackage = make_test_zip([
    'manifest.json' => $manifest,
    'files/VERSION' => $versionContents,
]);
$stage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-stage-test-' . bin2hex(random_bytes(6));
try {
    $package = system_update_read_package($validPackage);
    if ($package['version'] !== '1.62.83' || $package['mode'] !== 'upgrade') {
        throw new RuntimeException('Valid update package metadata was not accepted.');
    }
    if (!mkdir($stage, 0700, true)) { throw new RuntimeException('Could not create extraction stage.'); }
    system_update_extract_package($validPackage, $stage, $package['manifest']);
    if (trim((string) file_get_contents($stage . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'VERSION')) !== '1.62.83') {
        throw new RuntimeException('Valid update package did not extract the expected VERSION.');
    }
    echo "PASS accepts, extracts and hash-verifies a complete update package\n";
} finally {
    @unlink($validPackage);
    if (is_dir($stage)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stage, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($stage);
    }
}

$extraFileManifest = json_encode(['version' => '1.62.83', 'files' => ['VERSION'], 'migrations' => []]);
$extraFilePackage = make_test_zip([
    'manifest.json' => $extraFileManifest,
    'files/VERSION' => $versionContents,
    'files/extra.txt' => 'not declared',
]);
try { package_test_rejects($extraFilePackage, 'undeclared payload file'); }
finally { @unlink($extraFilePackage); }

$missingFileManifest = json_encode(['version' => '1.62.83', 'files' => ['MISSING.txt'], 'migrations' => []]);
$missingFilePackage = make_test_zip([
    'manifest.json' => $missingFileManifest,
    'files/VERSION' => $versionContents,
]);
try { package_test_rejects($missingFilePackage, 'missing manifest-listed file'); }
finally { @unlink($missingFilePackage); }

$badMigrationManifest = json_encode(['version' => '1.62.83', 'files' => ['VERSION'], 'migrations' => ['032-missing.sql']]);
$badMigrationPackage = make_test_zip([
    'manifest.json' => $badMigrationManifest,
    'files/VERSION' => $versionContents,
]);
try { package_test_rejects($badMigrationPackage, 'missing manifest-listed migration'); }
finally { @unlink($badMigrationPackage); }
