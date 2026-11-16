<?php
declare(strict_types=1);

/**
 * آزمون آفلاینِ «مدیریت خطاهای استخراج» — نسخهٔ ۱.۳۷
 * ============================================================================
 *   php tools/test_scan_errors_offline.php
 *
 * بررسی می‌کند که دو خطای رایج استخراج («سرویس RPC/WMI در دسترس نیست» و «دسترسی رد
 * شد») واقعاً به کد خطا و راه‌حل گام‌به‌گام نگاشت می‌شوند و صفحهٔ اسکن می‌تواند
 * شکست‌ها را گروه‌بندی کند. هیچ اتصال شبکه/دیتابیسی لازم نیست.
 */

$root = dirname(__DIR__);
// domain-scan.php بدون APP_ROOT بی‌صدا خارج می‌شود؛ بدون این تعریف، آزمون
// بدون هیچ خروجی و با کد خروج صفر تمام می‌شد و عملاً هیچ بررسی‌ای انجام نمی‌شد.
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $root);
}
// آزمون کاملاً آفلاین است: setting() (که در حالت عادی از دیتابیس می‌خواند) را
// با مقدار پیش‌فرض جایگزین می‌کنیم تا domain_scan_failure_summary بدون دیتابیس کار کند.
if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        return $default;
    }
}
require $root . '/domain-scan.php';

$pass = 0;
$fail = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo '[PASS] ' . $label . "\n";
    } else {
        $fail++;
        echo '[FAIL] ' . $label . ($detail !== '' ? ' — ' . $detail : '') . "\n";
    }
}

/* ── ۱) کاتالوگ خطاها کامل و عملی است ─────────────────────────────────────── */
$catalog = domain_scan_error_catalog();
check('۱: کاتالوگ خطاها شامل کدهای اصلی است', isset($catalog['rpc_unavailable'], $catalog['access_denied'], $catalog['not_reachable'], $catalog['bad_credentials']));
$thin = [];
foreach ($catalog as $code => $entry) {
    if (trim((string) ($entry['title'] ?? '')) === '' || count((array) ($entry['remedy'] ?? [])) < 2) {
        $thin[] = $code;
    }
}
check('۲: هر کد خطا عنوان و حداقل دو گام راه‌حل دارد', $thin === [], implode(',', $thin));

/* ── ۲) پیام‌های واقعی ویندوز → پیام فارسی → کد خطا ──────────────────────── */
$samples = [
    'rpc_unavailable' => 'The RPC server is unavailable. (Exception from HRESULT: 0x800706BA)',
    'access_denied' => 'Access is denied. (Exception from HRESULT: 0x80070005 (E_ACCESSDENIED))',
    'bad_credentials' => 'Logon failure: unknown user name or bad password.',
    'no_computer_info' => 'NO_COMPUTER_INFO: target returned no computer information (check WMI/DCOM access and firewall).',
    'policy_blocked' => 'File C:\\xampp\\htdocs\\tools\\collect_server_inventory.ps1 cannot be loaded because running scripts is disabled on this system.',
];
$mismatch = [];
foreach ($samples as $expectedCode => $raw) {
    $message = domain_scan_friendly_error($raw);
    $detected = domain_scan_error_code($message);
    if ($detected !== $expectedCode) {
        $mismatch[] = $expectedCode . '→' . $detected . ' (' . $message . ')';
    }
}
check('۳: هر پیام خام ویندوز به کد خطای درست نگاشت می‌شود', $mismatch === [], implode(' | ', $mismatch));

$rpcMessage = domain_scan_friendly_error('The RPC server is unavailable. (0x800706BA)');
$deniedMessage = domain_scan_friendly_error('Access is denied. (0x80070005)');
check('۴: پیام «RPC/WMI» همان جملهٔ آشنای کاربر است', str_contains($rpcMessage, 'سرویس RPC/WMI روی سیستم مقصد در دسترس نیست'), $rpcMessage);
check('۵: پیام «دسترسی رد شد» نقش حساب همین نوبت اسکن را دقیق مشخص می‌کند', str_contains($deniedMessage, 'دسترسی رد شد؛ حسابی که در فرم همین نوبت وارد کرده‌اید باید عضو «Administrators محلی» همان کامپیوتر باشد.'), $deniedMessage);

