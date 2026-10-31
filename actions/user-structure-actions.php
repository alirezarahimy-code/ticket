<?php

declare(strict_types=1);

/**
 * actions/user-structure-actions.php — ۶ اکشن POST ساختارهای پایه و کاربران (فاز ۲ بازسازی، برش ۸ — نسخهٔ ۱.۶۲.۱۹).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   add_category              افزودن دستهٔ پایه
 *   add_department            افزودن واحد سازمانی پایه
 *   link_domain_food_by_code  اتصال کاربر دامین به حساب غذا با کد پرسنلی
 *   update_user_role          تغییر نقش کاربر (با ادغام حساب‌های food/local در صورت نیاز)
 *   save_department_members   ذخیرهٔ اعضای واحد
 *   save_cd_dvd_settings      ذخیرهٔ تنظیمات آرشیو CD/DVD
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده؛ تنها موارد
 * function داخل بلوک، کلوژرهای static بی‌نام و فراخوانی‌های function_exists هستند
 * (تعریف تابع نیستند — بی‌خطر؛ اسکن و بازبینی شد).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: ساختارهای پایه (دسته/واحد) ─────
        if ($action === 'add_category') {
            require_permission('service_cat.manage');
            $name = post_value('category_name');
            $serviceGroup = valid_choice(post_value('service_group', 'it'), ['it', 'support'], 'it');
            if ($name !== '') {
                db()->prepare('INSERT INTO categories (name, service_group) VALUES (?, ?)')->execute([$name, $serviceGroup]);
                flash('success', 'دسته‌بندی اضافه شد.');
            }
            redirect('index.php?page=settings');
        }

        if ($action === 'add_department') {
            require_primary_admin();
            $name = post_value('department_name');
            $code = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', post_value('department_code')) ?? '');
            if ($name === '') {
                throw new RuntimeException('نام واحد را وارد کنید.');
            }
            db()->prepare('INSERT INTO departments (name, code) VALUES (?, ?)')->execute([$name, $code !== '' ? $code : null]);
            flash('success', 'واحد سازمانی اضافه شد.');
            redirect('index.php?page=' . valid_choice(post_value('return_page'), ['settings', 'organization'], 'settings'));
        }


        // ───── گروه اکشن: اتصال کاربر دامین به غذا و نقش کاربران ─────
        if ($action === 'link_domain_food_by_code') {
            require_primary_admin();
            require_once dirname(__DIR__) . '/food-personnel-link.php';
            $domainId = (int) ($_POST['user_id'] ?? 0);
            $authoritativeCode = normalize_employee_number(post_value('employee_number'));
            if ($domainId <= 0 || $authoritativeCode === null) {
                throw new RuntimeException('کاربر دامین و کد پرسنلی معتبر را وارد کنید.');
            }
            if (post_value('confirm_code_link') !== '1') {
                throw new RuntimeException('برای ادغام امن، تأیید کنید که رکوردهای این کد متعلق به همان کارمند هستند.');
            }
            $domainQuery = db()->prepare('SELECT id, username, full_name, auth_source, is_active, employee_number FROM users WHERE id = ? LIMIT 1');
            $domainQuery->execute([$domainId]);
            $domain = $domainQuery->fetch();
            if (!$domain || (string) $domain['auth_source'] !== 'ldap' || (int) $domain['is_active'] !== 1) {
                throw new RuntimeException('فقط کاربر دامین فعال را می‌توان با این مسیر به پرسنل غذا متصل کرد.');
            }
            $foodRows = db()->query("SELECT id, username, full_name, first_name, last_name, employee_number, national_code, is_active, role, is_primary_admin, auth_source FROM users WHERE auth_source IN ('food', 'local')")->fetchAll();
            $databaseCodeRows = fpl_code_candidate_rows($foodRows, (string) $authoritativeCode);
            $foodMatches = fpl_food_code_matches($foodRows, (string) $authoritativeCode);
            if ($foodMatches === []) {
                if ($databaseCodeRows !== []) {
                    $foundDetails = array_map(static function (array $row): string {
                        $sourceLabel = (string) ($row['auth_source'] ?? '') === 'food' ? 'پرسنل غذا' : 'حساب محلی';
                        return $sourceLabel . ' #' . (int) $row['id'] . '، نام=' . (string) ($row['full_name'] ?? '') . '، نام کاربری=' . (string) ($row['username'] ?? '') . '، نقش=' . (string) ($row['role'] ?? '');
                    }, $databaseCodeRows);
                    throw new RuntimeException('کد در جدول کاربران پیدا شد، اما نقش/وضعیت هیچ ردیفی اجازهٔ ادغام پرسنلی نمی‌دهد. ردیف‌های پیدا‌شده: ' . implode(' | ', $foundDetails));
                }
                throw new RuntimeException('در جدول کاربران هیچ رکورد پرسنل غذا یا حساب محلی با کد ' . $authoritativeCode . ' پیدا نشد؛ جستجو روی کد پرسنلی و نام کاربری حساب محلی انجام شد.');
            }
            foreach ($foodMatches as $foodMatch) {
                if (!fpl_food_matches_code($foodMatch, (string) $authoritativeCode)) {
                    throw new RuntimeException('یکی از رکوردهای یافته‌شده با کد پرسنلی یا نام کاربری منطبق نیست؛ ادغام انجام نشد.');
                }
            }
            if (post_value('selection_submitted') !== '1') {
                render_header('بررسی رکوردهای هم‌کد', $user);
                echo '<section class="card"><h1>بررسی رکوردهای کد ' . e((string) $authoritativeCode) . '</h1><p>حساب مقصد: ' . e((string) ($domain['full_name'] ?? '')) . ' · ' . e((string) ($domain['username'] ?? '')) . ' (#' . $domainId . ')</p><p>پیش از هر تغییری، رکوردهای پیدا‌شده را بررسی کنید. تیک هر رکوردی را که متعلق به همین کارمند است نگه دارید؛ موارد نامرتبط را بردارید. انتخاب رکورد پرسنل غذا، نام رسمی را در حساب مقصد حفظ می‌کند.</p>';
                echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="link_domain_food_by_code"><input type="hidden" name="user_id" value="' . $domainId . '"><input type="hidden" name="employee_number" value="' . e((string) $authoritativeCode) . '"><input type="hidden" name="confirm_code_link" value="1"><input type="hidden" name="selection_submitted" value="1"><div class="table-wrap"><table class="data-table"><thead><tr><th>ادغام</th><th>منبع/شناسه</th><th>نقش</th><th>نام</th><th>نام کاربری</th><th>کد پرسنلی</th><th>کد ملی</th><th>دلیل تطبیق</th></tr></thead><tbody>';
                foreach ($foodMatches as $foodMatch) {
                    $foodId = (int) $foodMatch['id'];
                    $sourceLabel = (string) ($foodMatch['auth_source'] ?? '') === 'food' ? 'پرسنل غذا' : 'حساب محلی';
                    $matchedBy = fpl_codes_equal((string) ($foodMatch['employee_number'] ?? ''), (string) $authoritativeCode) ? 'کد پرسنلی' : 'نام کاربری محلی';
                    echo '<tr><td><input type="checkbox" name="food_ids[]" value="' . $foodId . '" checked aria-label="ادغام ' . e((string) ($foodMatch['full_name'] ?? '')) . '"></td><td>' . e($sourceLabel) . ' #' . $foodId . '</td><td>' . e((string) ($foodMatch['role'] ?? '')) . '</td><td>' . e((string) ($foodMatch['full_name'] ?? '')) . '</td><td>' . e((string) ($foodMatch['username'] ?? '')) . '</td><td>' . e((string) ($foodMatch['employee_number'] ?? '—')) . '</td><td>' . e((string) ($foodMatch['national_code'] ?? '—')) . '</td><td>' . e($matchedBy) . '</td></tr>';
                }
                echo '</tbody></table></div><button class="button primary" type="submit">تأیید رکوردهای انتخاب‌شده و ادغام امن</button> <a class="button secondary" href="index.php?page=settings#users">انصراف</a></form></section>';
                render_footer();
                exit;
            }
            $selectedIds = array_map('intval', (array) ($_POST['food_ids'] ?? []));
            $foodMatches = fpl_select_food_matches($foodMatches, $selectedIds);
            // رکورد دارای employee_number اول ادغام می‌شود تا قید یکتایی کد آزاد شود؛
            // سپس حساب محلی با username همان کد نیز در همان تراکنش به دامنه منتقل می‌شود.
            usort($foodMatches, static function (array $left, array $right) use ($authoritativeCode): int {
                $leftRank = fpl_codes_equal((string) ($left['employee_number'] ?? ''), (string) $authoritativeCode) ? 0 : 1;
                $rightRank = fpl_codes_equal((string) ($right['employee_number'] ?? ''), (string) $authoritativeCode) ? 0 : 1;
                return $leftRank <=> $rightRank ?: ((int) $left['id'] <=> (int) $right['id']);
            });
            $foodIds = array_map(static fn (array $row): int => (int) $row['id'], $foodMatches);
            $employeeCodeOwner = employee_number_owner((string) $authoritativeCode, $domainId);
            if ($employeeCodeOwner > 0 && !in_array($employeeCodeOwner, $foodIds, true)) {
                throw new RuntimeException('این کد قبلاً به حساب دیگری (#' . $employeeCodeOwner . ') اختصاص یافته که رکورد پرسنل غذای قابل‌ادغام نیست؛ هیچ داده‌ای تغییر نکرد.');
            }
            $officialFood = null;
            foreach ($foodMatches as $foodMatch) {
                if ((string) ($foodMatch['auth_source'] ?? '') === 'food') {
                    $officialFood = $foodMatch;
                    break;
                }
            }
            $backup = fpl_backup_users(db());
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $sourcePlaceholders = implode(',', array_fill(0, count($foodIds), '?'));
                $pdo->prepare('UPDATE users SET employee_number = NULL WHERE id IN (' . $sourcePlaceholders . ')')->execute($foodIds);
                foreach ($foodIds as $foodId) {
                    $mergeResult = fpl_merge($pdo, $domainId, $foodId, true, (string) $authoritativeCode, true);
                    if (empty($mergeResult['ok'])) {
                        throw new RuntimeException((string) ($mergeResult['message'] ?? 'ادغام امن حساب #' . $foodId . ' انجام نشد.'));
                    }
                }
                // نام رسمیِ رکورد پرسنل غذا بر نام حساب محلیِ تکراری اولویت دارد.
                if ($officialFood !== null) {
                    $officialName = trim((string) ($officialFood['full_name'] ?? ''));
                    if ($officialName !== '') {
                        $pdo->prepare('UPDATE users SET full_name = ?, first_name = ?, last_name = ? WHERE id = ?')->execute([
                            $officialName,
                            trim((string) ($officialFood['first_name'] ?? '')) ?: null,
                            trim((string) ($officialFood['last_name'] ?? '')) ?: null,
                            $domainId,
                        ]);
                    }
                }
                $pdo->commit();
            } catch (Throwable $mergeError) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $mergeError;
            }
            system_log('info', 'food_personnel_link', 'اتصال قطعی کاربر دامین به همهٔ رکوردهای محلی/غذای دارای کد دستی', ['domain_id' => $domainId, 'food_ids' => $foodIds, 'employee_number' => $authoritativeCode, 'backup' => $backup]);
            flash('success', 'کد ' . $authoritativeCode . ' به کاربر دامین #' . $domainId . ' متصل شد؛ ' . count($foodIds) . ' رکورد محلی/پرسنل غذا و ارجاع‌هایشان با پشتیبان و در یک تراکنش ادغام شدند. پشتیبان: ' . $backup);
            redirect('index.php?page=settings#users');
        }

        if ($action === 'update_user_role') {
            $admin = require_primary_admin();
            $userId = (int) ($_POST['user_id'] ?? 0);
            $role = valid_choice(post_value('role'), array_keys(permission_roles()), 'user');
            $isActive = null;
            $employeeNumber = post_value('employee_number');
            if (strlen($employeeNumber) > 100) {
                throw new RuntimeException('کد پرسنلی بیش از حد طولانی است.');
            }
            $nationalCode = post_value('national_code');
            if ($nationalCode !== '' && !preg_match('/^\d{6,20}$/', $nationalCode)) {
                throw new RuntimeException('کد ملی باید فقط شامل ۶ تا ۲۰ رقم باشد.');
            }
            $departmentId = (int) ($_POST['department_id'] ?? 0) ?: null;
            $handlingUnitId = (int) ($_POST['handling_unit_id'] ?? 0) ?: null;
            // فقط نقش‌هایی که واقعاً به درخواست رسیدگی می‌کنند نیاز به واحد رسیدگی/سازمانی دارند.
            // نقش‌هایی مثل بازرس (inspector) یا سوپروایزر نباید ذخیره‌شان به این اجبار وابسته باشد.
            $needsHandlingUnit = in_array($role, ['agent', 'manager'], true);
            $handlingUnitCode = '';
            if (!$handlingUnitId && $needsHandlingUnit) {
                $defaultUnitQuery = db()->prepare('SELECT id, code FROM handling_units WHERE code = ? LIMIT 1');
                $defaultUnitQuery->execute([!empty($_POST['is_it_agent']) ? 'it' : 'support']);
                $defaultUnit = $defaultUnitQuery->fetch() ?: null;
                $handlingUnitId = $defaultUnit ? (int) $defaultUnit['id'] : null;
                $handlingUnitCode = $defaultUnit['code'] ?? '';
            }
            if ($handlingUnitId) {
                $unitQuery = db()->prepare('SELECT code FROM handling_units WHERE id = ? AND is_active = 1 LIMIT 1');
                $unitQuery->execute([$handlingUnitId]);
                $handlingUnitCode = (string) ($unitQuery->fetchColumn() ?: '');
            }
            $isItAgent = $handlingUnitCode === 'it' ? 1 : 0;
            if ($needsHandlingUnit && !$handlingUnitId) {
                throw new RuntimeException('برای کارشناس یا مدیر، واحد رسیدگی IT یا پشتیبانی را انتخاب کنید.');
            }
            $isPrimaryAdmin = $role === 'primary_admin' ? 1 : ($role === 'admin' && !empty($_POST['is_primary_admin']) ? 1 : 0);
            if ($userId === (int) $admin['id'] && !in_array($role, ['primary_admin'], true)) {
                throw new RuntimeException('ادمین اصلی فعلی نمی‌تواند خودش را از مدیریت اصلی خارج کند.');
            }
            if ($needsHandlingUnit && !$departmentId) {
                throw new RuntimeException('برای کارشناس یا مدیر، واحد سازمانی را انتخاب کنید.');
            }
            if (!in_array($role, ['primary_admin'], true) && $isPrimaryAdmin) {
                throw new RuntimeException('فقط مدیر سامانه می‌تواند ادمین اصلی باشد.');
            }
            $targetQuery = db()->prepare('SELECT role, is_primary_admin, is_active, department_id, auth_source, employee_number, national_code FROM users WHERE id = ? LIMIT 1');
            $targetQuery->execute([$userId]);
            $target = $targetQuery->fetch();
            if (!$target) {
                throw new RuntimeException('کاربر پیدا نشد.');
            }
            $isActive = array_key_exists('is_active', $_POST) ? (!empty($_POST['is_active']) ? 1 : 0) : (int) $target['is_active'];
            if ((int) $target['is_primary_admin'] === 1 && (!$isPrimaryAdmin || !in_array($role, ['primary_admin'], true))) {
                $primaryCount = (int) db()->query('SELECT COUNT(*) FROM users WHERE (role = "admin" OR role = "primary_admin") AND is_primary_admin = 1 AND is_active = 1')->fetchColumn();
                if ($primaryCount <= 1) {
                    throw new RuntimeException('حداقل یک ادمین اصلی باید باقی بماند.');
                }
            }
            if ($userId === (int) $admin['id'] && !$isActive) {
                throw new RuntimeException('ادمین اصلی فعلی نمی‌تواند غیرفعال شود.');
            }
            if ((int) $target['is_primary_admin'] === 1 && !$isActive) {
                $primaryCount = (int) db()->query('SELECT COUNT(*) FROM users WHERE (role = "admin" OR role = "primary_admin") AND is_primary_admin = 1 AND is_active = 1')->fetchColumn();
                if ($primaryCount <= 1) {
                    throw new RuntimeException('حداقل یک ادمین اصلی فعال باید باقی بماند.');
                }
            }
            $employeeNumberValue = normalize_employee_number($employeeNumber);
            if ($employeeNumberValue !== null) {
                require_once dirname(__DIR__) . '/food-personnel-link.php';
            }
            $mergeFoodId = 0;
            $mergeFood = null;
            $usersBackup = '';
            if ($employeeNumberValue !== null) {
                $ownerId = employee_number_owner($employeeNumberValue, $userId);
                if ($ownerId > 0) {
                    $ownerQuery = db()->prepare('SELECT id, username, employee_number, national_code, role, auth_source, is_primary_admin FROM users WHERE id = ? LIMIT 1');
                    $ownerQuery->execute([$ownerId]);
                    $owner = $ownerQuery->fetch() ?: null;
                    $ownerSource = (string) ($owner['auth_source'] ?? '');
                    $eligibleFoodOwner = $ownerSource === 'food'
                        || (is_array($owner) && fpl_is_local_food_account($owner));
                    if ((string) ($target['auth_source'] ?? '') !== 'ldap' || !$eligibleFoodOwner) {
                        throw new RuntimeException('این کد پرسنلی متعلق به کاربر دیگری است و پرسنل غذا/حساب محلی واجد شرایط برای ادغام نیست.');
                    }
                    if (!function_exists('fpl_find_food_by_code')) {
                        throw new RuntimeException('ماژول جستجوی امن پرسنل غذا بارگذاری نشده است.');
                    }
                    $uniqueFoodOwner = fpl_find_food_by_code(db(), (string) $employeeNumberValue);
                    if ($uniqueFoodOwner === null || (int) ($uniqueFoodOwner['id'] ?? 0) !== $ownerId) {
                        throw new RuntimeException('برای این کد بیش از یک حساب واجد شرایط پیدا شد یا مالک کد قابل تأیید نیست؛ ادغام انجام نشد.');
                    }
                    $mergeFood = $uniqueFoodOwner;
                } elseif (function_exists('fpl_find_food_by_code')) {
                    // حساب‌های محلی قدیمی ممکن است کد را فقط در username داشته باشند.
                    $mergeFood = fpl_find_food_by_code(db(), (string) $employeeNumberValue);
                }
                if ($mergeFood !== null) {
                    if (!function_exists('fpl_food_personnel_code') || !function_exists('fpl_merge') || !function_exists('fpl_backup_users')) {
                        throw new RuntimeException('ماژول ادغام امن حساب پرسنل غذا بارگذاری نشده است.');
                    }
                    $foodCode = normalize_employee_number(fpl_food_personnel_code($mergeFood));
                    if ($foodCode === null || !fpl_codes_equal($foodCode, $employeeNumberValue)) {
                        throw new RuntimeException('کد حساب پرسنل غذا با کد واردشده در کاربر دامین یکی نیست؛ ادغام انجام نشد.');
                    }
                    $foodId = (int) ($mergeFood['id'] ?? 0);
                    $foodNational = trim((string) ($mergeFood['national_code'] ?? ''));
                    if ($nationalCode !== '' && $foodNational !== '' && $nationalCode !== $foodNational) {
                        throw new RuntimeException('کد ملی واردشده در کاربر دامین با پرسنل غذا متفاوت است؛ ادغام انجام نشد.');
                    }
                    if ($nationalCode === '' && $foodNational !== '') {
                        $nationalCode = $foodNational;
                    }
                    $usersBackup = fpl_backup_users(db());
                    $mergeFoodId = $foodId;
                }
            }
            if ($mergeFoodId === 0 && $employeeNumberValue !== null && employee_number_owner($employeeNumberValue, $userId) > 0) {
                throw new RuntimeException('این کد پرسنلی قبلاً به کاربر دیگری داده شده است. کد تکراری مجاز نیست.');
            }
            $pdo = db();
            $pdo->beginTransaction();
            try {
                if ($mergeFoodId > 0) {
                    $mergeResult = fpl_merge($pdo, $userId, $mergeFoodId, true, (string) $employeeNumberValue);
                    if (empty($mergeResult['ok'])) {
                        throw new RuntimeException((string) ($mergeResult['message'] ?? 'ادغام حساب پرسنل غذا ناموفق بود.'));
                    }
                }
                $pdo->prepare('UPDATE users SET role = ?, employee_number = ?, national_code = ?, department_id = ?, handling_unit_id = ?, is_it_agent = ?, is_primary_admin = ?, is_active = ? WHERE id = ?')->execute([$role, $employeeNumberValue, $nationalCode !== '' ? $nationalCode : null, $departmentId, $handlingUnitId, $isItAgent, $isPrimaryAdmin, $isActive, $userId]);
                if ($role === 'manager' && $departmentId) {
                    $pdo->prepare('UPDATE departments SET manager_user_id = NULL WHERE manager_user_id = ? AND id <> ?')->execute([$userId, $departmentId]);
                    $pdo->prepare('UPDATE departments SET manager_user_id = ? WHERE id = ?')->execute([$userId, $departmentId]);
                } else {
                    $pdo->prepare('UPDATE departments SET manager_user_id = NULL WHERE manager_user_id = ?')->execute([$userId]);
                }
                if ($role === 'manager' && $handlingUnitId) {
                    $pdo->prepare('UPDATE handling_units SET manager_user_id = NULL WHERE manager_user_id = ? AND id <> ?')->execute([$userId, $handlingUnitId]);
                    $pdo->prepare('UPDATE handling_units SET manager_user_id = ? WHERE id = ?')->execute([$userId, $handlingUnitId]);
                } else {
                    $pdo->prepare('UPDATE handling_units SET manager_user_id = NULL WHERE manager_user_id = ?')->execute([$userId]);
                }
                $pdo->commit();
            } catch (Throwable $saveError) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $saveError;
            }
            if ($mergeFoodId > 0) {
                system_log('info', 'food_personnel_link', 'پیوند خودکار حساب پرسنل غذا به کاربر دامین با کد پرسنلی دستی', ['domain_id' => $userId, 'food_id' => $mergeFoodId, 'employee_number' => $employeeNumberValue, 'backup' => $usersBackup]);
                flash('success', 'کد پرسنلی اولویت‌دار ثبت شد و حساب پرسنل غذا #' . $mergeFoodId . ' با کاربر دامین ادغام شد. پشتیبان: ' . $usersBackup);
            } else {
                flash('success', 'نقش و دسترسی کاربر به‌روزرسانی شد.');
            }
            redirect('index.php?page=' . (post_value('return_page') === 'organization' ? 'organization' : 'settings#users'));
        }

        if ($action === 'save_department_members') {
            require_primary_admin();
            $departmentId = (int) ($_POST['department_id'] ?? 0);
            $managerId = (int) ($_POST['manager_user_id'] ?? 0);
            $memberIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['member_ids'] ?? [])))));
            if ($departmentId <= 0 || $managerId <= 0) {
                throw new RuntimeException('معاونت و مدیر معاونت را انتخاب کنید.');
            }
            if (!in_array($managerId, $memberIds, true)) {
                $memberIds[] = $managerId;
            }
            $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
            $memberQuery = db()->prepare('SELECT id, role FROM users WHERE id IN (' . $placeholders . ') AND is_active = 1');
            $memberQuery->execute($memberIds);
            $members = $memberQuery->fetchAll();
            if (count($members) !== count($memberIds)) {
                throw new RuntimeException('یکی از کاربران انتخاب‌شده فعال نیست.');
            }
            db()->beginTransaction();
            $clear = db()->prepare('UPDATE users SET department_id = NULL WHERE department_id = ? AND id NOT IN (' . $placeholders . ')');
            $clear->execute(array_merge([$departmentId], $memberIds));
            $assign = db()->prepare('UPDATE users SET department_id = ? WHERE id IN (' . $placeholders . ')');
            $assign->execute(array_merge([$departmentId], $memberIds));
            $clearOldManagers = db()->prepare('UPDATE departments SET manager_user_id = NULL WHERE manager_user_id = ? AND id <> ?');
            foreach ($memberIds as $memberId) {
                $clearOldManagers->execute([$memberId, $departmentId]);
            }
            db()->prepare('UPDATE users SET role = "manager" WHERE id = ? AND role NOT IN ("admin", "primary_admin", "supervisor", "support_manager", "inspector") AND is_primary_admin = 0')->execute([$managerId]);
            db()->prepare('UPDATE departments SET manager_user_id = ? WHERE id = ?')->execute([$managerId, $departmentId]);
            db_commit('index.php');
            flash('success', 'مدیر و اعضای معاونت ذخیره شدند.');
            redirect('index.php?page=' . valid_choice(post_value('return_page'), ['settings#departments', 'organization'], 'settings#departments'));
        }

        if ($action === 'save_cd_dvd_settings') {
            $settingsAdmin = require_permission('settings.users_edit');
            $inspectionUserId = (int) ($_POST['cd_dvd_inspection_user_id'] ?? 0);
            if ($inspectionUserId > 0) {
                $inspectionQuery = db()->prepare('SELECT id FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
                $inspectionQuery->execute([$inspectionUserId]);
                if (!$inspectionQuery->fetchColumn()) {
                    throw new RuntimeException('کاربر بازرسی انتخاب‌شده فعال نیست.');
                }
            }
            save_setting('cd_dvd_inspection_user_id', (string) $inspectionUserId);
            save_audit((int) $settingsAdmin['id'], 'cd_dvd_inspection_user_changed', null, ['user_id' => $inspectionUserId]);
            flash('success', $inspectionUserId > 0 ? 'کاربر بازرسی CD/DVD تعیین شد.' : 'کاربر بازرسی CD/DVD حذف شد.');
            redirect('index.php?page=settings#cd-dvd-settings');
        }
