<?php
declare(strict_types=1);

/**
 * bootstrap.php — هستهٔ مشترک سامانه (بارکنندهٔ ماژول‌های lib/ از نسخهٔ ۱.۶۲.۲۰).
 *
 * نقشهٔ خانواده‌های توابع و محل هر خانواده (جستجو با «function نام»):
 *   lib/db-core.php          پیکربندی (cfg)، PDO و db_*، نرخ ورود/API، نسخه، تم، redirect
 *   lib/logging-csrf.php     flash، لاگ سیستمی system_*، ثبت خطا، CSRF
 *   lib/auth-scope.php       current_user، require_*، دامنهٔ دید ticket_scope/asset_scope
 *   lib/settings-effects.php setting، افکت‌های triangle_* و particle_*، نقش‌های کلیدی
 *   lib/ldap.php             پیکربندی و همگام‌سازی دامین ldap_*
 *   lib/tickets-sla.php      شمارهٔ درخواست ticket_number_*، تاریخ شمسی jalali_*، SLA/OLA
 *   lib/notify-users.php     اعلان‌ها notify_* و notification_*، ورود دامین، upsert کاربران
 *   lib/activity-audit.php   ثبت فعالیت activity_*، حسابرسی save_audit، رویداد درخواست
 *
 * ترتیب require پایین عیناً ترتیب قدیمی تعریف‌ها در همین فایل است؛ تغییر ترتیب ممنوع.
 * نقشهٔ کامل معماری: docs/ARCHITECTURE-FA.md
 */

const APP_ROOT = __DIR__;

require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/session_bootstrap.php';
app_session_start();

$configFile = (string) (getenv('ITSM_CONFIG_PATH') ?: APP_ROOT . '/config.php');
if (!is_file($configFile)) {
    header('Location: install.php');
    exit;
}

$config = require $configFile;
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$cspNonce = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');


// ───── هسته، پیکربندی، HTTP امن و پایگاه داده (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/db-core.php';

// ───── پیام‌های فلش، لاگ سیستمی و CSRF (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/logging-csrf.php';

// ───── نشست کاربر، سطوح دسترسی و دامنهٔ دید (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/auth-scope.php';

// ───── تنظیمات، افکت‌های نمایشی و نقش‌های کلیدی (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/settings-effects.php';

// ───── پیکربندی و همگام‌سازی دامین (LDAP) (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/ldap.php';

// ───── شمارهٔ درخواست، تقویم شمسی و SLA/OLA (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/tickets-sla.php';

// ───── اعلان‌ها، ورود دامین و کاربران (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/notify-users.php';

// ───── ثبت فعالیت، حسابرسی و رویداد درخواست‌ها (شرح در سرآیند همان فایل) ─────
require __DIR__ . '/lib/activity-audit.php';

