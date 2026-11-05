<?php
declare(strict_types=1);

/** Offline behavioral tests for safe restoration of uploads ZIP archives. */
$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-restore-test-' . bin2hex(random_bytes(6));
if (!mkdir($testRoot . DIRECTORY_SEPARATOR . 'storage', 0700, true)) {
    throw new RuntimeException('Could not create test root.');
}
define('APP_ROOT', $testRoot);
require dirname(__DIR__) . '/backup.php';

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "FAIL: ext-zip is required.\n");
    exit(1);
}

function restore_test_assert(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
    echo "PASS: {$message}\n";
}

function restore_test_zip(array $entries, ?string $symlinkName = null): string
{
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-restore-archive-' . bin2hex(random_bytes(6)) . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Could not create test archive.');
    }
    foreach ($entries as $name => $contents) {
        if (!$zip->addFromString($name, $contents)) { throw new RuntimeException('Could not add ' . $name); }
    }
    if ($symlinkName !== null) {
        if (!$zip->setExternalAttributesName($symlinkName, ZipArchive::OPSYS_UNIX, (0120777 << 16))) {
            throw new RuntimeException('Could not mark symlink entry.');
        }
    }
    $zip->close();
    return $path;
}

function restore_test_call(string $zipPath): int
{
    return restore_upload_archive([
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($zipPath),
        'tmp_name' => $zipPath,
    ]);
}

function restore_test_reject(string $zipPath, string $label): void
{
    try {
        restore_test_call($zipPath);
        throw new RuntimeException('Expected rejection: ' . $label);
    } catch (RuntimeException $error) {
        if (str_starts_with($error->getMessage(), 'Expected rejection:')) { throw $error; }
        echo "PASS: rejected {$label}\n";
    }
}

try {
    $valid = restore_test_zip([
        'uploads/' => '',
        'uploads/department/attachment.txt' => 'safe attachment',
    ]);
    try {
        restore_test_assert(restore_test_call($valid) === 1, 'restores a valid legacy-compatible upload archive');
        restore_test_assert(
            file_get_contents($testRoot . '/storage/uploads/department/attachment.txt') === 'safe attachment',
            'restored file content matches the archive'
        );
    } finally { @unlink($valid); }

    $traversal = restore_test_zip([
        'uploads/good.txt' => 'must not be written',
        'uploads/../escape.txt' => 'outside',
    ]);
    try {
        restore_test_reject($traversal, 'parent traversal');
        restore_test_assert(
            !file_exists($testRoot . '/storage/uploads/good.txt'),
            'preflight rejects the whole archive before writing any file'
        );
        restore_test_assert(
            !file_exists($testRoot . '/storage/escape.txt'),
            'parent traversal does not create a file outside uploads'
        );
    } finally { @unlink($traversal); }

    $duplicate = restore_test_zip([
        'uploads/Readme.txt' => 'one',
        'uploads/readme.txt' => 'two',
    ]);
    try { restore_test_reject($duplicate, 'case-insensitive duplicate paths'); }
    finally { @unlink($duplicate); }

    $collision = restore_test_zip([
        'uploads/node' => 'file',
        'uploads/node/child.txt' => 'child',
    ]);
    try { restore_test_reject($collision, 'file/directory collision'); }
    finally { @unlink($collision); }

    $symlink = restore_test_zip(['uploads/link' => 'target'], 'uploads/link');
    try { restore_test_reject($symlink, 'symbolic link'); }
    finally { @unlink($symlink); }

    echo "All backup upload restore archive tests passed.\n";
} finally {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() && !$item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($testRoot);
}
