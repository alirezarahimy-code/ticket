<?php

declare(strict_types=1);

/**
 * actions/asset-actions.php — ۱۰ اکشن POST اموال، دارایی‌ها و اسکن دامین (فاز ۲ بازسازی، برش ۳ — نسخهٔ ۱.۶۲.۱۳).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   collect_inventory         جمع‌آوری اطلاعات سخت‌افزاری دستگاه جاری و ثبت در اموال
 *   asset_profile_selftest    خودآزمایی سازوکار پروفایل دارایی
 *   import_domain_assets      ورود دستگاه‌های دامین به فهرست دارایی‌ها
 *   domain_scan_start         شروع یک دور اسکن دامین
 *   domain_scan_batch         پردازش دستهٔ بعدیِ اسکن در جریان
 *   ping_assets               پینگ گروهی دارایی‌ها (پاسخ AJAX)
 *   domain_scan_retry         تلاش دوباره برای دستگاه‌های خطادار
 *   domain_scan_test_selected آزمایش اتصال/اعتبار روی موارد انتخاب‌شده (بدون ذخیره)
 *   domain_scan_selected      اسکن کامل موارد انتخاب‌شده
 *   save_inventory_form       ذخیرهٔ فرم شناسنامه/اموال دستگاه
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده؛ تنها موارد
 * function/continue داخل بلوک، کلوژر بی‌نام و continue داخل حلقه‌اند که برای include
 * کاملاً بی‌خطرند (اسکن و بازبینی شد).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

         // ───── گروه اکشن: اموال، دارایی‌ها و اسکن دامین ─────
         if ($action === 'collect_inventory') {
              $collectUser = require_permission('asset.extract');
              if (!is_global_ticket_role($collectUser['role']) && user_service_group($collectUser) !== 'it') {
                  throw new RuntimeException('دسترسی به استخراج اطلاعات این سیستم مجاز نیست.');
              }
              $overwrite = post_value('overwrite') === '1';
              if (function_exists('session_write_close')) {
                  @session_write_close();
              }
              $serverInventory = inventory_collect_server_reliable();
              $hardwareScore = inventory_hardware_score($serverInventory);
              $collectError = inventory_last_error();
              if ($hardwareScore === 0) {
                  if (session_status() !== PHP_SESSION_ACTIVE) {
                      session_start();
                  }
                  flash('danger', 'استخراج محلی سخت‌افزار موفق نشد.' . ($collectError !== '' ? ' علت: ' . $collectError : ' دسترسی حساب سرویس وب به PowerShell/WMI را بررسی کنید.') . ' هیچ دادهٔ خالی‌ای به‌عنوان استخراج موفق ذخیره نشد.');
                  redirect('index.php?page=assets');
              }
              $autoValues = asset_profile_auto_values($serverInventory);
              $assetId = inventory_upsert_server_asset($serverInventory, (int) $user['id']);
              $filled = asset_profile_apply_auto($assetId, $autoValues, (int) $user['id'], $overwrite);
              record_asset_history($assetId, 'inventory_collected', 'استخراج محلی اطلاعات این سیستم (میزبان PHP)', (int) $user['id'], null, 'اطلاعات سخت‌افزاری و نرم‌افزاری از میزبان PHP دریافت شد.');
              save_audit((int) $user['id'], 'server_inventory_collected', null, ['asset_id' => $assetId, 'overwrite' => $overwrite, 'hardware_score' => $hardwareScore]);
              if (session_status() !== PHP_SESSION_ACTIVE) {
                  session_start();
              }
              flash('success', 'استخراج محلی میزبان سامانه موفق بود' . ($filled > 0 ? ' و ' . $filled . ' فیلد شناسنامه پر شد.' : '؛ فیلدهای خودکار از قبل پر بودند.'));
              redirect('index.php?page=inventory&id=' . $assetId);
         }

        if ($action === 'asset_profile_selftest') {
            // استخراج آزمایشی: چه چیزی خوانده شد، چه فیلدی پر می‌شود و چه فیلدی چرا نه.
            $diagUser = require_permission('inventory.diagnostics');
            if (function_exists('set_time_limit')) {
                @set_time_limit(300);
            }
            if (function_exists('session_write_close')) {
                @session_write_close();
            }
            $overwrite = post_value('overwrite') === '1';
            $inventory = inventory_collect_server_reliable();
            $values = asset_profile_auto_values($inventory);
            $report = [
                'at' => date('Y-m-d H:i:s'),
                'os' => PHP_OS_FAMILY . ' ' . php_uname('r'),
                'hardware_score' => inventory_hardware_score($inventory),
                'error' => inventory_last_error(),
                'hostname' => (string) ($inventory['hostname'] ?? ''),
                'keys' => [],
                'filled' => 0,
                'empty' => 0,
                'asset_id' => 0,
                'applied' => 0,
                'rows' => [],
            ];
            foreach (['computer', 'bios', 'processor', 'os', 'motherboard', 'memory_modules', 'physical_disks', 'graphics', 'network', 'printers', 'software'] as $key) {
                $report['rows'][] = [$key, (string) count(inventory_rows($inventory['hardware'][$key] ?? $inventory[$key] ?? []))];
            }
            foreach ($values as $key => $value) {
                $text = trim((string) $value);
                if ($text === '') {
                    $report['empty']++;
                    continue;
                }
                $report['filled']++;
                if (count($report['keys']) < 60) {
                    $report['keys'][] = [$key, mb_substr($text, 0, 120)];
                }
            }
            if (inventory_hardware_score($inventory) > 0) {
                $report['asset_id'] = inventory_upsert_server_asset($inventory, (int) $diagUser['id']);
                if ($report['asset_id'] > 0) {
                    $report['applied'] = asset_profile_apply_auto($report['asset_id'], $values, (int) $diagUser['id'], $overwrite);
                }
            }
            $_SESSION['asset_selftest'] = $report;
            system_log('info', 'inventory', 'اجرای تست استخراج شناسنامه', ['filled' => $report['filled'], 'score' => $report['hardware_score'], 'asset_id' => $report['asset_id']]);
            flash($report['filled'] > 0 ? 'success' : 'danger', $report['filled'] > 0
                ? 'استخراج تست موفق بود: ' . $report['filled'] . ' فیلد شناسنامه به‌دست آمد' . ($report['applied'] > 0 ? ' و ' . $report['applied'] . ' فیلد ذخیره شد.' : '.')
                : 'استخراج تست هیچ فیلدی برنگرداند' . ($report['error'] !== '' ? ': ' . $report['error'] : '. جزئیات در همان صفحه آمده است.'));
            redirect('index.php?page=inventory-diagnostics' . ((int) ($_POST['asset_id'] ?? 0) > 0 ? '&id=' . (int) $_POST['asset_id'] : ''));
        }

        if ($action === 'import_domain_assets') {
            $importUser = require_permission('domain.import');
            $importResult = domain_scan_import((int) $importUser['id']);
            save_audit((int) $importUser['id'], 'domain_assets_imported', null, $importResult);
            flash('success', 'کامپیوترهای دامنه خوانده شد: ' . $importResult['created'] . ' سیستم جدید ثبت و ' . $importResult['updated'] . ' سیستم به‌روزرسانی شد (مجموع ' . $importResult['total'] . ').');
            redirect('index.php?page=assets');
        }

        if ($action === 'domain_scan_start') {
            $scanUser = require_permission('asset.remote_extract');
            $scanUsername = trim((string) ($_POST['scan_username'] ?? ''));
            $scanPassword = (string) ($_POST['scan_password'] ?? '');
            domain_scan_validate_credentials($scanUsername, $scanPassword);
            $scanProblem = domain_scan_preflight();
            if ($scanProblem !== null) {
                throw new RuntimeException($scanProblem);
            }
            $scanResult = domain_scan_start((int) $scanUser['id']);
            domain_scan_store_run_credentials((int) $scanResult['run_id'], (int) $scanUser['id'], $scanUsername, $scanPassword);
            unset($scanPassword);
            flash('success', 'اسکن دامنه آغاز شد: ' . $scanResult['total'] . ' کامپیوتر در صف قرار گرفت.');
            redirect('index.php?page=domain-scan&run=' . $scanResult['run_id']);
        }

        if ($action === 'domain_scan_batch') {
            $scanUser = require_permission('asset.remote_extract');
            $runId = (int) ($_POST['run_id'] ?? 0);
            if ($runId <= 0 || domain_scan_run($runId) === null) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'اسکن پیدا نشد.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $runCredentials = domain_scan_run_credentials($runId, (int) $scanUser['id']);
            if ($runCredentials === null) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'اعتبارنامهٔ امن این نوبت در دسترس نیست؛ اسکن را دوباره با واردکردن حساب شروع کنید.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $batchResult = domain_scan_process($runId, (int) ($_POST['limit'] ?? 3), (int) $scanUser['id'], $runCredentials);
            unset($runCredentials);
            $runRow = domain_scan_run($runId);
            if ($runRow && (string) $runRow['status'] === 'finished') {
                domain_scan_clear_run_credentials($runId);
            }
            header('Content-Type: application/json; charset=utf-8');
            $batchRows = [];
            foreach (domain_scan_queue_rows($runId, 1000) as $queueRow) {
                $batchRows[] = [
                    'hostname' => (string) $queueRow['hostname'],
                    'name' => asset_display_name((string) $queueRow['hostname']),
                    'status' => (string) $queueRow['status'],
                    'online' => (int) $queueRow['online'],
                    'message' => (string) ($queueRow['message'] ?? ''),
                ];
            }
            echo json_encode(['ok' => true, 'pending' => $batchResult['pending'], 'processed' => $batchResult['processed'], 'run' => $runRow, 'rows' => $batchRows], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        }

        if ($action === 'ping_assets') {
            $pingUser = require_permission('asset.ping');
            $pingAjax = post_value('ajax') === '1';
            $pingFail = static function (string $message) use ($pingAjax): void {
                if ($pingAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                flash('danger', $message);
                redirect('index.php?page=assets');
            };
            if (!is_global_ticket_role($pingUser['role']) && user_service_group($pingUser) !== 'it') {
                throw new RuntimeException('اجازهٔ پینگ سیستم‌ها را ندارید.');
            }
            if ($pingAjax && function_exists('session_write_close')) {
                @session_write_close();
            }
            $pingIds = $_POST['asset_ids'] ?? [];
            if (!is_array($pingIds)) {
                $pingIds = [];
            }
            $pingIds = array_values(array_unique(array_filter(array_map('intval', $pingIds), static fn (int $id): bool => $id > 0)));
            if ($pingIds === []) {
                $pingFail('هیچ سیستمی برای پینگ انتخاب نشد.');
            }
            if (count($pingIds) > 300) {
                $pingFail('برای هر نوبت حداکثر ۳۰۰ سیستم را پینگ کنید.');
            }
            [$pingScope, $pingScopeParams] = asset_scope($pingUser);
            $placeholders = implode(', ', array_fill(0, count($pingIds), '?'));
            $pingWhere = ($pingScope !== '' ? $pingScope . ' AND ' : 'WHERE ') . 'a.id IN (' . $placeholders . ')';
            $pingQuery = db()->prepare('SELECT a.id, a.hostname, a.ip_address FROM assets a ' . $pingWhere);
            $pingQuery->execute(array_merge($pingScopeParams, $pingIds));
            $targets = [];
            $pingFallbackIps = [];
            foreach ($pingQuery->fetchAll() as $row) {
                $host = trim((string) $row['hostname']);
                $storedIp = trim((string) $row['ip_address']);
                if ($host === '') {
                    $host = $storedIp;
                }
                if ($host !== '') {
                    $targets[(int) $row['id']] = $host;
                    if ($storedIp !== '') {
                        $pingFallbackIps[strtolower($host)] = $storedIp;
                    }
                }
            }
            if ($targets === []) {
                $pingFail('سیستم‌های انتخاب‌شده نام کامپیوتر یا IP ندارند.');
            }
            $pingResults = domain_scan_ping_hosts(array_values($targets), $pingFallbackIps);
            $onlineCount = 0;
            $checkedCount = 0;
            $pingStates = [];
            foreach ($targets as $targetId => $targetHost) {
                $isOnline = (bool) ($pingResults[strtolower($targetHost)] ?? false);
                domain_scan_mark_reachability($targetId, $isOnline, (int) $pingUser['id']);
                $pingStates[$targetId] = $isOnline;
                $checkedCount++;
                if ($isOnline) {
                    $onlineCount++;
                }
            }
            system_log('info', 'domain_scan', 'پینگ دستی سیستم‌ها', ['checked' => $checkedCount, 'online' => $onlineCount]);
            save_audit((int) $pingUser['id'], 'assets_pinged', null, ['checked' => $checkedCount, 'online' => $onlineCount]);
            if ($pingAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => true, 'checked' => $checkedCount, 'online' => $onlineCount, 'states' => $pingStates], JSON_UNESCAPED_UNICODE);
                exit;
            }
            flash('success', 'پینگ انجام شد: ' . $checkedCount . ' سیستم بررسی شد؛ ' . $onlineCount . ' آنلاین و ' . ($checkedCount - $onlineCount) . ' آفلاین.');
            redirect('index.php?page=assets');
        }

        if ($action === 'domain_scan_retry') {
            $retryUser = require_permission('asset.remote_extract');
            $retryRunId = (int) ($_POST['run_id'] ?? 0);
            $scanUsername = trim((string) ($_POST['scan_username'] ?? ''));
            $scanPassword = (string) ($_POST['scan_password'] ?? '');
            domain_scan_validate_credentials($scanUsername, $scanPassword);
            $retry = domain_scan_retry_failed($retryRunId, (int) $retryUser['id']);
            if ($retry['total'] === 0) {
                flash('info', 'در این نوبت سیستم خطاداری نبود؛ چیزی برای تلاش دوباره وجود ندارد.');
                redirect('index.php?page=domain-scan&run=' . $retryRunId);
            }
            domain_scan_store_run_credentials((int) $retry['run_id'], (int) $retryUser['id'], $scanUsername, $scanPassword);
            unset($scanPassword);
            save_audit((int) $retryUser['id'], 'domain_inventory_retry', null, ['from_run' => $retryRunId, 'run_id' => $retry['run_id'], 'total' => $retry['total']]);
            flash('success', $retry['total'] . ' سیستم ناموفق در نوبت تازه #' . $retry['run_id'] . ' دوباره در صف استخراج قرار گرفت.');
            redirect('index.php?page=domain-scan&run=' . $retry['run_id']);
        }

        if ($action === 'domain_scan_test_selected') {
            $scanUser = require_permission('asset.remote_extract');
            $scanUsername = trim((string) ($_POST['scan_username'] ?? ''));
            $scanPassword = (string) ($_POST['scan_password'] ?? '');
            domain_scan_validate_credentials($scanUsername, $scanPassword);
            $scanProblem = domain_scan_preflight();
            if ($scanProblem !== null) {
                flash('danger', $scanProblem);
                redirect('index.php?page=assets');
            }
            $testIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['asset_ids'] ?? [])), static fn (int $id): bool => $id > 0)));
            if (count($testIds) !== 1) {
                flash('danger', 'برای آزمون دسترسی، دقیقاً یک سیستم را انتخاب کنید.');
                redirect('index.php?page=assets');
            }
            [$testScope, $testScopeParams] = asset_scope($scanUser);
            $testWhere = ($testScope !== '' ? $testScope . ' AND ' : 'WHERE ') . 'a.id = ?';
            $testQuery = db()->prepare('SELECT a.id, a.hostname, a.ip_address FROM assets a ' . $testWhere . ' LIMIT 1');
            $testQuery->execute(array_merge($testScopeParams, $testIds));
            $testAsset = $testQuery->fetch();
            if (!$testAsset) {
                flash('danger', 'سیستم انتخاب‌شده پیدا نشد یا دسترسی مشاهده ندارید.');
                redirect('index.php?page=assets');
            }
            $testHost = trim((string) ($testAsset['hostname'] ?: $testAsset['ip_address']));
            if ($testHost === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $testHost)) {
                flash('danger', 'این شناسنامه نام رایانه یا IP معتبر ندارد.');
                redirect('index.php?page=assets');
            }
            if (function_exists('set_time_limit')) {
                @set_time_limit(300);
            }
            $testCredentials = ['username' => $scanUsername, 'password' => $scanPassword];
            unset($scanPassword, $_POST['scan_password']);
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            try {
                $testInventory = domain_scan_remote_inventory($testHost, $testCredentials);
                $testError = inventory_last_error();
            } catch (Throwable $testException) {
                $testInventory = [];
                $testError = domain_scan_friendly_error($testException->getMessage());
            }
            unset($testCredentials, $scanUsername);
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if ($testInventory === [] || inventory_hardware_score($testInventory) === 0) {
                flash('danger', 'آزمون دسترسی روی ' . $testHost . ' ناموفق بود: ' . ($testError !== '' ? $testError : 'اطلاعات سخت‌افزاری قابل‌استفاده دریافت نشد.'));
                redirect('index.php?page=assets');
            }
            $testScore = inventory_hardware_score($testInventory);
            $testSoftwareCount = count(inventory_rows($testInventory['software'] ?? []));
            flash('success', 'آزمون PowerShell/WMI روی ' . $testHost . ' موفق شد (امتیاز سخت‌افزار ' . $testScore . '/8، ' . $testSoftwareCount . ' نرم‌افزار). چیزی در شناسنامه ذخیره نشد؛ حالا می‌توانید اسکن گروهی را اجرا کنید.');
            redirect('index.php?page=assets');
        }

        if ($action === 'domain_scan_selected') {
            $scanUser = require_permission('asset.remote_extract');
            $scanUsername = trim((string) ($_POST['scan_username'] ?? ''));
            $scanPassword = (string) ($_POST['scan_password'] ?? '');
            domain_scan_validate_credentials($scanUsername, $scanPassword);
            $scanProblem = domain_scan_preflight();
            if ($scanProblem !== null) {
                flash('danger', $scanProblem);
                redirect('index.php?page=assets');
            }
            $scanResult = domain_scan_start_selected((array) ($_POST['asset_ids'] ?? []), $scanUser);
            domain_scan_store_run_credentials((int) $scanResult['run_id'], (int) $scanUser['id'], $scanUsername, $scanPassword);
            unset($scanPassword);
            save_audit((int) $scanUser['id'], 'domain_inventory_selected', null, ['run_id' => $scanResult['run_id'], 'total' => $scanResult['total']]);
            flash('success', $scanResult['total'] . ' سیستم وارد صف استخراج شبکه‌ای شد. سیستم‌های خاموش یا غیرقابل‌دسترسی با وضعیت جداگانه گزارش می‌شوند.');
            redirect('index.php?page=domain-scan&run=' . $scanResult['run_id']);
        }

        if ($action === 'save_inventory_form') {
            $inventoryUser = require_permission('inventory.view');
            if (!user_can($inventoryUser, 'asset.edit')) {
                throw new RuntimeException('ویرایش شناسنامهٔ دارایی برای نقش شما فعال نیست.');
            }
            $assetId = (int) ($_POST['asset_id'] ?? 0);
            [$assetScope, $assetParams] = asset_scope($inventoryUser);
            $assetQuery = db()->prepare('SELECT * FROM assets a ' . ($assetScope ? $assetScope . ' AND a.id = ?' : 'WHERE a.id = ?') . ' LIMIT 1');
            $assetQuery->execute(array_merge($assetParams, [$assetId]));
            $asset = $assetQuery->fetch();
            if (!$asset) {
                throw new RuntimeException('سیستم پیدا نشد یا دسترسی ندارید.');
            }
            $assetTag = (string) $asset['asset_tag'];
            $hostname = post_value('hostname');
            if ($hostname === '' && is_array($_POST['profile'] ?? null)) {
                $hostname = trim((string) ($_POST['profile']['hostname'] ?? ''));
            }
            if ($assetTag === '' || $hostname === '') {
                throw new RuntimeException('نام کامپیوتر الزامی است و شناسه سیستم باید از Inventory حفظ شود.');
            }
            $departmentId = (int) ($_POST['department_id'] ?? 0) ?: null;
            if (is_department_scoped_role($inventoryUser['role'])) {
                $departmentId = (int) ($inventoryUser['department_id'] ?? 0) ?: null;
            }
            $fields = [
                'hostname' => $hostname,
                'computer_type' => post_value('computer_type'),
                'manufacturer' => post_value('manufacturer'),
                'model' => post_value('model'),
                'asset_number' => post_value('asset_number'),
                'seal_number' => post_value('seal_number'),
                'employee_number' => post_value('employee_number'),
                'phone' => post_value('phone'),
                'department_id' => $departmentId,
                'operating_system' => post_value('operating_system'),
                'os_architecture' => post_value('os_architecture'),
                'os_serial' => post_value('os_serial'),
                'os_install_date' => post_value('os_install_date') !== '' ? jalali_input_to_gregorian(post_value('os_install_date')) : null,
                'registered_users' => (int) ($_POST['registered_users'] ?? 0) ?: null,
                'ip_address' => post_value('ip_address'),
                'mac_address' => post_value('mac_address'),
                'cpu' => post_value('cpu'),
                'memory_mb' => (int) ($_POST['memory_mb'] ?? 0) ?: null,
                'domain_username' => post_value('domain_username'),
                'antivirus' => post_value('antivirus'),
                'sound_card' => post_value('sound_card'),
                'network_card' => post_value('network_card'),
            ];
            $fields = array_filter($fields, static fn (mixed $value, string $key): bool => array_key_exists($key, $_POST), ARRAY_FILTER_USE_BOTH);
            $fields['hostname'] = $hostname;
            asset_profile_ensure_schema();
            db()->beginTransaction();
            $set = implode(', ', array_map(static fn (string $key): string => $key . ' = ?', array_keys($fields)));
            try {
                db()->prepare('UPDATE assets SET ' . $set . ', updated_at = NOW() WHERE id = ?')->execute([...array_values($fields), $assetId]);
                asset_profile_save($assetId, $_POST, (int) $inventoryUser['id']);
                if (db()->inTransaction()) {
                    db_commit('index.php');
                }
            } catch (Throwable $saveException) {
                if (db()->inTransaction()) {
                    db_rollback('index.php');
                }
                throw $saveException;
            }
            record_asset_history($assetId, 'inventory_manual_update', 'ویرایش شناسنامه سیستم', (int) $inventoryUser['id'], null, 'فرم شناسنامه سیستم ذخیره شد.', $asset, $fields);
            flash('success', 'شناسنامه سیستم و اطلاعات سخت‌افزاری ذخیره شد.');
             redirect('index.php?page=inventory&id=' . $assetId);
         }
