<?php
declare(strict_types=1);

/**
 * اتصال کاربران دامین (LDAP) به رکورد پرسنل غذا یا حساب محلی کارکنان
 * ============================================================================
 * منطق:
 *   ۱. کاربر دامین و پرسنل غذا با «نام + نام خانوادگی» (پس از نرمال‌سازی) تطبیق داده می‌شوند.
 *   ۲. همنام‌ها در فهرست انتخاب دستی نشان داده می‌شوند (کد پرسنلی و کد ملی کامل).
 *   ۳. فقط با انتخاب مدیر ادغام انجام می‌شود. ادغام:
 *        - کد پرسنلی و کد ملی را از رکورد غذا/حساب محلی کارکنان به کاربر دامین منتقل می‌کند،
 *        - همهٔ ارجاعات رکورد قبلی را به کاربر دامین منتقل می‌کند،
 *        - رکورد قبلی را پس از پشتیبان‌گیری حذف می‌کند،
 *      و هر ادغام در یک تراکنش جدا انجام می‌شود (خطا = بدون تغییر).
 *   ۴. قبل از هر اجرا یک نسخهٔ پشتیبان از جدول users ساخته می‌شود.
 *
 * دسترسی: فقط ادمین اصلی.
 */

/** نرمال‌سازی نام برای تطبیق: ی/ک عربی، نیم‌فاصله، فاصلهٔ اضافه، بزرگی حروف. */
function fpl_norm_name(string $value): string
{
    $value = strtr($value, ['ي' => 'ی', 'ك' => 'ک', 'ى' => 'ی', 'ة' => 'ه', 'ۀ' => 'ه', 'ئ' => 'ی']);
    $value = str_replace(["\u{200C}", "\u{200B}", "\u{200E}", "\u{200F}", "\u{FEFF}", "\u{0640}"], '', $value);
    $value = trim((string) preg_replace('/\s+/u', ' ', $value));
    return mb_strtolower($value, 'UTF-8');
}

/** نام کامل رکورد پرسنل غذا: نام + نام خانوادگی؛ اگر خالی بود full_name. */
function fpl_person_name(array $row): string
{
    $name = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));
    if ($name === '') {
        $name = (string) ($row['full_name'] ?? '');
    }
    return fpl_norm_name($name);
}

/**
 * ارجاعات به users.id که باید هنگام ادغام منتقل شوند.
 * - کلیدهای خارجی (از information_schema، اگر در دسترس باشد)
 * - ستون‌های بدون کلید خارجی که در schema.sql به کاربر اشاره می‌کنند
 * جدول/ستونی که در این نصب وجود نداشته باشد، بی‌صدا کنار گذاشته می‌شود.
 *
 * @return array<int, array{0:string,1:string}>
 */
function fpl_reference_columns(PDO $pdo): array
{
    $refs = [];
    try {
        $rows = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = 'users' AND REFERENCED_COLUMN_NAME = 'id'")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            $refs[$row[0] . '.' . $row[1]] = [(string) $row[0], (string) $row[1]];
        }
    } catch (Throwable $exception) {
        // محیط بدون information_schema (مثلاً آزمون): فقط فهرست دستی
    }
    foreach (fpl_manual_reference_columns() as [$table, $column]) {
        $refs[$table . '.' . $column] = [$table, $column];
    }
    return array_values($refs);
}

/** ستون‌های عددی بدون کلید خارجی که به users.id اشاره می‌کنند (بررسی‌شده در schema.sql). */
function fpl_manual_reference_columns(): array
{
    return [
        ['departments', 'manager_user_id'],
        ['org_units', 'manager_user_id'],
        ['org_unit_managers', 'user_id'],
        ['users', 'manager_user_id'],
        ['food_ticket_events', 'delivered_by'],
        ['food_ticket_group_uid_history', 'actor_id'],
        ['food_ticket_groups', 'created_by'],
        ['food_ticket_daily_absence', 'created_by'],
        ['food_ticket_card_map', 'user_id'],
        ['system_logs', 'user_id'],
        ['domain_scan_runs', 'started_by'],
        ['food_catalog', 'created_by'],
        ['food_calendar', 'created_by'],
        ['food_calendar', 'closed_by'],
        ['food_orders', 'employee_id'],
        ['food_orders', 'created_by'],
        ['food_order_logs', 'user_id'],
    ];
}

/** آیا ستون در جدول وجود دارد؟ (پرس‌وجوی سبک با LIMIT 0؛ بدون اثر جانبی) */
function fpl_column_exists(PDO $pdo, string $table, string $column): bool
{
    try {
        $pdo->query('SELECT `' . str_replace('`', '', $column) . '` FROM `' . str_replace('`', '', $table) . '` LIMIT 0');
        return true;
    } catch (Throwable $exception) {
        return false;
    }
}