/* ── ۳) گروه‌بندی شکست‌های یک نوبت ─────────────────────────────────────────── */
$rows = [
    ['hostname' => 'PC-023', 'status' => 'error', 'message' => $rpcMessage],
    ['hostname' => 'PC-024', 'status' => 'error', 'message' => $rpcMessage],
    ['hostname' => 'PC-025', 'status' => 'error', 'message' => $deniedMessage],
    ['hostname' => 'PC-026', 'status' => 'error', 'message' => 'ذخیرهٔ اطلاعات ناموفق: SQLSTATE[22001] Data too long'],
    ['hostname' => 'PC-027', 'status' => 'offline', 'message' => 'در شبکه پاسخ نداد'],
    ['hostname' => 'PC-028', 'status' => 'done', 'message' => '۱۲ فیلد شناسنامه پر شد'],
    ['hostname' => 'PC-029', 'status' => 'pending', 'message' => null],
];
$groups = domain_scan_failure_summary($rows);
check('۶: فقط ردیف‌های خطادار/آفلاین گروه‌بندی می‌شوند (۴ علت برای ۵ ردیف)', count($groups) === 4, (string) count($groups));
check('۷: گروه‌ها بر اساس تعداد نزولی مرتب‌اند', (int) $groups[0]['count'] === 2 && $groups[0]['code'] === 'rpc_unavailable', $groups[0]['code'] . ':' . $groups[0]['count']);
$byCode = [];
foreach ($groups as $group) {
    $byCode[$group['code']] = $group;
}
check('۸: کدهای تشخیص‌داده‌شده درست‌اند', isset($byCode['rpc_unavailable'], $byCode['access_denied'], $byCode['store_failed'], $byCode['not_reachable']), implode(',', array_keys($byCode)));
// نام‌های نمایشی با حالت پیش‌فرض short_lower کوچک می‌شوند (asset_display_name)؛
// پس انتظار تست هم شکل نمایشی است، نه نام خام ردیف.
check('۹: نام سیستم‌های هر گروه ثبت می‌شود و ردیف موفق داخل هیچ گروهی نیست',
    $byCode['rpc_unavailable']['hosts'] === ['pc-023', 'pc-024']
    && !in_array('pc-028', array_merge(...array_map(static fn (array $g): array => $g['hosts'], $groups)), true),
    implode('|', $byCode['rpc_unavailable']['hosts']));
check('۱۰: راه‌حل هر گروه از کاتالوگ می‌آید و خالی نیست', count($byCode['access_denied']['remedy']) >= 3);
check('۱۱: پیام واقعی سیستم در گزارش گروه دیده می‌شود', $byCode['store_failed']['messages'] !== []);

/* ── ۴) استخراج محلی (بدون RPC) و ترتیب پروتکل‌ها ─────────────────────────── */
$localNames = domain_scan_local_names();
check('۱۲: فهرست نام‌های محلی شامل localhost و 127.0.0.1 است',
    in_array('localhost', $localNames, true) && in_array('127.0.0.1', $localNames, true), implode(',', $localNames));
check('۱۳: فهرست نام‌های محلی مقدار خالی یا تکراری ندارد', $localNames === array_values(array_unique($localNames)) && !in_array('', $localNames, true));

$source = (string) file_get_contents($root . '/domain-scan.php');
check('۱۴: مسیر محلی (بدون ComputerName) در تابع استخراج هست', str_contains($source, "\$isLocal = in_array(strtolower(\$host), \$localNames, true)"));
check('۱۵: ترتیب پروتکل از تنظیمات به اسکریپت پاس داده می‌شود', str_contains($source, "ITSM_SCAN_PROTOCOLS"));
$ps1 = (string) file_get_contents($root . '/tools/collect_server_inventory.ps1');
check('۱۶: اسکریپت PowerShell ترتیب پروتکل را می‌خواند و پیش‌فرض Dcom,Wsman می‌ماند',
    str_contains($ps1, 'ITSM_SCAN_PROTOCOLS') && str_contains($ps1, "@('Dcom', 'Wsman')"));

/* ── ۵) اکشن «تلاش دوباره» و کارت راه‌حل در رابط کاربری ───────────────────── */
$indexSource = (string) file_get_contents($root . '/index.php');
$actionsSource = (string) file_get_contents($root . '/actions/asset-actions.php');
// فرم تلاش دوباره در index.php است و هندلرش (domain_scan_retry_failed) در actions/asset-actions.php.
check('۱۷: اکشن تلاش دوبارهٔ ناموفق‌ها در index.php و هندلرش در actions ثبت شده است', str_contains($indexSource, "domain_scan_retry") && str_contains($actionsSource, 'domain_scan_retry_failed'));
check('۱۸: کارت «دلایل شکست استخراج و راه‌حل» در صفحهٔ اسکن رندر می‌شود', str_contains($indexSource, 'دلایل شکست استخراج و راه‌حل') && str_contains($indexSource, 'domain_scan_failure_summary'));
check('۱۹: ترتیب پروتکل در تنظیمات قابل انتخاب است', str_contains($indexSource, 'domain_scan_protocols') && str_contains($indexSource, 'اول WinRM، بعد DCOM'));
check('۲۰: «تلاش دوباره» فقط ردیف‌های خطادار را برمی‌دارد (نه آفلاین‌ها و نه موفق‌ها)',
    str_contains($source, 'WHERE run_id = ? AND status = "error" ORDER BY id ASC'));

