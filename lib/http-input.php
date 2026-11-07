<?php
declare(strict_types=1);

/**
 * Shared HTTP input helpers.
 *
 * These functions were previously declared by index.php. Keeping them in the
 * common bootstrap makes POST handlers usable from dedicated module entry
 * points without loading the full legacy page controller.
 */

if (!function_exists('post_value')) {
    function post_value(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }
}

if (!function_exists('valid_choice')) {
    function valid_choice(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }
}