/**
 * تطبیق کاربران دامین با پرسنل غذا؛ کد پرسنلی موجود در دامین نسبت به نام اولویت دارد.
 * وضعیت هر کاربر دامین:
 *   single   : دقیقاً یک تطبیق سازگار بر اساس نام
 *   multi    : چند تطبیق سازگار (انتخاب دستی)
 *   code_match / code_multi : تطبیق یکتا/تکراری بر اساس کد دامین
 *   none     : هیچ تطبیقی نیست
 *   conflict : تطبیق هست ولی کد ملی متفاوت است
 *   has_code : دامین کد دارد ولی رکورد هم‌کد در غذا پیدا نشد
 *
 * @return array<int, array<string, mixed>>
 */
function fpl_candidates(PDO $pdo): array
{
    $domainRows = $pdo->query("SELECT id, username, full_name, first_name, last_name, employee_number, national_code, email, department, is_active, role FROM users WHERE auth_source = 'ldap' AND is_active = 1 ORDER BY full_name, id")->fetchAll();
    $foodRows = $pdo->query("SELECT id, username, full_name, first_name, last_name, employee_number, national_code, is_active, role, is_primary_admin, auth_source FROM users WHERE auth_source = 'food' OR (auth_source = 'local' AND role = 'local_user' AND COALESCE(is_primary_admin, 0) = 0) ORDER BY id")->fetchAll();

    $foodByName = [];
    foreach ($foodRows as $food) {
        $key = fpl_person_name($food);
        if ($key !== '') {
            $foodByName[$key][] = $food;
        }
    }

    $result = [];
    foreach ($domainRows as $domain) {
        $item = ['domain' => $domain, 'status' => 'none', 'matches' => [], 'chosen' => 0, 'reason' => ''];
        $domainCode = fpl_normalize_code((string) ($domain['employee_number'] ?? ''));
        if ($domainCode !== '') {
            $codeMatches = fpl_food_code_matches($foodRows, $domainCode);
            if ($codeMatches === []) {
                $item['status'] = 'has_code';
                $item['reason'] = 'کد ثبت‌شدهٔ کاربر دامین (' . $domainCode . ') اولویت دارد، اما پرسنل غذا/حساب محلی با همین کد پیدا نشد.';
            } else {
                $domainNational = trim((string) ($domain['national_code'] ?? ''));
                $compatible = [];
                $incompatible = 0;
                foreach ($codeMatches as $food) {
                    $foodNational = trim((string) ($food['national_code'] ?? ''));
                    $ok = $domainNational === '' || $foodNational === '' || $domainNational === $foodNational;
                    $food['compatible'] = $ok;
                    $food['code_match'] = true;
                    $item['matches'][] = $food;
                    if ($ok) { $compatible[] = $food; } else { $incompatible++; }
                }
                if (count($compatible) === 1) {
                    $item['status'] = 'code_match';
                    $item['chosen'] = (int) $compatible[0]['id'];
                    $item['reason'] = 'کد پرسنلی واردشده در حساب دامین مبنای اتصال است.';
                } elseif (count($compatible) > 1) {
                    $item['status'] = 'code_multi';
                    $item['reason'] = 'چند حساب پرسنل غذا با کد ثبت‌شده در دامین پیدا شد؛ پیش از ادغام رفع تکرار کنید.';
                } else {
                    $item['status'] = 'conflict';
                    $item['reason'] = 'کد ملی حساب دامین با پرسنل غذای دارای همین کد پرسنلی متفاوت است.';
                }
            }
            $result[] = $item;
            continue;
        }
        $key = fpl_norm_name((string) ($domain['full_name'] ?? ''));
        $domainNational = trim((string) ($domain['national_code'] ?? ''));
        $compatible = [];
        $incompatible = 0;
        foreach ($foodByName[$key] ?? [] as $food) {
            $foodNational = trim((string) ($food['national_code'] ?? ''));
            $ok = $domainNational === '' || $foodNational === '' || $domainNational === $foodNational;
            $food['compatible'] = $ok;
            $item['matches'][] = $food;
            if ($ok) {
                $compatible[] = $food;
            } else {
                $incompatible++;
            }
        }
        if ($compatible === [] && $incompatible > 0) {
            $item['status'] = 'conflict';
            $item['reason'] = 'نام یکسان است ولی کد ملی هر دو طرف متفاوت است.';
        } elseif (count($compatible) === 1) {
            $item['status'] = 'single';
            $item['chosen'] = (int) $compatible[0]['id'];
        } elseif (count($compatible) > 1) {
            $item['status'] = 'multi';
        }
        $result[] = $item;
    }
    return $result;
}

/** آیا انتخاب (دامین، پرسنل غذا) مجاز است؟ */
function fpl_pair_is_allowed(array $candidates, int $domainId, int $foodId): bool
{
    foreach ($candidates as $item) {
        if ((int) $item['domain']['id'] !== $domainId) {
            continue;
        }
        foreach ($item['matches'] as $match) {
            if ((int) $match['id'] === $foodId && !empty($match['compatible'])) {
                return true;
            }
        }
    }
    return false;
}

