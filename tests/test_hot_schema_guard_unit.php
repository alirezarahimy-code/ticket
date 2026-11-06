<?php
declare(strict_types=1);

// Offline behavioural tests: no real configuration, database or server data.
// تابع ticket_hot_schema_ensure() از متن فایل بیرون کشیده و با وابستگی‌های
// ساختگی اجرا می‌شود؛ هدف این است که «ترتیب $ready نسبت به گارد» با اجرای
// واقعی سنجیده شود، نه با خواندن متن.
const APP_ROOT = __DIR__ . '/..';

$failures = 0;
function check_hot(bool $condition, string $label): void
{
    global $failures;
    if (!$condition) { $failures++; echo "not ok - $label\n"; } else { echo "ok - $label\n"; }
}

$GLOBALS['in_txn'] = false;
$GLOBALS['ddl'] = [];
$GLOBALS['warnings'] = [];
$GLOBALS['shutdowns'] = 0;

function db_in_transaction(): bool { return (bool) $GLOBALS['in_txn']; }
function db_add_missing_column(string $t, string $c, string $ddl): void { $GLOBALS['ddl'][] = "$t.$c"; }
function db_ensure_enum_values(string $t, string $c, array $v, string $d): void { $GLOBALS['ddl'][] = "enum:$t.$c"; }
function system_log(string $level, string $channel, string $message): void { $GLOBALS['warnings'][] = "$level|$channel"; }
function register_shutdown_function_stub(callable $fn): void { $GLOBALS['shutdowns']++; $GLOBALS['pending'][] = $fn; }

// تابع هدف را از فایل واقعی بیرون می‌کشیم تا نسخهٔ در حال اجرا همانِ مخزن باشد.
$source = file_get_contents(APP_ROOT . '/lib/tickets-sla.php');
$start = strpos($source, 'function ticket_hot_schema_ensure(');
$end = strpos($source, "\nfunction ", $start + 10);
$body = substr($source, $start, $end - $start);
// فقط ثبت shutdown به نسخهٔ قابل‌مشاهده تغییر می‌کند؛ بقیهٔ متن دست‌نخورده است.
$body = str_replace('register_shutdown_function(', 'register_shutdown_function_stub(', $body);
eval($body);

// ───── ۱) داخل تراکنش: هیچ DDL نباید اجرا شود ─────
$GLOBALS['in_txn'] = true;
ticket_hot_schema_ensure();
check_hot(count($GLOBALS['ddl']) === 0, 'no schema migration runs while a transaction is open');
check_hot($GLOBALS['shutdowns'] === 1, 'the migration is deferred to the end of the request');
check_hot(count($GLOBALS['warnings']) === 1, 'the deferral is logged exactly once');
check_hot($GLOBALS['warnings'][0] === 'warning|ticket_hot_schema', 'it is logged as a warning on its own channel');

// ───── ۲) فراخوانی دوبارهٔ داخل تراکنش: ثبت تکراری ممنوع ─────
ticket_hot_schema_ensure();
ticket_hot_schema_ensure();
check_hot($GLOBALS['shutdowns'] === 1, 'repeated calls inside the transaction do not pile up shutdown handlers');
check_hot(count($GLOBALS['ddl']) === 0, 'still no migration after repeated calls inside the transaction');

// ───── ۳) دام اصلی: پس از پایان تراکنش مهاجرت باید واقعاً اجرا شود ─────
$GLOBALS['in_txn'] = false;
ticket_hot_schema_ensure();
check_hot(count($GLOBALS['ddl']) > 30, 'once the transaction is over the deferred migration really runs');
check_hot(in_array('tickets.ticket_no', $GLOBALS['ddl'], true), 'the tickets columns are migrated');
check_hot(in_array('users.is_primary_admin', $GLOBALS['ddl'], true), 'the users columns are migrated');
check_hot(in_array('enum:tickets.status', $GLOBALS['ddl'], true), 'enum values are migrated too');

// ───── ۴) پس از موفقیت، دوباره اجرا نمی‌شود ─────
$done = count($GLOBALS['ddl']);
ticket_hot_schema_ensure();
check_hot(count($GLOBALS['ddl']) === $done, 'a completed migration is not repeated on later calls');

echo $failures === 0 ? "\nهمهٔ بررسی‌ها موفق بود.\n" : "\n$failures بررسی شکست خورد.\n";
exit($failures === 0 ? 0 : 1);