/* ── ۶) خطای سرتاسری «هیچ‌کدام از پروتکل‌ها کار نکرد» و اعتبارنامهٔ منقضی ───── */
// ALL_PROTOCOLS_FAILED ممکن است در متن خود «Access is denied» هم داشته باشد؛
// باید به کد سرتاسری all_protocols نگاشت شود، نه access_denied.
$allProtocolsMessage = domain_scan_friendly_error('ALL_PROTOCOLS_FAILED: Dcom: Access is denied. (0x80070005); Wsman: The RPC server is unavailable. (0x800706BA)');
check('۲۱: ALL_PROTOCOLS_FAILED به کد سرتاسری all_protocols نگاشت می‌شود (نه access_denied)',
    domain_scan_error_code($allProtocolsMessage) === 'all_protocols', $allProtocolsMessage);
check('۲۲: کاتالوگ کدهای all_protocols و credentials_missing را با راه‌حل کامل دارد',
    isset($catalog['all_protocols'], $catalog['credentials_missing'])
    && count($catalog['all_protocols']['remedy']) >= 3
    && count($catalog['credentials_missing']['remedy']) >= 3);
$credentialsMessage = 'اعتبارنامهٔ امن این نوبت در دسترس نیست؛ دوباره وارد کنید.';
check('۲۳: پیام «اعتبارنامهٔ امن این نوبت» به کد credentials_missing نگاشت می‌شود',
    domain_scan_error_code($credentialsMessage) === 'credentials_missing', domain_scan_error_code($credentialsMessage));

/* ── ۷) آماده‌سازی راه‌دور مقصد (فعال‌سازی WinRM بدون GPO) ──────────────────── */
check('۲۴: کاتالوگ کدهای آماده‌سازی (SMB/تسک/بررسی) را با راه‌حل کامل دارد',
    isset($catalog['prepare_smb_unreachable'], $catalog['prepare_task_failed'], $catalog['prepare_verify_failed'])
    && count($catalog['prepare_smb_unreachable']['remedy']) >= 2
    && count($catalog['prepare_task_failed']['remedy']) >= 2
    && count($catalog['prepare_verify_failed']['remedy']) >= 2);
$prepareSamples = [
    'prepare_smb_unreachable' => 'PREPARE_SMB_UNREACHABLE: TCP 445 on PC-023 is not reachable',
    'prepare_task_failed' => 'PREPARE_TASK_FAILED: schtasks /create failed: Access is denied.',
    'prepare_verify_failed' => 'PREPARE_VERIFY_FAILED: CIM session could not be established after preparation',
];
$prepareMismatch = [];
foreach ($prepareSamples as $expectedCode => $raw) {
    $detected = domain_scan_error_code(domain_scan_friendly_error($raw));
    if ($detected !== $expectedCode) {
        $prepareMismatch[] = $expectedCode . '→' . $detected;
    }
}
check('۲۵: خطاهای PREPARE_* اسکریپت آماده‌سازی به کدهای درست نگاشت می‌شوند', $prepareMismatch === [], implode(' | ', $prepareMismatch));

$preparePanelScript = (string) file_get_contents($root . '/tools/prepare_targets.ps1');
$prepareTargetScript = (string) file_get_contents($root . '/tools/prepare_remote_target.ps1');
check('۲۶: اسکریپت سمت پنل (SMB، schtasks، بررسی CIM، پاک‌سازی) کامل است',
    str_contains($preparePanelScript, 'PREPARE_SMB_UNREACHABLE')
    && str_contains($preparePanelScript, 'schtasks.exe')
    && str_contains($preparePanelScript, 'New-CimSession')
    && str_contains($preparePanelScript, 'ITSM_SCAN_USER')
    && str_contains($preparePanelScript, 'prepare_remote_target.ps1'));
check('۲۷: اسکریپت سمت مقصد WinRM و قواعد فایروال WMI را فعال می‌کند',
    str_contains($prepareTargetScript, 'Enable-PSRemoting')
    && str_contains($prepareTargetScript, 'Windows Management Instrumentation (WMI-In)')
    && str_contains($prepareTargetScript, 'prepare_result.json'));

$actionsSource = (string) file_get_contents($root . '/actions/asset-actions.php');
$permissionsSource = (string) file_get_contents($root . '/permissions.php');
check('۲۸: اکشن domain_scan_prepare با مجوز asset.remote_prepare و ممیزی ثبت شده است',
    str_contains($actionsSource, "domain_scan_prepare")
    && str_contains($actionsSource, "require_permission('asset.remote_prepare')")
    && str_contains($actionsSource, 'domain_targets_prepared')
    && str_contains($permissionsSource, "'asset.remote_prepare'")
    && str_contains($permissionsSource, 'perm_edit_v5'));