/**
 * ادغام یک جفت. کل عملیات در یک تراکنش است؛ در صورت خطا چیزی تغییر نمی‌کند.
 * @return array{ok:bool, message:string, moved:array<string,int>}
 */
function fpl_merge(PDO $pdo, int $domainId, int $foodId, bool $allowNameMismatch = false, ?string $authoritativeCode = null): array
{
    $moved = [];
    $ownsTransaction = !$pdo->inTransaction();
    try {
        if ($ownsTransaction) {
            $pdo->beginTransaction();
        }

        $domainStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $domainStmt->execute([$domainId]);
        $domain = $domainStmt->fetch();
        $foodStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $foodStmt->execute([$foodId]);
        $food = $foodStmt->fetch();

        if (!$domain || (string) $domain['auth_source'] !== 'ldap') {
            throw new RuntimeException('کاربر دامین پیدا نشد یا دامین نیست.');
        }
        $foodSource = (string) ($food['auth_source'] ?? '');
        $isFoodOnlyRecord = $foodSource === 'food';
        $isLocalFoodAccount = $foodSource === 'local'
            && (string) ($food['role'] ?? '') === 'local_user'
            && (int) ($food['is_primary_admin'] ?? 0) !== 1;
        if (!$food || (!$isFoodOnlyRecord && !$isLocalFoodAccount)) {
            throw new RuntimeException('رکورد پرسنل غذا یا حساب محلی معتبر پیدا نشد.');
        }
        if (!$allowNameMismatch && fpl_norm_name((string) $domain['full_name']) !== fpl_person_name($food)) {
            throw new RuntimeException('نام دو رکورد دیگر یکی نیست.');
        }
        $domainPersonnelCode = fpl_normalize_code((string) ($domain['employee_number'] ?? ''));
        $domainNational = trim((string) ($domain['national_code'] ?? ''));
        $foodNational = trim((string) ($food['national_code'] ?? ''));
        if ($domainNational !== '' && $foodNational !== '' && $domainNational !== $foodNational) {
            throw new RuntimeException('کد ملی دو رکورد متفاوت است.');
        }
        $personnelCode = fpl_food_personnel_code($food);
        if ($personnelCode === '') {
            throw new RuntimeException('رکورد پرسنل غذا کد پرسنلی ندارد.');
        }
        $manualCode = fpl_normalize_code((string) ($authoritativeCode ?? ''));
        if ($manualCode !== '') {
            if (!fpl_codes_equal($manualCode, $personnelCode)) {
                throw new RuntimeException('کد واردشده با کد این حساب پرسنل غذا یکی نیست؛ ادغام انجام نشد.');
            }
            // در ادغام دستی، کد واردشده مدیر بر مقدار قدیمی ذخیره‌شده روی حساب دامین مقدم است.
            $personnelCode = $manualCode;
        } elseif ($domainPersonnelCode !== '' && !fpl_codes_equal($domainPersonnelCode, $personnelCode)) {
            throw new RuntimeException('کد پرسنلی ثبت‌شده در حساب دامین با رکورد پرسنل غذا یکی نیست؛ ادغام متوقف شد.');
        } elseif ($domainPersonnelCode !== '') {
            $personnelCode = $domainPersonnelCode;
        }

        // ۱. کد پرسنلی را از رکورد پرسنل غذا آزاد کن (کد پرسنلی یکتاست) و نام کاربری موقت بده.
        $pdo->prepare('UPDATE users SET employee_number = NULL, national_code = NULL, username = ? WHERE id = ?')
            ->execute(['fpl_merged_' . $foodId, $foodId]);

        // ۲. کد پرسنلی و کد ملی را به کاربر دامین بده.
        // نام، نام خانوادگی و نام کامل از رکورد پرسنل غذا (فهرست رسمی) گرفته می‌شود.
        $foodFirst = trim((string) ($food['first_name'] ?? ''));
        $foodLast = trim((string) ($food['last_name'] ?? ''));
        $foodFull = trim((string) ($food['full_name'] ?? ''));
        if ($foodFull === '') {
            $foodFull = trim($foodFirst . ' ' . $foodLast);
        }
        $pdo->prepare("UPDATE users SET employee_number = ?, national_code = CASE WHEN national_code IS NULL OR national_code = '' THEN ? ELSE national_code END, full_name = ?, first_name = ?, last_name = ? WHERE id = ?")
            ->execute([$personnelCode, $foodNational !== '' ? $foodNational : null, $foodFull, $foodFirst !== '' ? $foodFirst : null, $foodLast !== '' ? $foodLast : null, $domainId]);

        // ۳. ارجاعات را منتقل کن.
        foreach (fpl_reference_columns($pdo) as [$table, $column]) {
            if (!fpl_column_exists($pdo, $table, $column)) {
                continue;
            }
            $stmt = $pdo->prepare('UPDATE `' . $table . '` SET `' . $column . '` = ? WHERE `' . $column . '` = ?');
            $stmt->execute([$domainId, $foodId]);
            if ($stmt->rowCount() > 0) {
                $moved[$table . '.' . $column] = $stmt->rowCount();
            }
        }

        // ۴. بررسی: هیچ ارجاعی به رکورد پرسنل غذا نمانده باشد.
        foreach (fpl_reference_columns($pdo) as [$table, $column]) {
            if (!fpl_column_exists($pdo, $table, $column)) {
                continue;
            }
            $check = $pdo->prepare('SELECT COUNT(*) FROM `' . $table . '` WHERE `' . $column . '` = ?');
            $check->execute([$foodId]);
            if ((int) $check->fetchColumn() > 0) {
                throw new RuntimeException('ارجاع باقی‌مانده در ' . $table . '.' . $column);
            }
        }

        // ۵. رکورد پرسنل غذا را حذف کن.
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$foodId]);

        if ($ownsTransaction) {
            $pdo->commit();
        }
        return ['ok' => true, 'message' => 'ادغام انجام شد.', 'moved' => $moved];
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (!$ownsTransaction) {
            throw $exception;
        }
        return ['ok' => false, 'message' => $exception->getMessage(), 'moved' => []];
    }
}

