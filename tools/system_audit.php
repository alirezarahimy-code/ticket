<?php

declare(strict_types=1);

/**
 * اجرای ممیزی سلامت سامانه از خط فرمان.
 *
 * این فایل فقط یک پوستهٔ نازک روی lib/logging-audit.php است. منطق بررسی
 * یک‌جا و فقط یک‌جا زندگی می‌کند، تا نسخهٔ خط فرمان و نسخهٔ کرون هرگز از هم
 * جدا نیفتند.
 *
 *   php tools/system_audit.php              گزارش خوانا
 *   php tools/system_audit.php --json       خروجی ماشینی
 *   php tools/system_audit.php --check      کد خروج ۱ در صورت مشکل
 *   php tools/system_audit.php --update-baseline
 *   php tools/system_audit.php --no-db      فقط بررسی‌های بی‌نیاز از دیتابیس
 *
 * در CI با --no-db اجرا می‌شود چون آنجا پایگاه دادهٔ واقعی وجود ندارد.
 * روی سرور، همین بررسی‌ها به‌علاوهٔ بررسی‌های دیتابیس از دل
 * cron/check_logging_health.php اجرا می‌شوند و در سیستم لاگ ثبت می‌گردند.
 */

$options = $argv ?? [];
$wantJson = in_array('--json', $options, true);
$wantCheck = in_array('--check', $options, true);
$updateBaseline = in_array('--update-baseline', $options, true);
$noDb = in_array('--no-db', $options, true);

if ($noDb) {
    // بدون bootstrap کامل: فقط ثابت ریشه لازم است.
    define('APP_ROOT', dirname(__DIR__));
    require_once APP_ROOT . '/lib/logging-audit.php';
    $skip = ['db_schema_tables', 'db_schema_columns', 'db_pending_migrations', 'unresolved_issues'];
    $results = [];
    foreach (logging_audit_checks() as $callable) {
        $probe = $callable === 'logging_audit_check_filesystem_writable'
            || $callable === 'logging_audit_check_spool_backlog'
            || $callable === 'logging_audit_check_worker_heartbeats';
        if (!$probe) {
            continue;
        }
        $result = $callable();
        $result['metric'] = count($result['findings']);
        $result['structural'] = in_array($result['key'], logging_audit_structural_checks(), true);
        $results[] = $result;
    }
    $run = ['checks' => $results, 'metrics' => array_column($results, 'metric', 'key'), 'regressions' => []];
} else {
    require_once dirname(__DIR__) . '/bootstrap.php';
    require_once APP_ROOT . '/lib/logging-audit.php';
    $alerts = [];
    $run = logging_audit_run_and_record($updateBaseline, $alerts);
}

if ($wantJson) {
    echo json_encode($run, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
} else {
    echo "گزارش ممیزی سلامت سامانه\n";
    echo str_repeat('=', 48), "\n\n";
    foreach ($run['checks'] as $check) {
        $mark = $check['metric'] === 0 ? '✅' : (!empty($check['structural']) ? '🔴' : '⚠️ ');
        printf("%s %-42s %d یافته\n", $mark, $check['title'], $check['metric']);
        foreach (array_slice($check['findings'], 0, 8) as $finding) {
            echo '      - ', $finding['where'], ' — ', $finding['detail'], "\n";
        }
        if ($check['metric'] > 8) {
            echo '      … و ', $check['metric'] - 8, " مورد دیگر\n";
        }
    }
    echo "\n";
    if (!empty($run['structural_problems'])) {
        echo "🔴 ناسازگاری ساختاری: ", implode('، ', $run['structural_problems']), "\n";
    }
    if (!empty($run['regressions'])) {
        foreach ($run['regressions'] as $key => $info) {
            echo "⚠️  پس‌رفت در {$key}: {$info['baseline']} → {$info['metric']}\n";
        }
    }
    if (empty($run['structural_problems']) && empty($run['regressions'])) {
        echo "🟢 هیچ ناسازگاری ساختاری و هیچ پس‌رفتی نیست.\n";
    }
}

if ($wantCheck) {
    $bad = count($run['structural_problems'] ?? []) + count($run['regressions'] ?? []);
    exit($bad > 0 ? 1 : 0);
}
exit(0);