check('۲۹: دکمهٔ «آماده‌سازی سیستم‌های مقصد» در صفحهٔ اسکن رندر می‌شود',
    str_contains($indexSource, 'آماده‌سازی سیستم‌های مقصد (فعال‌سازی WinRM)')
    && str_contains($indexSource, 'domain_scan_prepare_preflight')
    && str_contains($indexSource, '__prepareHosts'));

/* ── ۸) روش دوم: انتشار خودکار GPO از داخل پنل (بدون GPMC دستی) ─────────────── */
$gpoScript = (string) file_get_contents($root . '/tools/prepare_domain_gpo.ps1');
check('۳۰: اسکریپت GPO بدون نیاز به RSAT روی پنل کار می‌کند (Invoke-Command روی DC)',
    str_contains($gpoScript, 'Invoke-Command')
    && str_contains($gpoScript, 'DomainControllers')
    && str_contains($gpoScript, 'Import-Module GroupPolicy'));
check('۳۱: اسکریپت GPO سیاست WinRM و قواعد فایروال WMI/RPC/WinRM را می‌نویسد',
    str_contains($gpoScript, 'AllowAutoConfig')
    && str_contains($gpoScript, 'RPC-EPMap')
    && str_contains($gpoScript, 'LPort=5985')
    && str_contains($gpoScript, 'LPort=49152-65535'));
check('۳۲: اسکریپت GPO لینک می‌کند، gpupdate اجباری می‌زند و حالت Unlink دارد',
    str_contains($gpoScript, 'New-GPLink')
    && str_contains($gpoScript, 'Invoke-GPUpdate')
    && str_contains($gpoScript, 'Remove-GPLink')
    && str_contains($gpoScript, 'PREPARE_GPO_LINK_FAILED'));
$gpoSamples = [
    'prepare_gpo_not_domain' => 'PREPARE_GPO_NOT_DOMAIN: The panel server is not a domain member',
    'prepare_gpo_failed' => 'PREPARE_GPO_FAILED: New-GPO failed: Access is denied.',
    'prepare_gpo_link_failed' => 'PREPARE_GPO_LINK_FAILED: New-GPLink failed: Access is denied.',
];
$gpoMismatch = [];
foreach ($gpoSamples as $expectedCode => $raw) {
    $detected = domain_scan_error_code(domain_scan_friendly_error($raw));
    if ($detected !== $expectedCode) {
        $gpoMismatch[] = $expectedCode . '→' . $detected;
    }
}
check('۳۳: خطاهای PREPARE_GPO_* به کدهای درست نگاشت می‌شوند', $gpoMismatch === [], implode(' | ', $gpoMismatch));
check('۳۴: کاتالوگ خطاهای GPO (عضو نبودن دامنه/ساخت/لینک) را با راه‌حل کامل دارد',
    isset($catalog['prepare_gpo_not_domain'], $catalog['prepare_gpo_failed'], $catalog['prepare_gpo_link_failed'])
    && count($catalog['prepare_gpo_failed']['remedy']) >= 2
    && count($catalog['prepare_gpo_link_failed']['remedy']) >= 2);
check('۳۵: اکشن انتشار و لغو GPO با مجوز و ممیزی ثبت شده است',
    str_contains($actionsSource, 'domain_scan_prepare_gpo')
    && str_contains($actionsSource, "domain_scan_prepare_gpo_unlink")
    && str_contains($actionsSource, 'domain_gpo_published')
    && str_contains($actionsSource, 'domain_gpo_unlinked')
    && str_contains($actionsSource, 'domain_scan_prepare_domain_gpo'));
check('۳۶: کارت «روش دوم: GPO خودکار» در صفحهٔ اسکن رندر می‌شود',
    str_contains($indexSource, 'روش دوم: ساخت و اعمال خودکار Group Policy در کل دامنه')
    && str_contains($indexSource, 'domain_scan_prepare_gpo')
    && str_contains($indexSource, 'gpo-form'));
check('۳۷: تسک آماده‌سازی با /ru SYSTEM ساخته می‌شود (مستقل از لاگین کاربر)',
    str_contains($preparePanelScript, '/ru SYSTEM'));

echo "\n" . ($fail === 0
    ? 'همهٔ ' . $pass . " بررسی مدیریت خطای استخراج موفق بود.\n"
    : $fail . ' بررسی ناموفق از ' . ($pass + $fail) . " مورد.\n");
exit($fail === 0 ? 0 : 1);