/** پشتیبان جدول users قبل از اجرا. */
function fpl_backup_users(PDO $pdo): string
{
    $name = 'users_backup_fpl_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
    $pdo->exec('CREATE TABLE `' . $name . '` AS SELECT * FROM users');
    return $name;
}

/** نرمال‌سازی کد پرسنلی: اعداد فارسی/عربی به لاتین، حذف فاصله. */
function fpl_normalize_code(string $value): string
{
    $digits = ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'];
    return trim(strtr(trim($value), $digits));
}

function fpl_codes_equal(string $left, string $right): bool
{
    $left = fpl_normalize_code($left);
    $right = fpl_normalize_code($right);
    if ($left === $right) {
        return true;
    }
    $leftLoose = ltrim($left, '0');
    $rightLoose = ltrim($right, '0');
    return ($leftLoose === '' ? '0' : $leftLoose) === ($rightLoose === '' ? '0' : $rightLoose);
}

/** کد مؤثر پرسنل؛ حساب محلی قدیمی ممکن است کد را فقط در username نگه داشته باشد. */
function fpl_food_personnel_code(array $food): string
{
    $code = fpl_normalize_code((string) ($food['employee_number'] ?? ''));
    if ($code !== '') {
        return $code;
    }
    if ((string) ($food['auth_source'] ?? '') === 'local'
        && (string) ($food['role'] ?? '') === 'local_user'
        && (int) ($food['is_primary_admin'] ?? 0) !== 1) {
        return fpl_normalize_code((string) ($food['username'] ?? ''));
    }
    return '';
}

/** رکوردهای منطبق با کد؛ کد دقیق اولویت دارد و صفرهای آغازین فقط در نبود تطبیق دقیق نادیده گرفته می‌شوند. */
function fpl_food_code_matches(array $foodRows, string $code): array
{
    $code = fpl_normalize_code($code);
    if ($code === '') {
        return [];
    }
    $exact = [];
    $loose = [];
    foreach ($foodRows as $row) {
        $rowCode = fpl_food_personnel_code($row);
        if ($rowCode === '') {
            continue;
        }
        if ($rowCode === $code) {
            $exact[] = $row;
        } elseif (ltrim($rowCode, '0') === ltrim($code, '0')) {
            $loose[] = $row;
        }
    }
    return $exact !== [] ? $exact : $loose;
}

/** پیدا کردن یکتای پرسنل غذا با کد (با تحمل اعداد فارسی و صفرهای آغازین). */
function fpl_find_food_by_code(PDO $pdo, string $code): ?array
{
    $code = fpl_normalize_code($code);
    if ($code === '') {
        return null;
    }
    $rows = $pdo->query("SELECT id, username, full_name, first_name, last_name, employee_number, national_code, is_active, role, is_primary_admin, auth_source FROM users WHERE auth_source = 'food' OR (auth_source = 'local' AND role = 'local_user' AND COALESCE(is_primary_admin, 0) = 0)")->fetchAll();
    $matches = fpl_food_code_matches($rows, $code);
    return count($matches) === 1 ? $matches[0] : null;
}

/**
 * اجرای درخواست‌ها.
 * $links  : [domainId => foodId] برای تطبیق نام (یک‌به‌یک یا انتخاب همنام)
 * $manual : [domainId => کد واردشدهٔ مدیر]؛ در این مسیر کد دستی بر کد قبلی دامین اولویت دارد
 */
