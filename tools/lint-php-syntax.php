<?php
declare(strict_types=1);

/** Parse all NUL-separated PHP paths from stdin in one PHP process. */
$paths = explode("\0", (string) stream_get_contents(STDIN));
$files = array_values(array_filter($paths, static fn (string $path): bool => $path !== ''));
$failed = 0;
foreach ($files as $file) {
    try {
        $source = file_get_contents($file);
        if ($source === false) {
            throw new RuntimeException('cannot read file');
        }
        token_get_all($source, TOKEN_PARSE);
    } catch (Throwable $error) {
        fwrite(STDERR, $file . ': ' . $error->getMessage() . PHP_EOL);
        $failed++;
    }
}
if ($failed > 0) {
    fwrite(STDERR, $failed . ' of ' . count($files) . ' PHP files failed syntax parsing.' . PHP_EOL);
    exit(1);
}
echo 'PHP syntax OK for ' . count($files) . ' files.' . PHP_EOL;
