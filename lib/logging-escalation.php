<?php
declare(strict_types=1);

/**
 * تشدید شدت بر اساس تکرار.
 * هر fingerprint در یک پنجرهٔ زمانی شمارش می‌شود.
 * این تابع فقط «برچسب» را طبق آستانه‌های زیر تعیین می‌کند؛
 * ترفیعِ واقعیِ سطح (warning→error و error→critical) در lib/logging-csrf.php انجام می‌شود.
 * آستانه‌ها (برای برچسب):
 *   ≥۱ بار       → مشکل جزئی
 *   ≥۳ بار       → مشکل تکراری — نیاز به بررسی
 *   ≥۱۰ بار      → بحرانی — احتمالاً سرویس قطع شده
 *   ≥۵۰ بار      → فاجعه — بررسی فوری لازم است (برچسب؛ alert خودکار ارسال نمی‌شود)
 * نکته: شمارش «پیش از» این رویداد انجام می‌شود؛ ردیف‌های قدیمی‌تر retroactively تغییر نمی‌کنند.
 */

defined('APP_ROOT') || exit;

function logging_escalation_thresholds(): array
{
    return [
        'minor'      => ['count' => 1,  'level' => 'warning',  'label' => 'مشکل جزئی'],
        'repeat'     => ['count' => 3,  'level' => 'error',    'label' => 'مشکل تکراری — نیاز به بررسی'],
        'critical'   => ['count' => 10, 'level' => 'critical', 'label' => 'بحرانی — احتمالاً سرویس قطع شده'],
        'disaster'   => ['count' => 50, 'level' => 'critical', 'label' => 'فاجعه — بررسی فوری لازم است'],
    ];
}

/**
 * شمارش تکرار یک fingerprint در پنجرهٔ زمانی (پیش‌فرض ۱ ساعت).
 * برمی‌گرداند: [شمار، سطح، برچسب]
 */
function logging_escalation_count(string $fingerprint, int $windowSeconds = 3600): array
{
    $count = 0;
    try {
        if (function_exists('db_table_exists') && db_table_exists('system_logs')) {
            $stmt = db()->prepare("SELECT COUNT(*) FROM system_logs WHERE fingerprint = ? AND created_at >= NOW() - INTERVAL ? SECOND");
            $stmt->execute([$fingerprint, $windowSeconds]);
            $count = (int) $stmt->fetchColumn();
        }
    } catch (Throwable) {
        // دیتابیس قطع است — شمارش صفر می‌ماند
    }

    $thresholds = logging_escalation_thresholds();
    $level = 'warning';
    $label = 'مشکل جزئی';
    foreach ($thresholds as $t) {
        if ($count >= $t['count']) {
            $level = $t['level'];
            $label = $t['label'];
        }
    }

    return ['count' => $count, 'level' => $level, 'label' => $label];
}

/**
 * بررسی می‌کند آیا یک fingerprint در پنجرهٔ زمانی از آستانهٔ بحرانی گذشته یا نه.
 */
function logging_escalation_is_critical(string $fingerprint, int $windowSeconds = 3600): bool
{
    return logging_escalation_count($fingerprint, $windowSeconds)['level'] === 'critical';
}

/**
 * جمع‌بندی هوشمند: خلاصهٔ آخرین X دقیقه.
 * برمی‌گرداند آرایه‌ای از خلاصه‌ها برای نمایش در داشبورد.
 */
function logging_smart_summary(int $minutes = 60): array
{
    $summary = ['errors' => [], 'warnings' => [], 'total' => 0];
    try {
        if (!function_exists('db_table_exists') || !db_table_exists('system_logs')) return $summary;
        $stmt = db()->prepare(
            "SELECT fingerprint, code, context, level, COUNT(*) as cnt
             FROM system_logs
             WHERE created_at >= NOW() - INTERVAL ? MINUTE
             AND level IN ('error','warning','critical')
             GROUP BY fingerprint, code, context, level
             ORDER BY cnt DESC, code
             LIMIT 20"
        );
        $stmt->execute([$minutes]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary['total'] = (int) array_sum(array_column($rows, 'cnt'));
        foreach ($rows as $row) {
            $bucket = $row['level'] === 'critical' ? 'errors' : ($row['level'] === 'error' ? 'errors' : 'warnings');
            $summary[$bucket][] = [
                'code' => (string) $row['code'],
                'context' => (string) $row['context'],
                'count' => (int) $row['cnt'],
                'level' => (string) $row['level'],
            ];
        }
    } catch (Throwable) {
    }
    return $summary;
}