function fpl_apply(PDO $pdo, array $links, array $manual = []): array
{
    $candidates = fpl_candidates($pdo);
    $results = [];
    $usedFood = [];
    $pairs = [];
    $usedDomain = [];
    foreach ($links as $domainId => $foodId) {
        $domainId = (int) $domainId;
        $foodId = (int) $foodId;
        if ($foodId <= 0 || $domainId <= 0) {
            continue;
        }
        if (!fpl_pair_is_allowed($candidates, $domainId, $foodId)) {
            $results[] = ['domain' => $domainId, 'food' => $foodId, 'ok' => false, 'message' => 'این انتخاب دیگر معتبر نیست؛ فهرست را دوباره بارگذاری کنید.'];
            continue;
        }
        if (isset($usedFood[$foodId])) {
            $results[] = ['domain' => $domainId, 'food' => $foodId, 'ok' => false, 'message' => 'این رکورد پرسنل غذا قبلاً برای کاربر دیگری انتخاب شده است.'];
            continue;
        }
        $allowNameMismatch = false;
        foreach ($candidates as $candidate) {
            if ((int) ($candidate['domain']['id'] ?? 0) === $domainId && in_array($candidate['status'], ['code_match', 'code_multi'], true)) {
                $allowNameMismatch = true;
                break;
            }
        }
        $usedFood[$foodId] = true;
        $usedDomain[$domainId] = true;
        $pairs[] = [$domainId, $foodId, $allowNameMismatch, null];
    }
    foreach ($manual as $domainId => $code) {
        $domainId = (int) $domainId;
        $code = fpl_normalize_code((string) $code);
        if ($code === '' || $domainId <= 0 || isset($usedDomain[$domainId])) {
            continue;
        }
        $domainStmt = $pdo->prepare("SELECT id, is_active, auth_source FROM users WHERE id = ?");
        $domainStmt->execute([$domainId]);
        $domain = $domainStmt->fetch();
        if (!$domain || (string) $domain['auth_source'] !== 'ldap' || (int) $domain['is_active'] !== 1) {
            $results[] = ['domain' => $domainId, 'food' => 0, 'ok' => false, 'message' => 'کاربر دامین برای اتصال دستی معتبر نیست (غیرفعال یا دامین نیست).'];
            continue;
        }
        $food = fpl_find_food_by_code($pdo, $code);
        if ($food === null) {
            $results[] = ['domain' => $domainId, 'food' => 0, 'ok' => false, 'message' => 'پرسنل غذا با کد «' . $code . '» پیدا نشد (یا بیش از یک نفر دارد).'];
            continue;
        }
        $foodId = (int) $food['id'];
        if (isset($usedFood[$foodId])) {
            $results[] = ['domain' => $domainId, 'food' => $foodId, 'ok' => false, 'message' => 'این پرسنل غذا قبلاً برای کاربر دیگری انتخاب شده است.'];
            continue;
        }
        $usedFood[$foodId] = true;
        $usedDomain[$domainId] = true;
        $pairs[] = [$domainId, $foodId, true, $code];
    }
    if ($pairs === []) {
        return $results;
    }
    $backup = fpl_backup_users($pdo);
    foreach ($pairs as [$domainId, $foodId, $allowMismatch, $authoritativeCode]) {
        $foodName = (string) $pdo->query('SELECT full_name FROM users WHERE id = ' . (int) $foodId)->fetchColumn();
        $merge = fpl_merge($pdo, $domainId, $foodId, $allowMismatch, $authoritativeCode);
        $message = $merge['message'];
        if ($allowMismatch) {
            $message .= ' (اتصال دستی با پرسنل غذا: ' . $foodName . ')';
        }
        $results[] = ['domain' => $domainId, 'food' => $foodId, 'ok' => $merge['ok'], 'message' => $message, 'moved' => $merge['moved'], 'backup' => $backup];
    }
    return $results;
}

/** فیلد کد پرسنلی دستی برای کاربری که تطبیق نام ندارد یا در AD کدش به‌علت تداخل خالی مانده است. */
function fpl_manual_input(int $domainId, string $value = ''): string
{
    return '<div><small>کد واردشده در این فیلد بر کد قبلی دامین مقدم است؛ کد پرسنل غذا را وارد کنید (مثلاً 335).</small><br>'
        . '<input type="text" name="manual[' . $domainId . ']" value="' . e($value) . '" placeholder="کد پرسنلی" style="width:160px"></div>';
}

