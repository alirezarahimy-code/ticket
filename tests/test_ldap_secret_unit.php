<?php
declare(strict_types=1);

// Offline behavioural tests: no real configuration, database or server data.
// توجه: در این محیط افزونهٔ openssl در دسترس نیست، پس ldap_secret_key() همان
// مسیر «کلید موجود نیست» را می‌رود. دقیقاً همان مسیری که در نصب‌های بدون
// app.key اجرا می‌شود و شکستنش یعنی از کار افتادن ورود دامینی پس از ارتقا.
const APP_ROOT = __DIR__ . '/..';

$failures = 0;
function check_ldap(bool $condition, string $label): void
{
    global $failures;
    if (!$condition) { $failures++; echo "not ok - $label\n"; } else { echo "ok - $label\n"; }
}

$GLOBALS['fake_cfg'] = [];
function cfg(string $key, mixed $default = null): mixed { return $GLOBALS['fake_cfg'][$key] ?? $default; }
$GLOBALS['logged'] = [];
function system_log(string $level, string $channel, string $message): void { $GLOBALS['logged'][] = $level . '|' . $channel; }
function setting(string $key, ?string $default = null): ?string { return $GLOBALS['fake_settings'][$key] ?? $default; }

require APP_ROOT . '/lib/ldap.php';

// ───── سازگاری رو به عقب: نصبی که app.key ندارد ─────
check_ldap(ldap_secret_key() === null, 'no app key yields no encryption key instead of throwing');
check_ldap(ldap_secret_encrypt('Str0ng-AD-Pass') === 'Str0ng-AD-Pass', 'without a key the password is stored exactly as before');
check_ldap(ldap_secret_encrypt('') === '', 'an empty password stays empty and is never wrapped');

// ───── مقدار قدیمیِ متن ساده باید همچنان خوانده شود ─────
check_ldap(ldap_secret_decrypt('Str0ng-AD-Pass') === 'Str0ng-AD-Pass', 'legacy plaintext value is returned untouched');
check_ldap(ldap_secret_decrypt('') === '', 'empty stored value stays empty');
check_ldap(ldap_secret_decrypt('enc') === 'enc', 'a plaintext password that merely starts with enc is not mistaken for an envelope');
check_ldap(ldap_secret_decrypt('encrypted-by-hand') === 'encrypted-by-hand', 'only the exact enc:v1: prefix marks an envelope');

// ───── شکست بسته: پاکت غیرقابل رمزگشایی هرگز به سرور دامین نمی‌رود ─────
$GLOBALS['logged'] = [];
check_ldap(ldap_secret_decrypt('enc:v1:' . base64_encode('too-short')) === '', 'a truncated envelope fails closed');
check_ldap(ldap_secret_decrypt('enc:v1:!!!not-base64!!!') === '', 'a malformed envelope fails closed');
check_ldap(ldap_secret_decrypt('enc:v1:') === '', 'an empty envelope fails closed');
check_ldap(count($GLOBALS['logged']) === 3, 'every failed decryption is logged for the administrator');
check_ldap($GLOBALS['logged'][0] === 'error|ldap', 'the failure is logged at error level on the ldap channel');

// ───── نقطهٔ خواندن: ldap_cfg باید از همین مسیر رد شود ─────
$GLOBALS['fake_settings'] = ['ldap_bind_password' => 'Plain-Legacy-Pass'];
check_ldap(ldap_cfg('bind_password', '') === 'Plain-Legacy-Pass', 'ldap_cfg returns a legacy plaintext password unchanged');
$GLOBALS['fake_settings'] = ['ldap_bind_password' => 'enc:v1:' . base64_encode('broken')];
check_ldap(ldap_cfg('bind_password', '') === '', 'ldap_cfg never hands an undecryptable envelope to the domain server');
$GLOBALS['fake_settings'] = ['ldap_host' => 'dc01.example.local'];
check_ldap(ldap_cfg('host', '') === 'dc01.example.local', 'other ldap settings are not touched by the secret path');

echo $failures === 0 ? "\nهمهٔ بررسی‌ها موفق بود.\n" : "\n$failures بررسی شکست خورد.\n";
exit($failures === 0 ? 0 : 1);
