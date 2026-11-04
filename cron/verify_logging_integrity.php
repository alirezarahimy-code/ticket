<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit("Not found\n"); }

require dirname(__DIR__) . '/lib/logging-integrity.php';

$key = (string) getenv('LOGGING_INTEGRITY_KEY');
$archive = (string) ($argv[1] ?? '');
if (strlen($key) < 32 || $archive === '' || !is_file($archive) || !is_file($archive . '.manifest.json')) {
    fwrite(STDERR, "Usage: php verify_logging_integrity.php <archive.jsonl> (with LOGGING_INTEGRITY_KEY set)\n"); exit(2);
}
$manifest = json_decode((string) file_get_contents($archive . '.manifest.json'), true);
if (!is_array($manifest)) { fwrite(STDERR, "Invalid integrity manifest.\n"); exit(1); }
$format = (int) ($manifest['format'] ?? 0);
if ($format === 3) {
    $chainError = logging_integrity_verify_supplement_chain($archive, $key);
    if ($chainError !== null) { fwrite(STDERR, $chainError . "\n"); exit(1); }
    fwrite(STDOUT, sprintf("[PASS] %d late-arrival records verified; supplement chain is linked to the immutable daily snapshot.\n", (int) $manifest['records']));
    exit(0);
}
$dailyError = logging_integrity_verify_daily_manifest_error($archive, $manifest, $key);
if ($dailyError !== null) {
    fwrite(STDERR, $dailyError . "\n"); exit(1);
}
fwrite(STDOUT, sprintf("[PASS] %d records verified; daily chain and HMAC are valid (format %d).\n", (int) $manifest['records'], $format));