/** نقطهٔ ورود صفحه (از index.php صدا زده می‌شود). */
function fpl_render_request(): never
{
    $admin = require_primary_admin();
    $pdo = db();
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();
        $links = (array) ($_POST['links'] ?? []);
        $manual = (array) ($_POST['manual'] ?? []);
        $results = fpl_apply($pdo, $links, $manual);
        $_SESSION['fpl_last_results'] = $results;
        $okCount = count(array_filter($results, static fn (array $r): bool => $r['ok']));
        $failCount = count($results) - $okCount;
        if ($results === []) {
            flash('warning', 'هیچ انتخابی برای ادغام ثبت نشد.');
        } else {
            flash($failCount === 0 ? 'success' : 'warning', 'ادغام انجام شد: ' . $okCount . ' مورد موفق، ' . $failCount . ' مورد ناموفق.');
        }
        system_log('info', 'food_personnel_link', 'اتصال کاربران دامین به پرسنل غذا: ' . $okCount . ' موفق، ' . $failCount . ' ناموفق', ['by' => $admin['id'] ?? null]);
        redirect('index.php?page=food-personnel-link');
    }

    if (($_GET['download'] ?? '') === 'csv' || ($_GET['download'] ?? '') === 'xlsx') {
        fpl_report_csv(fpl_report_rows($pdo));
        exit;
    }

    $candidates = fpl_candidates($pdo);
    $lastResults = $_SESSION['fpl_last_results'] ?? [];
    unset($_SESSION['fpl_last_results']);

    $counts = ['single' => 0, 'multi' => 0, 'none' => 0, 'conflict' => 0, 'has_code' => 0, 'code_match' => 0, 'code_multi' => 0];
    foreach ($candidates as $item) {
        $counts[$item['status']]++;
    }

    render_header('اتصال کاربران دامین به پرسنل غذا', $admin);
    echo '<section class="page-heading"><div><span class="eyebrow">تنظیمات کاربران</span><h1>اتصال کاربران دامین به پرسنل غذا</h1>';
    echo '<p>کاربران دامین با رکوردهای پرسنل غذا و حساب‌های محلی کارکنان بر اساس نام و نام خانوادگی مقایسه می‌شوند. هیچ تغییری بدون تأیید شما انجام نمی‌شود.</p></div>';
    echo '<a class="button secondary" href="index.php?page=settings">بازگشت به تنظیمات</a></section>';

    if ($lastResults !== []) {
        echo '<div class="panel"><h2>نتیجهٔ آخرین اجرا</h2><ul>';
        foreach ($lastResults as $r) {
            $label = $r['ok'] ? 'موفق' : 'ناموفق';
            echo '<li>' . e($label) . ' — کاربر دامین #' . (int) $r['domain'] . ' با پرسنل غذا #' . (int) $r['food'] . ': ' . e((string) $r['message']);
            if (!empty($r['moved'])) {
                echo ' (' . e(implode('، ', array_map(static fn ($k, $v): string => $k . ' = ' . $v, array_keys($r['moved']), $r['moved']))) . ')';
            }
            if (!empty($r['backup'])) {
                echo ' — پشتیبان: ' . e((string) $r['backup']);
            }
            echo '</li>';
        }
        echo '</ul></div>';
    }

    $report = fpl_report_rows($pdo);
    $reportCounts = [];
    foreach ($report as $row) {
        $reportCounts[$row['category']] = ($reportCounts[$row['category']] ?? 0) + 1;
    }
    echo '<div class="panel"><h2>گزارش کاربران دامین</h2><ul>';
    foreach ($reportCounts as $category => $count) {
        echo '<li>' . e(str_replace('_', ' ', (string) $category)) . ': ' . (int) $count . '</li>';
    }
    echo '</ul><a class="button secondary" href="index.php?page=food-personnel-link&amp;download=xlsx">دانلود گزارش Excel (XLSX)</a></div>';

    echo '<div class="panel"><p>وضعیت: ' . $counts['single'] . ' تطبیق یک‌به‌یک، ' . $counts['multi'] . ' همنام (انتخاب دستی)، ' . $counts['code_match'] . ' اتصال با کد ثبت‌شدهٔ دامین، ' . $counts['code_multi'] . ' تکرار کد دامین، ' . $counts['conflict'] . ' کد ملی متفاوت، ' . $counts['none'] . ' بدون تطبیق، ' . $counts['has_code'] . ' از قبل کد پرسنلی دارد.</p></div>';

    echo '<form method="post" action="index.php?page=food-personnel-link">' . csrf_field();
    echo '<div class="panel"><table class="data-table"><thead><tr><th>کاربر دامین</th><th>نام کاربری</th><th>وضعیت</th><th>گزینه‌ها (کد پرسنلی / کد ملی کامل)</th></tr></thead><tbody>';
    foreach ($candidates as $item) {
        $domain = $item['domain'];
        if ($item['status'] === 'none' || $item['status'] === 'has_code') {
            $manualCode = (string) ($domain['employee_number'] ?? '');
            $status = $item['status'] === 'none' ? 'بدون تطبیق' : 'کد دامین ثبت‌شده؛ بدون رکورد هم‌کد در پرسنل غذا';
            echo '<tr><td>' . e((string) $domain['full_name']) . '</td><td>' . e((string) $domain['username']) . '</td><td>' . e($status) . '<br><small>' . e((string) $item['reason']) . '</small></td><td>' . fpl_manual_input((int) $domain['id'], $manualCode) . '</td></tr>';
            continue;
        }
        $domainId = (int) $domain['id'];
        echo '<tr><td>' . e((string) $domain['full_name']) . '</td><td>' . e((string) $domain['username']) . '</td>';
        $statusLabel = ['single' => 'یک تطبیق (پیش‌انتخاب)', 'multi' => 'همنام: انتخاب کنید', 'conflict' => 'کد ملی متفاوت', 'code_match' => 'کد پرسنلی دامین (اولویت‌دار)', 'code_multi' => 'کد پرسنلی دامین تکراری'][$item['status']] ?? '';
        echo '<td>' . e($statusLabel) . '</td><td>';
        echo '<label><input type="radio" name="links[' . $domainId . ']" value="0"' . ($item['chosen'] === 0 ? ' checked' : '') . '> جدا بماند</label><br>';
        foreach ($item['matches'] as $match) {
            $foodId = (int) $match['id'];
            $ok = !empty($match['compatible']);
            $checked = $ok && (int) $item['chosen'] === $foodId ? ' checked' : '';
            $text = 'کد پرسنلی: ' . (string) $match['employee_number'] . ' | کد ملی: ' . (string) ($match['national_code'] ?? '—') . ' | شناسه: ' . $foodId . ($match['is_active'] ? '' : ' | غیرفعال');
            if ($ok) {
                echo '<label><input type="radio" name="links[' . $domainId . ']" value="' . $foodId . '"' . $checked . '> ' . e($text) . '</label><br>';
            } else {
                echo '<label style="opacity:.6"><input type="radio" disabled> ' . e($text) . ' (کد ملی متفاوت با کاربر دامین)</label><br>';
            }
        }
        if ($item['reason'] !== '') {
            echo '<small>' . e((string) $item['reason']) . '</small>';
        }
        if (in_array($item['status'], ['conflict', 'code_multi'], true)) {
            echo '<br>' . fpl_manual_input($domainId, (string) ($domain['employee_number'] ?? ''));
        }
        echo '</td></tr>';
    }
    echo '</tbody></table></div>';
    echo '<div class="panel"><p>پیش از ثبت، فهرست را با دقت بررسی کنید. پشتیبان جدول کاربران قبل از اجرا ساخته می‌شود.</p>';
    echo '<button type="submit" class="button primary">ثبت اتصال‌های انتخاب‌شده</button></div></form>';
    render_footer();
    exit;
}

