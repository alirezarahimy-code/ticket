<?php
declare(strict_types=1);

/** Offline path-safety tests for the update ZIP validator. */
const APP_ROOT = __DIR__ . '/..';
function db() { throw new RuntimeException('Database is not used by this test.'); }
function save_audit(...$args): void {}
function app_error_log(string $message): bool { return true; }
function cfg(string $key, mixed $default = null): mixed { return $default; }
require APP_ROOT . '/system-update.php';

$cases = [
    'manifest root file' => ['manifest.json', true],
    'migration file' => ['migrations/024-central-logging.sql', true],
    'files root entry' => ['files/VERSION', true],
    'nested asset' => ['files/assets/app.js', true],
    'directory entry' => ['files/', true],
    'nested directory entry' => ['files/assets/', true],
    'parent traversal' => ['files/../config.php', false],
    'dot segment' => ['files/./app.js', false],
    'absolute unix path' => ['/etc/passwd', false],
    'absolute windows path' => ['C:/Windows/win.ini', false],
    'backslash path' => ['files\\..\\config.php', false],
    'empty path segment' => ['files//app.js', false],
    'unsupported root' => ['README.md', false],
    'windows reserved device' => ['files/CON', false],
    'windows trailing dot' => ['files/app.', false],
    'windows trailing space' => ['files/app ', false],
    'invalid windows character' => ['files/app?.php', false],
];

$failed = 0;
foreach ($cases as $label => [$path, $expected]) {
    $actual = system_update_archive_path_is_safe($path);
    $ok = $actual === $expected;
    echo ($ok ? 'PASS' : 'FAIL') . ' ' . $label . ' (' . $path . ')' . PHP_EOL;
    if (!$ok) { $failed++; }
}
echo $failed === 0 ? "All archive path tests passed.\n" : "$failed archive path test(s) failed.\n";
exit($failed === 0 ? 0 : 1);