/** فهرست جدول‌های پشتیبان fpl (جدیدترین اول). */
function fpl_backup_tables(PDO $pdo): array
{
    $names = [];
    try {
        $rows = $pdo->query("SHOW TABLES LIKE 'users\\_backup\\_fpl\\_%'")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            $names[] = (string) $row[0];
        }
    } catch (Throwable $exception) {
        try {
            $rows = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name LIKE 'users_backup_fpl_%'")->fetchAll(PDO::FETCH_NUM);
            foreach ($rows as $row) {
                $names[] = (string) $row[0];
            }
        } catch (Throwable $inner) {
            return [];
        }
    }
    rsort($names);
    return $names;
}

/**
 * گزارش وضعیت همهٔ کاربران دامین.
 * دسته‌ها:
 *   تطبیق_شده      : در ادغام‌های قبلی به پرسنل غذا وصل شده (از جدول پشتیبان)
 *   دارای_کد        : کد پرسنلی دارد و ادغام لازم نیست
 *   تطبیق_یک‌به‌یک  : هنوز در فهرست است و یک گزینهٔ سازگار دارد
 *   همنام           : چند گزینه دارد؛ باید انتخاب شود
 *   کد_ملی_متفاوت  : نام یکسان ولی کد ملی متفاوت
 *   بدون_تطبیق      : هیچ تطبیق نامی ندارد (با پیشنهاد فامیلی مشابه)
 *
 * @return array<int, array<string, string>>
 */
function fpl_report_rows(PDO $pdo): array
{
    $domainRows = $pdo->query("SELECT id, username, full_name, employee_number, national_code, is_active FROM users WHERE auth_source = 'ldap' ORDER BY full_name, id")->fetchAll();
    $currentFood = $pdo->query("SELECT id, full_name, first_name, last_name, employee_number, national_code FROM users WHERE auth_source = 'food' OR (auth_source = 'local' AND role = 'local_user' AND COALESCE(is_primary_admin, 0) = 0)")->fetchAll();
    $currentFoodCodes = [];
    foreach ($currentFood as $food) {
        $currentFoodCodes[(string) $food['employee_number']] = true;
    }

    // پرسنل/حساب محلی حذف‌شده در ادغام‌ها (از جدول‌های پشتیبان)، کلید: کد پرسنلی
    $merged = [];
    foreach (fpl_backup_tables($pdo) as $table) {
        $stmt = $pdo->query('SELECT id, full_name, first_name, last_name, employee_number, national_code FROM `' . str_replace('`', '', $table) . "` WHERE auth_source = 'food' OR (auth_source = 'local' AND role = 'local_user' AND COALESCE(is_primary_admin, 0) = 0)");
        foreach ($stmt->fetchAll() as $old) {
            $code = (string) $old['employee_number'];
            if ($code !== '' && !isset($currentFoodCodes[$code]) && !isset($merged[$code])) {
                $merged[$code] = ['food' => $old, 'backup' => $table];
            }
        }
    }

    $candidates = [];
    foreach (fpl_candidates($pdo) as $item) {
        $candidates[(int) $item['domain']['id']] = $item;
    }

    $report = [];
    foreach ($domainRows as $domain) {
        $domainId = (int) $domain['id'];
        $code = trim((string) ($domain['employee_number'] ?? ''));
        $row = [
            'domain_id' => (string) $domainId,
            'username' => (string) $domain['username'],
            'full_name' => (string) $domain['full_name'],
            'employee_number' => $code,
            'national_code' => (string) ($domain['national_code'] ?? ''),
            'category' => '',
            'details' => '',
        ];
        if ((int) ($domain['is_active'] ?? 0) !== 1) {
            $row['category'] = 'غیرفعال_دامین';
            $row['details'] = 'حساب دامین غیرفعال است؛ در اتصال و ایمپورت لحاظ نمی‌شود.';
        } elseif ($code !== '' && isset($merged[$code])) {
            $old = $merged[$code]['food'];
            $row['category'] = 'تطبیق_شده';
            $row['details'] = 'پرسنل غذای قبلی: ' . (string) $old['full_name'] . ' | پشتیبان: ' . $merged[$code]['backup'];
        } elseif ($code !== '') {
            $row['category'] = 'دارای_کد';
            $row['details'] = 'کد پرسنلی از قبل ثبت شده است.';
        } else {
            $item = $candidates[$domainId] ?? ['status' => 'none', 'matches' => []];
            $map = ['single' => 'تطبیق_یک‌به‌یک', 'multi' => 'همنام', 'conflict' => 'کد_ملی_متفاوت', 'none' => 'بدون_تطبیق'];
            $row['category'] = $map[$item['status']] ?? 'بدون_تطبیق';
            $parts = [];
            foreach ($item['matches'] as $match) {
                $parts[] = (string) $match['full_name'] . ' (پرسنلی ' . (string) $match['employee_number'] . ', ملی ' . (string) ($match['national_code'] ?? '—') . ')';
            }
            if ($item['status'] === 'none') {
                $parts = fpl_similar_food_names($currentFood, (string) $domain['full_name']);
            }
            $row['details'] = implode(' | ', $parts);
        }
        $report[] = $row;
    }
    return $report;
}

/** پیشنهاد پرسنل غذا با فامیلی مشابه (برای اصلاح دستی). */
function fpl_similar_food_names(array $foodRows, string $domainFullName): array
{
    $parts = preg_split('/\s+/u', fpl_norm_name($domainFullName)) ?: [];
    $last = end($parts) ?: '';
    if ($last === '' || mb_strlen($last, 'UTF-8') < 2) {
        return [];
    }
    $suggestions = [];
    foreach ($foodRows as $food) {
        $foodParts = preg_split('/\s+/u', fpl_person_name($food)) ?: [];
        $foodLast = end($foodParts) ?: '';
        if ($foodLast === $last || str_contains(fpl_person_name($food), $last)) {
            $suggestions[] = (string) $food['full_name'] . ' (پرسنلی ' . (string) $food['employee_number'] . ', ملی ' . (string) ($food['national_code'] ?? '—') . ')';
        }
        if (count($suggestions) >= 5) {
            break;
        }
    }
    return $suggestions === [] ? ['پیشنهادی پیدا نشد؛ دستی بررسی شود.'] : array_map(static fn (string $s): string => 'پیشنهاد: ' . $s, $suggestions);
}

/** خروجی XLSX گزارش (فایل واقعی Excel). */
function fpl_report_csv(array $report): void
{
    $headers = ['شناسه دامین', 'نام کاربری', 'نام کامل', 'کد پرسنلی', 'کد ملی', 'وضعیت', 'جزئیات'];
    $keys = ['domain_id', 'username', 'full_name', 'employee_number', 'national_code', 'category', 'details'];
    $sheetRows = [array_map(static fn (string $h): array => ['v' => $h, 's' => 'header'], $headers)];
    foreach ($report as $row) {
        $line = [];
        foreach ($keys as $key) {
            $value = $row[$key] ?? '';
            $line[] = ['v' => is_scalar($value) ? (string) $value : '', 's' => 'body'];
        }
        $sheetRows[] = $line;
    }
    $bytes = xlsx_build([[
        'name' => 'گزارش پرسنل',
        'rows' => $sheetRows,
        'widths' => [16, 22, 24, 16, 16, 18, 40],
    ]]);
    xlsx_send('fpl-report-' . date('Ymd_His') . '.xlsx', $bytes);
}
