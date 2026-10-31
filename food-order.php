<?php
declare(strict_types=1);

/**
 * برنامه غذایی و سفارش داخلی.
 * فقط پنج جدول جدید این ماژول را ایجاد می‌کند؛ روی جدول‌های موجود DDL اجرا نمی‌شود.
 */

function food_order_schema_ensure(): bool
{
    static $ready = false;
    if ($ready) {
        return true;
    }
    $tables = ['food_catalog', 'food_calendar', 'food_calendar_items', 'food_orders', 'food_order_logs'];
    $placeholders = implode(',', array_fill(0, count($tables), '?'));
    try {
        $stmt = db()->prepare("SELECT TABLE_NAME FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$placeholders})");
        $stmt->execute($tables);
        $present = array_fill_keys(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
        $mealColumns = db()->prepare("SELECT TABLE_NAME FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('food_calendar', 'food_orders') AND COLUMN_NAME = 'meal_type'");
        $mealColumns->execute();
        $mealColumnsPresent = array_fill_keys(array_map('strval', $mealColumns->fetchAll(PDO::FETCH_COLUMN)), true);
    } catch (Throwable $e) {
        app_error_log('[food-order] schema check failed: ' . $e->getMessage());
        throw new RuntimeException('بررسی جدول‌های برنامه غذایی انجام نشد؛ وضعیت اتصال و دسترسی MySQL را بررسی کنید.', 0, $e);
    }
    $missing = array_values(array_filter($tables, static fn (string $table): bool => !isset($present[$table])));
    if ($missing !== []) {
        throw new RuntimeException('جدول‌های برنامه غذایی ایجاد نشده‌اند (' . implode(', ', $missing) . ')؛ ابتدا upgrade-1.38-food-orders.sql را اجرا کنید.');
    }
    $missingMealColumns = array_values(array_diff(['food_calendar', 'food_orders'], array_keys($mealColumnsPresent)));
    if ($missingMealColumns !== []) {
        throw new RuntimeException('تفکیک وعده‌های غذایی آماده نیست؛ مهاجرت 006-food-meal-type.sql را اعمال کنید.');
    }
    $ready = true;
    return true;
}

function food_order_normalize_digits(mixed $value): string
{
    return strtr((string) ($value ?? ''), [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ]);
}

function food_order_digits(mixed $value): string
{
    return preg_replace('/\D+/', '', food_order_normalize_digits($value)) ?? '';
}

function food_order_iso_date(mixed $value): ?string
{
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    $raw = trim(food_order_normalize_digits($value));
    if ($raw === '') {
        return null;
    }
    if (function_exists('food_ticket_iso_date_input')) {
        $parsed = food_ticket_iso_date_input($raw);
        if (is_string($parsed) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $parsed) && checkdate((int) substr($parsed, 5, 2), (int) substr($parsed, 8, 2), (int) substr($parsed, 0, 4))) {
            return $parsed;
        }
    }
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $raw, $m)) {
        $y = (int) $m[1];
        $mo = (int) $m[2];
        $d = (int) $m[3];
        if ($y >= 1700 && $y <= 2100 && checkdate($mo, $d, $y)) {
            return sprintf('%04d-%02d-%02d', $y, $mo, $d);
        }
        if ($y >= 1200 && $y <= 1600 && $mo >= 1 && $mo <= 12 && $d >= 1 && $d <= 31 && function_exists('jalali_to_gregorian')) {
            [$gy, $gm, $gd] = jalali_to_gregorian($y, $mo, $d);
            if (checkdate($gm, $gd, $gy)) {
                return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
            }
        }
    }
    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $raw, $m)) {
        return food_order_iso_date($m[1] . '-' . $m[2] . '-' . $m[3]);
    }
    if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $raw, $m)) {
        return food_order_iso_date($m[1] . '-' . $m[2] . '-' . $m[3]);
    }
    return null;
}

function food_order_timezone(): DateTimeZone
{
    $timezone = (string) (function_exists('cfg') ? cfg('app.timezone', 'Asia/Tehran') : 'Asia/Tehran');
    try {
        return new DateTimeZone($timezone !== '' ? $timezone : 'Asia/Tehran');
    } catch (Throwable) {
        return new DateTimeZone('Asia/Tehran');
    }
}

function food_order_now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', food_order_timezone());
}

function food_order_today(): string
{
    return food_order_now()->format('Y-m-d');
}

function food_order_jalali_label(string $isoDate): string
{
    $date = food_order_iso_date($isoDate);
    if ($date === null || !function_exists('gregorian_to_jalali')) {
        return $date ?? '';
    }
    [$jy, $jm, $jd] = gregorian_to_jalali((int) substr($date, 0, 4), (int) substr($date, 5, 2), (int) substr($date, 8, 2));
    return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
}

/** @return array{year:int,month:int,from:string,to:string,days:int} */
function food_order_jalali_month_range(string $month): array
{
    $month = trim(food_order_normalize_digits($month));
    if (!preg_match('/^(\d{4})[-\/](\d{1,2})$/', $month, $m)) {
        throw new RuntimeException('ماه شمسی معتبر نیست.');
    }
    $year = (int) $m[1];
    $monthNumber = (int) $m[2];
    if ($year < 1200 || $year > 1600 || $monthNumber < 1 || $monthNumber > 12 || !function_exists('jalali_to_gregorian')) {
        throw new RuntimeException('ماه شمسی خارج از بازهٔ مجاز است.');
    }
    [$gy, $gm, $gd] = jalali_to_gregorian($year, $monthNumber, 1);
    if (!checkdate($gm, $gd, $gy)) {
        throw new RuntimeException('تبدیل ماه شمسی معتبر نیست.');
    }
    if ($monthNumber === 12) {
        [$ny, $nm, $nd] = jalali_to_gregorian($year + 1, 1, 1);
    } else {
        [$ny, $nm, $nd] = jalali_to_gregorian($year, $monthNumber + 1, 1);
    }
    if (!checkdate($nm, $nd, $ny)) {
        throw new RuntimeException('مرز ماه شمسی معتبر نیست.');
    }
    $from = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $gy, $gm, $gd), new DateTimeZone('UTC'));
    $next = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $ny, $nm, $nd), new DateTimeZone('UTC'));
    $days = (int) $from->diff($next)->days;
    $to = $next->modify('-1 day');
    return ['year' => $year, 'month' => $monthNumber, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'days' => $days];
}

function food_order_is_primary_admin(array $user): bool
{
    return function_exists('user_is_primary_admin')
        ? user_is_primary_admin($user)
        : (((string) ($user['role'] ?? '') === 'primary_admin') || ((string) ($user['role'] ?? '') === 'admin' && (int) ($user['is_primary_admin'] ?? 0) === 1));
}

function food_order_user_can(array $user, string $permission): bool
{
    return function_exists('user_can') ? user_can($user, $permission) : food_order_is_primary_admin($user);
}

function food_order_require_permission(array $user, string $permission): void
{
    if (!$user || !food_order_user_can($user, $permission)) {
        throw new RuntimeException('دسترسی شما به این عملیات مجاز نیست.');
    }
}

/** Read-only calendar count access follows access to the food calendar itself. */
function food_order_can_view_order_counts(array $user): bool
{
    foreach (["food.menu", "food.order_close", "food.reports", "food.orders"] as $permission) {
        if (food_order_user_can($user, $permission)) {
            return true;
        }
    }
    return false;
}

function food_order_require_primary_admin(array $user): void
{
    if (!$user || !food_order_is_primary_admin($user)) {
        throw new RuntimeException('دسترسی به مدیریت مهلت اضطراری فقط برای ادمین اصلی مجاز است.');
    }
}

/** شخص موضوع خوداظهاری سلف: خود کاربر، یا همکار تأییدشده با کد ملی. */
function food_order_day_visit_subject(array $actor, array $body): array
{
    $selfId = (int) ($actor['id'] ?? 0);
    $employeeId = max(0, (int) ($body['employee_id'] ?? 0));
    if ($employeeId <= 0 || $employeeId === $selfId) {
        return $actor;
    }
    return food_order_verify_employee_national_code(
        $employeeId,
        (string) ($body['national_code'] ?? ''),
        true
    );
}

function food_order_log(?int $orderId, ?int $calendarId, string $action, ?int $userId, mixed $oldValue = null, mixed $newValue = null, ?string $reason = null): void
{
    $encode = static function (mixed $value): ?string {
        if ($value === null) {
            return null;
        }
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        return is_string($json) ? $json : (string) $value;
    };
    db()->prepare('INSERT INTO food_order_logs (order_id, calendar_id, action, user_id, old_value, new_value, reason) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$orderId, $calendarId, $action, $userId, $encode($oldValue), $encode($newValue), $reason !== null ? mb_substr($reason, 0, 500, 'UTF-8') : null]);
}

function food_order_audit(string $action, array $user, string $targetType, int $targetId, mixed $oldValue = null, mixed $newValue = null, ?string $reason = null): void
{
    if (!function_exists('food_ticket_audit')) {
        return;
    }
    food_ticket_audit($action, [
        'user_id' => (int) ($user['id'] ?? 0),
        'target_type' => $targetType,
        'target_id' => $targetId,
        'target_label' => $targetType . ':' . $targetId,
        'old' => $oldValue,
        'new' => $newValue,
        'reason' => $reason,
        'meta' => ['module' => 'food-order'],
    ]);
}

function food_order_raw_mode(): string
{
    $query = db()->prepare('SELECT value FROM settings WHERE `key` = ? LIMIT 1');
    $query->execute(['food_order_mode']);
    $value = $query->fetchColumn();
    return $value === false ? '' : strtoupper(trim((string) $value));
}

/**
 * Worker-safe source mode: direct SQL, 5-second cache (never setting()).
 * This build is internal-only for food orders. Legacy ACCESS/TEST values are treated as
 * INTERNAL-DB; they can never cause an ODBC order connection to be opened.
 */
function food_order_mode(bool $reload = false): string
{
    $cache = $GLOBALS['__food_order_mode_cache'] ?? null;
    $now = microtime(true);
    if (!$reload && is_array($cache) && isset($cache['at'], $cache['value']) && ($now - (float) $cache['at']) < 5.0) {
        return (string) $cache['value'];
    }
    // A MySQL/settings error intentionally propagates. It must not become an Access fallback or an empty map.
    $raw = food_order_raw_mode();
    if ($raw !== '' && !in_array($raw, ['INTERNAL-DB', 'INTERNAL_DB', 'INTERNAL', 'PRODUCTION', 'ACCESS', 'TEST'], true)) {
        throw new RuntimeException('وضعیت سفارش معتبر نیست.');
    }
    $GLOBALS['__food_order_mode_cache'] = ['at' => $now, 'value' => 'INTERNAL-DB'];
    return 'INTERNAL-DB';
}

function food_order_user_row(int $id): ?array
{
    if ($id < 1) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, username, full_name, first_name, last_name, employee_number, national_code, is_active FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function food_order_full_name(array $row): string
{
    $first = trim((string) ($row['first_name'] ?? ''));
    $last = trim((string) ($row['last_name'] ?? ''));
    $name = trim($first . ' ' . $last);
    return $name !== '' ? $name : trim((string) ($row['full_name'] ?? $row['username'] ?? ''));
}

function food_order_employee_search(string $query): array
{
    $query = trim(preg_replace('/[%_]+/u', '', $query) ?? $query);
    $where = "WHERE is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> ''";
    $params = [];
    if ($query !== '') {
        if (mb_strlen($query, 'UTF-8') < 2) {
            return [];
        }
        $like = '%' . mb_substr($query, 0, 60, 'UTF-8') . '%';
        $where .= ' AND (full_name LIKE ? OR first_name LIKE ? OR last_name LIKE ?)';
        $params = [$like, $like, $like];
    }
    // This legacy search is used by employee proxy flows; expose names only, never employee codes.
    $stmt = db()->prepare("SELECT id, full_name, first_name, last_name FROM users {$where} ORDER BY full_name, id");
    $stmt->execute($params);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $items[] = ['id' => (int) $row['id'], 'name' => food_order_full_name($row)];
    }
    return $items;
}

/** گزارش‌گیری مدیریتی: جست‌وجو با نام یا شمارهٔ پرسنلی؛ فقط endpoint مجاز گزارش از آن استفاده می‌کند. */
function food_order_report_employee_search(string $query): array
{
    $query = trim(preg_replace('/[%_]+/u', '', food_order_normalize_digits($query)) ?? $query);
    if ($query !== '' && mb_strlen($query, 'UTF-8') < 2) {
        return [];
    }
    $baseWhere = "is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> ''";
    if ($query === '') {
        $stmt = db()->prepare("SELECT id, full_name, first_name, last_name, employee_number FROM users
            WHERE {$baseWhere} ORDER BY full_name, last_name, first_name, id LIMIT 500");
        $stmt->execute();
    } else {
        $like = '%' . mb_substr($query, 0, 60, 'UTF-8') . '%';
        $stmt = db()->prepare("SELECT id, full_name, first_name, last_name, employee_number FROM users
            WHERE {$baseWhere} AND (full_name LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR employee_number LIKE ?)
            ORDER BY full_name, last_name, first_name, id LIMIT 50");
        $stmt->execute([$like, $like, $like, $like]);
    }
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $items[] = ['id' => (int) $row['id'], 'name' => food_order_full_name($row), 'employee_number' => (string) ($row['employee_number'] ?? '')];
    }
    return $items;
}

function food_order_name_key(string $name): string
{
    $key = strtr($name, ['ي' => 'ی', 'ك' => 'ک', 'ى' => 'ی', 'ة' => 'ه', "\u{200C}" => '', "\u{200B}" => '']);
    return (string) preg_replace('/\s+/u', '', mb_strtolower($key, 'UTF-8'));
}

/** بررسی وجود ستون category (برای سازگاری با دیتابیس قبل از به‌روزرسانی). */
function food_order_catalog_has_category(): bool
{
    static $has = null;
    if ($has !== null) {
        return $has;
    }
    try {
        $stmt = db()->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_catalog' AND COLUMN_NAME = 'category'");
        $has = (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        $has = false;
    }
    return $has;
}

function food_order_catalog(bool $includeInactive = true): array
{
    food_order_schema_ensure();
    $hasCategory = food_order_catalog_has_category();
    $sql = $hasCategory ? 'SELECT id, food_name, category, active FROM food_catalog' : 'SELECT id, food_name, NULL AS category, active FROM food_catalog';
    if (!$includeInactive) {
        $sql .= ' WHERE active = 1';
    }
    $sql .= ' ORDER BY active DESC, food_name, id';
    $rows = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    return array_map(static fn (array $r): array => [
        'id' => (int) $r['id'],
        'food_name' => (string) $r['food_name'],
        'category' => $r['category'] !== null && $r['category'] !== '' ? (string) $r['category'] : 'سایر',
        'active' => (bool) $r['active'],
    ], $rows);
}

/**
 * ماه ورودی شمسی است. فقط سفارش‌های صاحب غذا در پاسخ کارمند قرار می‌گیرد؛ created_by هرگز فیلتر نمایش نیست.
 */
function food_order_meal_type(mixed $value): string
{
    $meal = strtolower(trim((string) ($value ?? 'lunch')));
    if (!in_array($meal, ['lunch', 'dinner'], true)) {
        throw new RuntimeException('وعدهٔ غذایی معتبر نیست.');
    }
    return $meal;
}

function food_order_month_status(string $month, array $user, bool $includeCounts = false, bool $includeInactiveFoods = false, string $mealType = 'lunch'): array
{
    food_order_schema_ensure();
    $mealType = food_order_meal_type($mealType);
    $range = food_order_jalali_month_range($month);
    $employeeId = (int) ($user['id'] ?? 0);
    $countSelect = $includeCounts ? 'COUNT(o.id)' : '0';
    $foodJoin = $includeInactiveFoods ? 'f.id = i.food_id' : 'f.id = i.food_id AND f.active = 1';
    $countJoin = $includeCounts ? " LEFT JOIN food_orders o ON o.calendar_item_id = i.id AND o.food_date = c.food_date AND o.meal_type = c.meal_type AND o.status = 'active'" : '';
    $groupBy = $includeCounts ? ' GROUP BY c.id, c.food_date, c.meal_type, c.order_status, c.note, i.id, i.food_id, i.sort_order, f.food_name, f.active' : '';
    $stmt = db()->prepare("SELECT c.id AS calendar_id, c.food_date, c.meal_type, c.order_status, c.note,
            i.id AS item_id, i.food_id, i.sort_order, f.food_name, f.active AS food_active, {$countSelect} AS order_count
        FROM food_calendar c
        LEFT JOIN food_calendar_items i ON i.calendar_id = c.id AND i.active = 1
        LEFT JOIN food_catalog f ON {$foodJoin}
        {$countJoin}
        WHERE c.food_date BETWEEN ? AND ? AND c.meal_type = ?{$groupBy}
        ORDER BY c.food_date, i.sort_order, i.id");
    $stmt->execute([$range['from'], $range['to'], $mealType]);
    $days = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $date = (string) $row['food_date'];
        if (!isset($days[$date])) {
            $days[$date] = [
                'date' => $date,
                'meal_type' => $mealType,
                'calendar_id' => (int) $row['calendar_id'],
                'order_status' => (string) $row['order_status'],
                'note' => (string) ($row['note'] ?? ''),
                'items' => [],
                'my_order' => null,
                'holiday_title' => '',
            ];
        }
        if (!empty($row['item_id']) && $row['food_name'] !== null) {
            $item = [
                'item_id' => (int) $row['item_id'],
                'food_id' => (int) $row['food_id'],
                'food_name' => (string) $row['food_name'],
                'food_active' => (bool) ($row['food_active'] ?? false),
                'sort_order' => (int) $row['sort_order'],
            ];
            if ($includeCounts) {
                $item['order_count'] = (int) $row['order_count'];
            }
            $days[$date]['items'][] = $item;
        }
    }

    $holidayStmt = db()->prepare('SELECT holiday_date, title FROM holidays WHERE is_active = 1 AND holiday_date BETWEEN ? AND ?');
    $holidayStmt->execute([$range['from'], $range['to']]);
    foreach ($holidayStmt->fetchAll(PDO::FETCH_ASSOC) as $holiday) {
        $date = (string) $holiday['holiday_date'];
        if (!isset($days[$date])) {
            $days[$date] = [
                'date' => $date,
                'calendar_id' => 0,
                'order_status' => '',
                'note' => '',
                'items' => [],
                'my_order' => null,
                'holiday_title' => '',
            ];
        }
        $days[$date]['holiday_title'] = (string) $holiday['title'];
    }

    $windowStates = food_order_emergency_window_states(array_map(static fn (array $day): int => (int) ($day['calendar_id'] ?? 0), $days));
    foreach ($days as $date => &$day) {
        $calendarId = (int) ($day['calendar_id'] ?? 0);
        $window = $windowStates[$calendarId] ?? ['active' => false, 'expires_at' => '', 'reason' => ''];
        $day['late_order_window_open'] = !empty($window['active']);
        if ($includeCounts) {
            $day['late_order_window_expires_at'] = (string) ($window['expires_at'] ?? '');
            $day['late_order_window_reason'] = (string) ($window['reason'] ?? '');
            $day['late_order_window_expires_label'] = !empty($window['expires_at'])
                ? food_order_jalali_label(substr((string) $window['expires_at'], 0, 10)) . ' ' . substr((string) $window['expires_at'], 11, 5)
                : '';
        }
    }
    unset($day);

    if ($employeeId > 0) {
        $own = db()->prepare("SELECT o.id, o.food_date, o.status, o.calendar_item_id, o.created_at, i.food_id, c.food_name
            FROM food_orders o
            JOIN food_calendar_items i ON i.id = o.calendar_item_id
            JOIN food_catalog c ON c.id = i.food_id
            WHERE o.employee_id = ? AND o.food_date BETWEEN ? AND ? AND o.meal_type = ? AND o.status = 'active'");
        $own->execute([$employeeId, $range['from'], $range['to'], $mealType]);
        foreach ($own->fetchAll(PDO::FETCH_ASSOC) as $order) {
            $date = (string) $order['food_date'];
            if (!isset($days[$date])) {
                $days[$date] = [
                    'date' => $date,
                    'calendar_id' => 0,
                    'order_status' => '',
                    'note' => '',
                    'items' => [],
                    'my_order' => null,
                    'holiday_title' => '',
                ];
            }
            $days[$date]['my_order'] = [
                'id' => (int) $order['id'],
                'food_id' => (int) $order['food_id'],
                'item_id' => (int) $order['calendar_item_id'],
                'food_name' => (string) $order['food_name'],
                'reserve_date' => substr((string) $order['created_at'], 0, 10),
                'reserve_time' => substr((string) $order['created_at'], 11, 8),
                'cancel_request' => null,
            ];
        }
        $pending = food_order_pending_cancel_map(array_map(static fn (array $day): int => (int) (($day['my_order']['id'] ?? 0)), $days));
        foreach ($days as $date => $day) {
            $oid = (int) (($day['my_order']['id'] ?? 0));
            if ($oid > 0 && isset($pending[$oid])) {
                $days[$date]['my_order']['cancel_request'] = $pending[$oid];
            }
        }
    }
    ksort($days, SORT_STRING);
    return [
        'month' => sprintf('%04d-%02d', $range['year'], $range['month']),
        'month_start' => $range['from'],
        'month_end' => $range['to'],
        'month_days' => $range['days'],
        'today' => food_order_today(),
        'days' => $days,
    ];
}

/**
 * تبدیل ورودی تاریخ بازهٔ لیست به Y-m-d میلادی.
 * پذیرش: شمسی (۱۴۰۵/۰۷/۰۱ یا ۱۴۰۵-۰۷-۰۱، با ارقام فارسی) یا میلادی (۲۰۲۶-۱۰-۰۱).
 * خالی ⇒ null.
 */
function food_order_list_date(string $raw): ?string
{
    $s = trim(food_order_normalize_digits($raw));
    if ($s === '') {
        return null;
    }
    if (!preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $s, $m)) {
        throw new RuntimeException('تاریخ «' . $raw . '» معتبر نیست؛ مثال: ۱۴۰۵/۰۷/۰۱');
    }
    $y = (int) $m[1];
    $mo = (int) $m[2];
    $d = (int) $m[3];
    if ($y >= 1200 && $y <= 1600) {
        if (!function_exists('jalali_to_gregorian') || $mo < 1 || $mo > 12 || $d < 1 || $d > 31) {
            throw new RuntimeException('تاریخ شمسی «' . $raw . '» معتبر نیست.');
        }
        [$gy, $gm, $gd] = jalali_to_gregorian($y, $mo, $d);
        // بررسی برگشتی: روز ۳۱ مهر یا ۳۰ بهمن (طول ماه شمسی) را رد می‌کند؛ بدون آن به روز بعد می‌رفت.
        if (!checkdate($gm, $gd, $gy) || gregorian_to_jalali($gy, $gm, $gd) !== [$y, $mo, $d]) {
            throw new RuntimeException('تاریخ شمسی «' . $raw . '» معتبر نیست.');
        }
    } else {
        [$gy, $gm, $gd] = [$y, $mo, $d];
    }
    if (!checkdate($gm, $gd, $gy)) {
        throw new RuntimeException('تاریخ «' . $raw . '» معتبر نیست.');
    }
    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
}

/**
 * بازهٔ نمایش سفارش‌ها.
 * پیش‌فرض: از امروز تا پایان ماه شمسی بعد (و اگر سفارش دیرتری باشد تا همان تاریخ).
 * غذای روزهای گذشته از فهرست پیش‌فرض خارج می‌شود؛ سفارش ماه بعد پنهان نمی‌ماند.
 * کاربر می‌تواند هر بازه‌ای (حداکثر ۴۰۰ روز) را انتخاب کند.
 * @return array{from:string,to:string}
 */
function food_order_list_range(string $fromRaw, string $toRaw, int $employeeId = 0): array
{
    $today = food_order_today();
    [$jy, $jm] = function_exists('gregorian_to_jalali')
        ? gregorian_to_jalali((int) substr($today, 0, 4), (int) substr($today, 5, 2), (int) substr($today, 8, 2))
        : [0, 0];
    $monthEnd = $jy > 0 ? food_order_jalali_month_range(sprintf('%04d/%02d', $jy, $jm))['to'] : $today;
    $nextMonthEnd = $monthEnd;
    if ($jy > 0) {
        $nextKey = $jm === 12
            ? sprintf('%04d/%02d', $jy + 1, 1)
            : sprintf('%04d/%02d', $jy, $jm + 1);
        $nextMonthEnd = food_order_jalali_month_range($nextKey)['to'];
    }
    $defaultTo = $nextMonthEnd > $monthEnd ? $nextMonthEnd : $monthEnd;
    if ($employeeId > 0 && function_exists('db')) {
        try {
            $latestStmt = db()->prepare('SELECT MAX(food_date) FROM food_orders WHERE employee_id = ? AND food_date >= ?');
            $latestStmt->execute([$employeeId, $today]);
            $latest = $latestStmt->fetchColumn();
            if (is_string($latest) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $latest) && $latest > $defaultTo) {
                $defaultTo = $latest;
            }
        } catch (Throwable) {
        }
    }
    $maxTo = (new DateTimeImmutable($today, new DateTimeZone('UTC')))->modify('+400 days')->format('Y-m-d');
    if ($defaultTo > $maxTo) {
        $defaultTo = $maxTo;
    }

    $from = food_order_list_date($fromRaw);
    $to = food_order_list_date($toRaw);
    if ($from === null && $to === null) {
        $from = $today;
        $to = $defaultTo;
    } elseif ($from === null) {
        $from = min($today, (string) $to);
    } elseif ($to === null) {
        $to = $defaultTo;
    }
    if ($from > $to) {
        throw new RuntimeException('تاریخ شروع بازه نباید بعد از تاریخ پایان باشد.');
    }
    $days = (int) (new DateTimeImmutable($from, new DateTimeZone('UTC')))->diff(new DateTimeImmutable($to, new DateTimeZone('UTC')))->days;
    if ($days > 400) {
        throw new RuntimeException('بازهٔ انتخاب‌شده بیش از ۴۰۰ روز است؛ بازه را کوتاه‌تر کنید.');
    }
    return ['from' => $from, 'to' => $to];
}

/**
 * سفارش‌های یک کارمند در بازه. اگر $from/$to داده نشود، همهٔ سفارش‌ها (تا $limit) برگردانده می‌شود.
 */
function food_order_my_orders(int $employeeId, int $limit = 120, ?string $from = null, ?string $to = null): array
{
    food_order_schema_ensure();
    $limit = max(1, min(400, $limit));
    $range = ($from !== null && $to !== null) ? ' AND o.food_date BETWEEN ? AND ?' : '';
    $params = [$employeeId];
    if ($range !== '') {
        array_push($params, $from, $to);
    }
    $stmt = db()->prepare("SELECT o.id, o.employee_id, o.calendar_item_id, o.food_date, o.status, o.created_at,
            (SELECT cal.order_status FROM food_calendar cal WHERE cal.food_date = o.food_date AND cal.meal_type = o.meal_type LIMIT 1) AS day_status,
            o.meal_type,
            u.full_name, u.first_name, u.last_name, u.national_code, c.food_name, i.food_id
        FROM food_orders o
        JOIN users u ON u.id = o.employee_id
        JOIN food_calendar_items i ON i.id = o.calendar_item_id
        JOIN food_catalog c ON c.id = i.food_id
        WHERE o.employee_id = ?{$range}
        ORDER BY o.food_date DESC, o.id DESC LIMIT {$limit}");
    $stmt->execute($params);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $created = (string) $row['created_at'];
        $items[] = [
            'id' => (int) $row['id'],
            'employee_id' => (int) $row['employee_id'],
            'national_code' => food_order_digits($row['national_code'] ?? ''),
            'first_name' => trim((string) ($row['first_name'] ?? '')),
            'last_name' => trim((string) ($row['last_name'] ?? '')),
            'full_name' => food_order_full_name($row),
            'food_name' => (string) $row['food_name'],
            'food_id' => (int) $row['food_id'],
            'food_date' => (string) $row['food_date'],
            'meal_type' => (string) $row['meal_type'],
            'reserve_date' => substr($created, 0, 10),
            'reserve_time' => substr($created, 11, 8),
            'status' => (string) $row['status'],
            'day_status' => (string) ($row['day_status'] ?? ''),
            'cancel_request' => null,
        ];
    }
    $pending = food_order_pending_cancel_map(array_column($items, 'id'));
    foreach ($items as &$item) {
        $oid = (int) $item['id'];
        if (isset($pending[$oid])) {
            $item['cancel_request'] = $pending[$oid];
        }
    }
    unset($item);
    return $items;
}

function food_order_manager_orders(string $date, string $mealType = 'lunch'): array
{
    food_order_schema_ensure();
    $iso = food_order_iso_date($date);
    $mealType = food_order_meal_type($mealType);
    if ($iso === null) {
        throw new RuntimeException('تاریخ روز سفارش معتبر نیست.');
    }
    $stmt = db()->prepare("SELECT o.id, o.employee_id, o.calendar_item_id, o.food_date, o.meal_type, o.status, o.created_at, o.created_by,
            u.full_name, u.first_name, u.last_name, u.employee_number, u.national_code, c.food_name, i.food_id, i.sort_order AS menu_no
        FROM food_orders o
        JOIN users u ON u.id = o.employee_id
        JOIN food_calendar_items i ON i.id = o.calendar_item_id
        JOIN food_catalog c ON c.id = i.food_id
        WHERE o.food_date = ? AND o.meal_type = ?
        ORDER BY CASE WHEN o.status = 'active' THEN 0 ELSE 1 END, u.last_name, u.first_name, u.full_name, o.id");
    $stmt->execute([$iso, $mealType]);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $created = (string) $row['created_at'];
        $employeeId = (int) $row['employee_id'];
        $items[] = [
            'id' => (int) $row['id'],
            'employee_id' => $employeeId,
            'calendar_item_id' => (int) $row['calendar_item_id'],
            'food_id' => (int) $row['food_id'],
            'menu_no' => (int) ($row['menu_no'] ?? 0),
            'national_code' => food_order_digits($row['national_code'] ?? ''),
            'employee_number' => (string) ($row['employee_number'] ?? ''),
            'first_name' => trim((string) ($row['first_name'] ?? '')),
            'last_name' => trim((string) ($row['last_name'] ?? '')),
            'full_name' => food_order_full_name($row),
            'food_name' => (string) $row['food_name'],
            'food_date' => (string) $row['food_date'],
            'meal_type' => (string) $row['meal_type'],
            'reserve_date' => substr($created, 0, 10),
            'reserve_time' => substr($created, 11, 8),
            'status' => (string) $row['status'],
            'is_proxy' => (int) ($row['created_by'] ?? 0) !== $employeeId,
        ];
    }
    return $items;
}

/** سفارش‌های فعال/لغوشدهٔ اعضای گروه در روز انتخابی (با جابه‌جایی روزانهٔ گروه لحاظ‌شده). */
function food_order_group_orders(int $groupId, string $date, string $mealType = 'lunch'): array
{
    $iso = food_order_iso_date($date);
    $mealType = food_order_meal_type($mealType);
    if ($iso === null || !function_exists('food_ticket_groups_load') || !function_exists('food_ticket_group_members_for_date')) {
        throw new RuntimeException('گروه یا تاریخ گزارش معتبر نیست.');
    }
    $groups = array_values(array_filter(
        food_ticket_groups_load(true),
        static fn (array $candidate): bool => (string) ($candidate['status'] ?? '') === 'active'
    ));
    $orders = food_order_manager_orders($iso, $mealType);

    if ($groupId === 0) {
        $groupResults = [];
        $allItems = [];
        foreach ($groups as $group) {
            $id = (int) ($group['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $memberIds = array_fill_keys(array_map(
                static fn (array $member): int => (int) ($member['id'] ?? 0),
                food_ticket_group_members_for_date($id, $iso)
            ), true);
            $items = array_values(array_filter(
                $orders,
                static fn (array $order): bool => isset($memberIds[(int) ($order['employee_id'] ?? 0)])
            ));
            $allItems = array_merge($allItems, $items);
            $groupResults[] = ['id' => $id, 'title' => (string) ($group['title'] ?? ''), 'items' => $items];
        }
        return [
            'group' => ['id' => 0, 'title' => 'همهٔ گروه‌ها'],
            'date' => $iso,
            'meal_type' => $mealType,
            'groups' => $groupResults,
            'items' => $allItems,
        ];
    }
    if ($groupId < 0) {
        throw new RuntimeException('گروه یا تاریخ گزارش معتبر نیست.');
    }
    $group = null;
    foreach ($groups as $candidate) {
        if ((int) ($candidate['id'] ?? 0) === $groupId) {
            $group = $candidate;
            break;
        }
    }
    if ($group === null) {
        throw new RuntimeException('گروه انتخاب‌شده پیدا نشد.');
    }
    $members = food_ticket_group_members_for_date($groupId, $iso);
    $memberIds = array_fill_keys(array_map(static fn (array $member): int => (int) ($member['id'] ?? 0), $members), true);
    $groupOrders = array_values(array_filter($orders, static fn (array $order): bool => isset($memberIds[(int) ($order['employee_id'] ?? 0)])));
    return [
        'group' => ['id' => $groupId, 'title' => (string) $group['title']],
        'date' => $iso,
        'meal_type' => $mealType,
        'groups' => [['id' => $groupId, 'title' => (string) $group['title'], 'items' => $groupOrders]],
        'items' => $groupOrders,
    ];
}

function food_order_statistics_range(string $fromDate, string $toDate, string $mode = 'food', string $mealType = 'lunch'): array
{
    food_order_schema_ensure();
    $mode = strtolower(trim($mode));
    $mealType = food_order_meal_type($mealType);
    if (!in_array($mode, ['food', 'employee'], true)) {
        throw new RuntimeException('نوع آمار سفارش معتبر نیست.');
    }
    $from = food_order_iso_date($fromDate);
    $to = food_order_iso_date($toDate);
    if ($from === null || $to === null || $from > $to) {
        throw new RuntimeException('بازهٔ تاریخ آمار معتبر نیست.');
    }
    $span = (new DateTimeImmutable($from, new DateTimeZone('UTC')))->diff(new DateTimeImmutable($to, new DateTimeZone('UTC')))->days;
    if ($span > 366) {
        throw new RuntimeException('بازهٔ آمار حداکثر یک سال است.');
    }
    if ($mode === 'employee') {
        $stmt = db()->prepare("SELECT u.id AS employee_id, u.full_name, u.first_name, u.last_name, COUNT(o.id) AS order_count
            FROM food_orders o
            JOIN users u ON u.id = o.employee_id
            WHERE o.food_date BETWEEN ? AND ? AND o.meal_type = ? AND o.status = 'active'
            GROUP BY u.id, u.full_name, u.first_name, u.last_name
            ORDER BY order_count DESC, u.last_name, u.first_name, u.id");
    } else {
        $stmt = db()->prepare("SELECT o.food_date, f.id AS food_id, f.food_name, COUNT(o.id) AS order_count
            FROM food_orders o
            JOIN food_calendar_items i ON i.id = o.calendar_item_id
            JOIN food_catalog f ON f.id = i.food_id
            WHERE o.food_date BETWEEN ? AND ? AND o.meal_type = ? AND o.status = 'active'
            GROUP BY o.food_date, f.id, f.food_name
            ORDER BY o.food_date, f.food_name, f.id");
    }
    $stmt->execute([$from, $to, $mealType]);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ($mode === 'employee') {
            $items[] = [
                'full_name' => food_order_full_name($row),
                'count' => (int) $row['order_count'],
            ];
        } else {
            $date = (string) $row['food_date'];
            if (!isset($items[$date])) {
                $weekday = (int) (new DateTimeImmutable($date, new DateTimeZone('UTC')))->format('N');
                $weekdayIndex = ($weekday + 1) % 7; // Saturday=0 … Friday=6.
                $weekdays = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];
                $items[$date] = [
                    'food_date' => $date,
                    'food_date_jalali' => food_order_jalali_label($date),
                    'weekday' => $weekdays[$weekdayIndex],
                    'foods' => [],
                ];
            }
            $items[$date]['foods'][] = [
                'food_id' => (int) $row['food_id'],
                'food_name' => (string) $row['food_name'],
                'count' => (int) $row['order_count'],
            ];
        }
    }
    return $mode === 'food' ? array_values($items) : $items;
}

/** گزارش ردیفی فیش‌های چاپ‌شده و وضعیت تحویل، به‌علاوهٔ فیش‌های چاپ‌شدهٔ مهمان. */
function food_order_printed_delivery_report(string $fromDate, string $toDate): array
{
    food_order_schema_ensure();
    $from = food_order_iso_date($fromDate);
    $to = food_order_iso_date($toDate);
    if ($from === null || $to === null || $from > $to) {
        throw new RuntimeException('بازهٔ تاریخ گزارش فیش‌ها معتبر نیست.');
    }
    $span = (new DateTimeImmutable($from, new DateTimeZone('UTC')))->diff(new DateTimeImmutable($to, new DateTimeZone('UTC')))->days;
    if ($span > 366) {
        throw new RuntimeException('بازهٔ گزارش حداکثر یک سال است.');
    }
    if (function_exists('db_table_exists') && !db_table_exists('food_ticket_events')) {
        return ['items' => [], 'employee_count' => 0, 'guest_count' => 0];
    }
    if (function_exists('food_ticket_events_ensure_delivery') && !food_ticket_events_ensure_delivery()) {
        throw new RuntimeException('ستون‌های وضعیت تحویل غذا در پایگاه‌داده در دسترس نیستند.');
    }
    if (function_exists('food_ticket_manual_attendance_reconcile_range')) {
        food_ticket_manual_attendance_reconcile_range($from, $to);
    }

    // رویداد فیش منبع ردیف‌هاست؛ منو فقط به‌عنوان مشخصه از برنامهٔ همان روز خوانده می‌شود.
    $menuMap = [];
    try {
        $menuStmt = db()->prepare("SELECT cal.food_date, c.food_name, i.sort_order
            FROM food_calendar cal
            JOIN food_calendar_items i ON i.calendar_id = cal.id
            JOIN food_catalog c ON c.id = i.food_id
            WHERE cal.food_date BETWEEN ? AND ?
            ORDER BY cal.food_date, (cal.meal_type = 'lunch') DESC, i.sort_order");
        $menuStmt->execute([$from, $to]);
        foreach ($menuStmt->fetchAll(PDO::FETCH_ASSOC) as $menuRow) {
            $food = trim((string) ($menuRow['food_name'] ?? ''));
            $date = (string) ($menuRow['food_date'] ?? '');
            $key = $date . "\0" . $food;
            if ($food !== '' && !isset($menuMap[$key])) {
                $menuMap[$key] = (int) ($menuRow['sort_order'] ?? 0);
            }
        }
    } catch (Throwable $menuError) {
        $menuMap = []; // اگر برنامهٔ روز در دسترس نباشد، ردیف‌های فیش همچنان گزارش می‌شوند.
    }

    $stmt = db()->prepare("SELECT e.id, e.user_id, e.punch_date AS food_date, e.punch_time,
            COALESCE(NULLIF(e.personnel_code, ''), u.employee_number, '') AS employee_number,
            COALESCE(NULLIF(e.national_code, ''), u.national_code, '') AS national_code,
            COALESCE(NULLIF(e.full_name, ''), u.full_name, 'مهمان') AS full_name,
            COALESCE(NULLIF(e.food_type, ''), IF(e.event_type = 'guest', 'مهمان', '')) AS food_name,
            e.event_type, e.print_status, e.delivery_status, e.delivered_at
        FROM food_ticket_events e
        LEFT JOIN users u ON u.id = e.user_id
        WHERE e.punch_date BETWEEN ? AND ?
          AND (e.print_status = 'printed' OR (e.source_uid = 'manual-attendance' AND e.event_type = 'printed' AND e.delivery_status = 'delivered'))
          AND ((e.event_type = 'printed' AND e.user_id IS NOT NULL AND e.delivery_status = 'delivered')
               OR e.event_type = 'guest')
        ORDER BY e.punch_date, e.food_type, e.full_name, e.id");
    $stmt->execute([$from, $to]);
    $items = [];
    $employeeIds = [];
    $guestCount = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $date = (string) ($row['food_date'] ?? '');
        $food = trim((string) ($row['food_name'] ?? ''));
        $eventType = (string) ($row['event_type'] ?? '');
        $userId = (int) ($row['user_id'] ?? 0);
        if ($eventType === 'guest') {
            $guestCount++;
        } else {
            $employeeIds[$userId] = true;
        }
        $items[] = [
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => $userId,
            'food_date' => $date,
            'punch_time' => (string) ($row['punch_time'] ?? ''),
            'employee_number' => (string) ($row['employee_number'] ?? ''),
            'national_code' => food_order_digits($row['national_code'] ?? ''),
            'full_name' => (string) ($row['full_name'] ?? ''),
            'food_name' => $food,
            'event_type' => $eventType,
            'menu_no' => (int) ($menuMap[$date . "\0" . $food] ?? 0),
            'delivered_at' => (string) ($row['delivered_at'] ?? ''),
        ];
    }
    return ['items' => $items, 'employee_count' => count($employeeIds), 'guest_count' => $guestCount];
}

function food_order_export_printed_delivery_report(string $fromDate, string $toDate, array $user, array $requestedFields, string $view = 'list'): never
{
    food_order_require_permission($user, 'food.order_close');
    $report = food_order_printed_delivery_report($fromDate, $toDate);
    $labels = [
        'row' => 'ردیف', 'national_code' => 'کد ملی', 'employee_number' => 'کد پرسنلی',
        'full_name' => 'نام و نام خانوادگی', 'food_name' => 'نوع غذا', 'food_date' => 'تاریخ غذا',
        'punch_time' => 'ساعت تردد', 'record_type' => 'نوع فیش', 'menu_no' => 'شماره منو',
        'delivered_at' => 'زمان ثبت تحویل',
    ];
    $fields = array_values(array_unique(array_filter($requestedFields, static fn ($field): bool => is_string($field) && isset($labels[$field]))));
    if (!$fields) {
        $fields = ['row', 'employee_number', 'full_name', 'food_name', 'food_date', 'punch_time', 'record_type', 'menu_no'];
    }
    $items = $report['items'];
    if ($view === 'menu') {
        usort($items, static fn (array $a, array $b): int => ((int) $a['menu_no'] <=> (int) $b['menu_no'])
            ?: strcmp((string) $a['food_name'], (string) $b['food_name'])
            ?: strcmp((string) $a['full_name'], (string) $b['full_name']));
    }
    $headers = array_map(static fn (string $field): string => $labels[$field], $fields);
    $rows = [];
    foreach ($items as $index => $item) {
        $row = [];
        foreach ($fields as $field) {
            $value = match ($field) {
                'row' => $index + 1,
                'national_code' => food_order_digits($item['national_code'] ?? ''),
                'employee_number' => (string) ($item['employee_number'] ?? ''),
                'full_name', 'food_name' => (string) ($item[$field] ?? ''),
                'food_date' => food_order_jalali_label((string) ($item['food_date'] ?? '')),
                'punch_time' => substr((string) ($item['punch_time'] ?? ''), 0, 5),
                'record_type' => ($item['event_type'] ?? '') === 'guest' ? 'مهمان' : 'پرسنل',
                'menu_no' => (int) ($item['menu_no'] ?? 0) > 0 ? 'منوی ' . food_order_digits($item['menu_no']) : (($item['event_type'] ?? '') === 'guest' ? 'مهمان' : 'بدون منو'),
                'delivered_at' => (string) ($item['delivered_at'] ?? ''),
                default => '',
            };
            $row[] = food_order_export_safe_value($value);
        }
        $rows[] = $row;
    }
    food_order_audit('food_ticket_printed_delivery_export', $user, 'food_ticket_events', 0, null, [
        'from_date' => $fromDate, 'to_date' => $toDate, 'row_count' => count($rows), 'view' => $view,
    ]);
    $from = food_order_iso_date($fromDate) ?? $fromDate;
    $to = food_order_iso_date($toDate) ?? $toDate;
    $range = food_order_jalali_label($from) . ($from === $to ? '' : ' تا ' . food_order_jalali_label($to));
    food_order_send_xlsx('فیش‌های-تحویل‌شده-' . str_replace('-', '', $from) . '-' . str_replace('-', '', $to) . '.xlsx', 'فهرست فیش‌های چاپ‌شده و تحویل‌شده', $headers, $rows, $range);
}

/** جزئیات سفارش‌های فعالِ کارکنان انتخاب‌شده در یک بازهٔ محدود و قابل ممیزی. */
function food_order_selected_orders_range(string $fromDate, string $toDate, array $employeeIds, string $mealType = 'lunch'): array
{
    food_order_schema_ensure();
    $from = food_order_iso_date($fromDate);
    $to = food_order_iso_date($toDate);
    $mealType = food_order_meal_type($mealType);
    $ids = array_values(array_unique(array_filter(array_map('intval', $employeeIds), static fn (int $id): bool => $id > 0)));
    if ($from === null || $to === null || $from > $to) {
        throw new RuntimeException('بازهٔ تاریخ سفارش معتبر نیست.');
    }
    $span = (int) (new DateTimeImmutable($from, new DateTimeZone('UTC')))->diff(new DateTimeImmutable($to, new DateTimeZone('UTC')))->days;
    if ($span > 366) {
        throw new RuntimeException('بازهٔ گزارش حداکثر یک سال است.');
    }
    if (!$ids || count($ids) > 100) {
        throw new RuntimeException('یک تا ۱۰۰ پرسنل را از فهرست انتخاب کنید.');
    }
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $active = db()->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> '' AND id IN ({$marks})");
    $active->execute($ids);
    if ((int) $active->fetchColumn() !== count($ids)) {
        throw new RuntimeException('یکی از پرسنل انتخاب‌شده غیرفعال یا نامعتبر است؛ فهرست را تازه کنید.');
    }
    $query = db()->prepare("SELECT o.employee_id, u.full_name, u.first_name, u.last_name, u.employee_number,
            o.food_date, o.meal_type, c.food_name, o.created_at, o.status
        FROM food_orders o
        JOIN users u ON u.id = o.employee_id
        JOIN food_calendar_items i ON i.id = o.calendar_item_id
        JOIN food_catalog c ON c.id = i.food_id
        WHERE o.employee_id IN ({$marks}) AND o.food_date BETWEEN ? AND ?
          AND o.meal_type = ? AND o.status = 'active'
        ORDER BY u.last_name, u.first_name, u.full_name, o.food_date, o.created_at, o.id");
    $query->execute(array_merge($ids, [$from, $to, $mealType]));
    $items = [];
    foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $created = (string) ($row['created_at'] ?? '');
        $items[] = [
            'employee_id' => (int) $row['employee_id'],
            'full_name' => food_order_full_name($row),
            'employee_number' => (string) ($row['employee_number'] ?? ''),
            'food_date' => (string) $row['food_date'],
            'meal_type' => (string) $row['meal_type'],
            'food_name' => (string) $row['food_name'],
            'reserve_date' => substr($created, 0, 10),
            'reserve_time' => substr($created, 11, 8),
            'status' => (string) $row['status'],
        ];
    }
    return $items;
}

function food_order_export_selected_orders_range(string $fromDate, string $toDate, array $employeeIds, array $user, string $mealType = 'lunch'): never
{
    food_order_require_permission($user, 'food.order_close');
    $from = food_order_iso_date($fromDate);
    $to = food_order_iso_date($toDate);
    if ($from === null || $to === null || $from > $to) {
        food_order_send_json(['error' => 'بازهٔ تاریخ سفارش معتبر نیست.'], 400);
    }
    $mealType = food_order_meal_type($mealType);
    $items = food_order_selected_orders_range($from, $to, $employeeIds, $mealType);
    $headers = ['ردیف', 'نام و نام خانوادگی', 'شمارهٔ پرسنلی', 'تاریخ غذا', 'وعده', 'نوع غذا', 'تاریخ ثبت', 'ساعت ثبت', 'وضعیت'];
    $mealLabel = $mealType === 'dinner' ? 'شام' : 'ناهار';
    $data = [];
    foreach ($items as $index => $item) {
        $data[] = [
            $index + 1,
            $item['full_name'],
            $item['employee_number'],
            food_order_jalali_label($item['food_date']),
            $mealLabel,
            $item['food_name'],
            food_order_jalali_label($item['reserve_date']),
            substr($item['reserve_time'], 0, 5),
            'فعال',
        ];
    }
    foreach ($data as &$row) {
        foreach ($row as &$value) {
            $value = food_order_export_safe_value($value);
        }
        unset($value);
    }
    unset($row);
    $ids = array_values(array_unique(array_filter(array_map('intval', $employeeIds), static fn (int $id): bool => $id > 0)));
    food_order_audit('food_order_selected_staff_export', $user, 'food_orders', 0, null, [
        'from_date' => $from, 'to_date' => $to, 'meal_type' => $mealType,
        'employee_count' => count($ids), 'row_count' => count($data),
    ]);
    $token = str_replace('-', '', $from) . '-' . str_replace('-', '', $to);
    $rangeLabel = food_order_jalali_label($from) . ' تا ' . food_order_jalali_label($to);
    food_order_send_xlsx('سفارش-پرسنل-انتخابی-' . $token . '.xlsx', 'سفارش پرسنل انتخابی — ' . $mealLabel, $headers, $data, $rangeLabel);
}

function food_order_emergency_window_payload(?array $log): array
{
    $empty = ['active' => false, 'expires_at' => '', 'reason' => ''];
    if (!$log || (string) ($log['action'] ?? '') !== 'emergency_window_grant') {
        return $empty;
    }
    $payload = json_decode((string) ($log['new_value'] ?? ''), true);
    $expiresAt = is_array($payload) ? trim((string) ($payload['expires_at'] ?? '')) : '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $expiresAt)) {
        return $empty;
    }
    $active = $expiresAt > food_order_now()->format('Y-m-d H:i:s');
    return [
        'active' => $active,
        'expires_at' => $expiresAt,
        'reason' => trim((string) ($log['reason'] ?? '')),
    ];
}

/** آخرین ثبت/لغو مهلت اضطراری را برای روز و وعدهٔ مشخص برمی‌گرداند. */
function food_order_emergency_window_state(int $calendarId): array
{
    if ($calendarId < 1) {
        return food_order_emergency_window_payload(null);
    }
    $stmt = db()->prepare("SELECT action, new_value, reason FROM food_order_logs
        WHERE calendar_id = ? AND action IN ('emergency_window_grant', 'emergency_window_revoke')
        ORDER BY id DESC LIMIT 1");
    $stmt->execute([$calendarId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return food_order_emergency_window_payload(is_array($row) ? $row : null);
}

/** بارگذاری یک‌جای وضعیت مهلت‌های اضطراری برای روزهای تقویم. */
function food_order_emergency_window_states(array $calendarIds): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $calendarIds), static fn (int $id): bool => $id > 0)));
    if ($ids === []) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT calendar_id, action, new_value, reason FROM food_order_logs
        WHERE calendar_id IN ({$placeholders}) AND action IN ('emergency_window_grant', 'emergency_window_revoke')
        ORDER BY id ASC");
    $stmt->execute($ids);
    $latest = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $latest[(int) $row['calendar_id']] = $row;
    }
    $states = [];
    foreach ($latest as $calendarId => $row) {
        $states[$calendarId] = food_order_emergency_window_payload($row);
    }
    return $states;
}

function food_order_emergency_window_grant(array $body, array $user): array
{
    food_order_require_primary_admin($user);
    food_order_schema_ensure();
    $date = food_order_iso_date($body['date'] ?? '');
    $mealType = food_order_meal_type($body['meal_type'] ?? 'lunch');
    $hours = (int) ($body['duration_hours'] ?? 0);
    $reason = trim((string) ($body['reason'] ?? ''));
    if ($date === null) {
        throw new RuntimeException('تاریخ روز سفارش معتبر نیست.');
    }
    if (!in_array($hours, [1, 2, 4, 8, 24], true)) {
        throw new RuntimeException('مدت مهلت اضطراری باید یکی از ۱، ۲، ۴، ۸ یا ۲۴ ساعت باشد.');
    }
    if (mb_strlen($reason, 'UTF-8') < 5 || mb_strlen($reason, 'UTF-8') > 500) {
        throw new RuntimeException('برای اعطای دسترسی اضطراری، دلیل ۵ تا ۵۰۰ نویسه‌ای وارد کنید.');
    }
    $today = food_order_today();
    if ($date > $today) {
        throw new RuntimeException('دسترسی اضطراری فقط برای روز جاری یا روزهای گذشته قابل اعطاست.');
    }
    $daysBack = (int) (new DateTimeImmutable($date, new DateTimeZone('UTC')))->diff(new DateTimeImmutable($today, new DateTimeZone('UTC')))->days;
    if ($daysBack > 7) {
        throw new RuntimeException('برای جلوگیری از ثبت دیرهنگام قدیمی، فقط تا ۷ روز گذشته قابل انتخاب است.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $calendarStmt = $pdo->prepare('SELECT id FROM food_calendar WHERE food_date = ? AND meal_type = ? FOR UPDATE');
        $calendarStmt->execute([$date, $mealType]);
        $calendarId = (int) $calendarStmt->fetchColumn();
        if ($calendarId < 1) {
            throw new RuntimeException('برای این روز و وعده برنامهٔ غذایی ثبت نشده است.');
        }
        $activeFoodStmt = $pdo->prepare("SELECT COUNT(*) FROM food_calendar_items i JOIN food_catalog f ON f.id = i.food_id WHERE i.calendar_id = ? AND i.active = 1 AND f.active = 1");
        $activeFoodStmt->execute([$calendarId]);
        if ((int) $activeFoodStmt->fetchColumn() < 1) {
            throw new RuntimeException('برای این روز غذای فعالی تعریف نشده است؛ امکان بازکردن مهلت سفارش وجود ندارد.');
        }
        $old = food_order_emergency_window_state($calendarId);
        $expiresAt = food_order_now()->modify('+' . $hours . ' hours')->format('Y-m-d H:i:s');
        $new = ['food_date' => $date, 'meal_type' => $mealType, 'expires_at' => $expiresAt, 'duration_hours' => $hours];
        food_order_log(null, $calendarId, 'emergency_window_grant', (int) $user['id'], $old, $new, $reason);
        $pdo->commit();
        food_order_audit('food_order_emergency_window_grant', $user, 'food_calendar', $calendarId, $old, $new, $reason);
        return ['ok' => true, 'active' => true, 'expires_at' => $expiresAt, 'message' => 'مهلت اضطراری ثبت سفارش برای همین روز و وعده فعال شد.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function food_order_emergency_window_revoke(array $body, array $user): array
{
    food_order_require_primary_admin($user);
    food_order_schema_ensure();
    $date = food_order_iso_date($body['date'] ?? '');
    $mealType = food_order_meal_type($body['meal_type'] ?? 'lunch');
    $reason = trim((string) ($body['reason'] ?? ''));
    if ($date === null || $date > food_order_today()) {
        throw new RuntimeException('تاریخ روز سفارش اضطراری معتبر نیست.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $calendarStmt = $pdo->prepare('SELECT id FROM food_calendar WHERE food_date = ? AND meal_type = ? FOR UPDATE');
        $calendarStmt->execute([$date, $mealType]);
        $calendarId = (int) $calendarStmt->fetchColumn();
        if ($calendarId < 1) {
            throw new RuntimeException('برای این روز و وعده برنامهٔ غذایی ثبت نشده است.');
        }
        $old = food_order_emergency_window_state($calendarId);
        if (empty($old['active'])) {
            $pdo->commit();
            return ['ok' => true, 'active' => false, 'message' => 'مهلت اضطراری فعالی برای این روز وجود ندارد.'];
        }
        $new = ['revoked_at' => food_order_now()->format('Y-m-d H:i:s')];
        food_order_log(null, $calendarId, 'emergency_window_revoke', (int) $user['id'], $old, $new, $reason !== '' ? $reason : 'لغو توسط مدیر');
        $pdo->commit();
        food_order_audit('food_order_emergency_window_revoke', $user, 'food_calendar', $calendarId, $old, $new, $reason !== '' ? $reason : 'لغو توسط مدیر');
        return ['ok' => true, 'active' => false, 'message' => 'مهلت اضطراری این روز بسته شد.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function food_order_ensure_orderable_day(PDO $pdo, string $isoDate, string $mealType = 'lunch', bool $allowClosed = false): array
{
    $mealType = food_order_meal_type($mealType);
    $stmt = $pdo->prepare('SELECT id, order_status FROM food_calendar WHERE food_date = ? AND meal_type = ? FOR UPDATE');
    $stmt->execute([$isoDate, $mealType]);
    $calendar = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$calendar) {
        throw new RuntimeException('برای این تاریخ هنوز برنامهٔ غذایی ثبت نشده است.');
    }
    $today = food_order_today();
    $dateObj = new DateTimeImmutable($isoDate, new DateTimeZone('UTC'));
    $todayObj = new DateTimeImmutable($today, new DateTimeZone('UTC'));
    $isPast = $dateObj < $todayObj;
    $isClosed = (string) $calendar['order_status'] === 'closed';
    $lateWindow = food_order_emergency_window_state((int) $calendar['id']);
    if (($isPast || ($isClosed && !$allowClosed)) && empty($lateWindow['active'])) {
        throw new RuntimeException($isPast ? 'تاریخ گذشته و فقط‌خواندنی است.' : 'سفارش این روز بسته شده است.');
    }
    return [
        'calendar_id' => (int) $calendar['id'],
        'order_status' => (string) $calendar['order_status'],
        'late_order_window_open' => !empty($lateWindow['active']),
    ];
}

/**
 * بستن سفارش‌گیری جلوی ثبت/تغییر سفارش را می‌گیرد؛ لغو سفارش آینده را مسدود نمی‌کند.
 * همچنان ردیف تقویم را قفل می‌کنیم تا با تغییر هم‌زمان برنامه وضعیت سفارش دقیق بماند.
 */
function food_order_lock_cancellable_day(PDO $pdo, string $isoDate, string $mealType = 'lunch', bool $allowPastEmergency = false): array
{
    if (food_order_iso_date($isoDate) === null) {
        throw new RuntimeException('تاریخ سفارش معتبر نیست.');
    }
    $mealType = food_order_meal_type($mealType);
    $stmt = $pdo->prepare('SELECT id, order_status FROM food_calendar WHERE food_date = ? AND meal_type = ? FOR UPDATE');
    $stmt->execute([$isoDate, $mealType]);
    $calendar = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$calendar) {
        throw new RuntimeException('برای این تاریخ برنامهٔ غذایی پیدا نشد.');
    }
    $dateObj = new DateTimeImmutable($isoDate, new DateTimeZone('UTC'));
    $todayObj = new DateTimeImmutable(food_order_today(), new DateTimeZone('UTC'));
    $lateWindow = food_order_emergency_window_state((int) $calendar['id']);
    if ($dateObj < $todayObj && (!$allowPastEmergency || empty($lateWindow['active']))) {
        throw new RuntimeException('تاریخ گذشته و فقط‌خواندنی است؛ برای عملیات گروهی باید مهلت اضطراری همان روز و وعده فعال باشد.');
    }
    return ['calendar_id' => (int) $calendar['id'], 'order_status' => (string) $calendar['order_status'], 'late_order_window_open' => !empty($lateWindow['active'])];
}

function food_order_verify_employee_national_code(int $employeeId, string $provided, bool $requireEmployeeNumber = true, ?PDO $pdo = null): array
{
    $pdo ??= db();
    $stmt = $pdo->prepare('SELECT id, full_name, first_name, last_name, employee_number, national_code, is_active FROM users WHERE id = ? LIMIT 1' . ($pdo->inTransaction() ? ' FOR UPDATE' : ''));
    $stmt->execute([$employeeId]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target || (int) ($target['is_active'] ?? 0) !== 1 || ($requireEmployeeNumber && trim((string) ($target['employee_number'] ?? '')) === '')) {
        throw new RuntimeException('کد ملی واردشده با اطلاعات فرد انتخاب‌شده مطابقت ندارد.');
    }
    $expected = food_order_digits($target['national_code'] ?? '');
    $actual = food_order_digits($provided);
    if ($expected === '' || strlen($actual) !== 10 || !hash_equals($expected, $actual)) {
        throw new RuntimeException('کد ملی واردشده با اطلاعات فرد انتخاب‌شده مطابقت ندارد.');
    }
    return $target;
}

function food_order_create_or_change(int $employeeId, int $actorId, string $dateInput, int $itemId, array $actor, ?string $proxyNationalCode = null, bool $emergency = false, string $mealType = 'lunch', bool $managerBulk = false): array
{
    food_order_schema_ensure();
    $mealType = food_order_meal_type($mealType);
    $iso = food_order_iso_date($dateInput);
    if ($iso === null || $itemId < 1 || $employeeId < 1 || $actorId < 1) {
        throw new RuntimeException('تاریخ یا انتخاب غذا معتبر نیست.');
    }
    $isProxy = $employeeId !== $actorId;
    if ($managerBulk && !$emergency) {
        throw new RuntimeException('عملیات گروهی فقط از مسیر مدیر مجاز است.');
    }
    if ($emergency) {
        // مسیر مدیران: فقط food.emergency؛ عملیات گروهی از فهرست شناسه‌های معتبر پرسنلی می‌آید.
        if (!food_order_user_can($actor, 'food.emergency')) {
            throw new RuntimeException('دسترسی سفارش اضطراری ندارید.');
        }
        if (!$managerBulk) {
            check_api_rate_limit('food_order_emergency_' . $actorId, 120, 60);
        }
    }
    if (($isProxy || $emergency) && !$managerBulk) {
        if ($proxyNationalCode === null || trim($proxyNationalCode) === '') {
            throw new RuntimeException('کد ملی فرد انتخاب‌شده را وارد کنید.');
        }
        // یک نفر ممکن است برای چند همکار سفارش بدهد؛ سقف در حد چند ده ثبت در دقیقه است نه چند تلاش.
        check_api_rate_limit('food_order_proxy_attempt_' . $actorId, $emergency ? 120 : 80, 60);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $day = food_order_ensure_orderable_day($pdo, $iso, $mealType, $emergency);
        if (($isProxy || $emergency) && $managerBulk) {
            $targetStmt = $pdo->prepare("SELECT id, full_name, first_name, last_name, employee_number, is_active FROM users WHERE id = ? AND is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> '' LIMIT 1 FOR UPDATE");
            $targetStmt->execute([$employeeId]);
            $target = $targetStmt->fetch(PDO::FETCH_ASSOC);
            if (!$target) {
                throw new RuntimeException('پرسنل فعال با شمارهٔ پرسنلی معتبر پیدا نشد.');
            }
        } elseif ($isProxy || $emergency) {
            $target = food_order_verify_employee_national_code($employeeId, (string) $proxyNationalCode, true, $pdo);
        } else {
            $selfStmt = $pdo->prepare('SELECT id, full_name, first_name, last_name, employee_number, national_code, is_active FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
            $selfStmt->execute([$employeeId]);
            $target = $selfStmt->fetch(PDO::FETCH_ASSOC);
            if (!$target || (int) ($target['is_active'] ?? 0) !== 1) {
                throw new RuntimeException('حساب کاربری فعال پیدا نشد.');
            }
            $selfNational = food_order_digits($target['national_code'] ?? '');
            if ($selfNational === '') {
                throw new RuntimeException('کد ملی شما در سامانه ثبت نشده است؛ برای ثبت سفارش با منابع انسانی هماهنگ کنید.');
            }
            if (strlen($selfNational) !== 10) {
                throw new RuntimeException('کد ملی ثبت‌شدهٔ شما معتبر نیست؛ برای اصلاح اطلاعات با منابع انسانی هماهنگ کنید.');
            }
        }

        $item = $pdo->prepare("SELECT i.id, i.food_id, f.food_name FROM food_calendar_items i
            JOIN food_catalog f ON f.id = i.food_id
            WHERE i.id = ? AND i.calendar_id = ? AND i.active = 1 AND f.active = 1 FOR UPDATE");
        $item->execute([$itemId, $day['calendar_id']]);
        $food = $item->fetch(PDO::FETCH_ASSOC);
        if (!$food) {
            throw new RuntimeException('این غذا دیگر برای تاریخ انتخاب‌شده قابل سفارش نیست.');
        }

        $existingStmt = $pdo->prepare('SELECT id, calendar_item_id, status FROM food_orders WHERE employee_id = ? AND food_date = ? AND meal_type = ? FOR UPDATE');
        $existingStmt->execute([$employeeId, $iso, $mealType]);
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);
        $old = $existing ? ['calendar_item_id' => (int) $existing['calendar_item_id'], 'status' => (string) $existing['status']] : null;
        $action = $managerBulk ? 'order_bulk_create' : ($isProxy ? 'order_proxy_create' : 'order_create');
        if ($existing) {
            if ((string) $existing['status'] === 'active' && (int) $existing['calendar_item_id'] === $itemId) {
                $pdo->commit();
                return ['ok' => true, 'order_id' => (int) $existing['id'], 'unchanged' => true, 'message' => 'همین غذا قبلاً برای این روز ثبت شده است.'];
            }
            $action = (string) $existing['status'] === 'cancelled'
                ? ($managerBulk ? 'order_bulk_create' : ($isProxy ? 'order_proxy_create' : 'order_create'))
                : ($managerBulk ? 'order_bulk_change' : 'order_change');
            $pdo->prepare("UPDATE food_orders SET calendar_item_id = ?, created_by = ?, status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$itemId, $actorId, (int) $existing['id']]);
            $orderId = (int) $existing['id'];
        } else {
            $pdo->prepare("INSERT INTO food_orders (employee_id, calendar_item_id, food_date, meal_type, created_by, status) VALUES (?, ?, ?, ?, ?, 'active')")
                ->execute([$employeeId, $itemId, $iso, $mealType, $actorId]);
            $orderId = (int) $pdo->lastInsertId();
        }
        $lateReason = !empty($day['late_order_window_open']) ? 'ثبت در مهلت اضطراری زمان‌دارِ تأییدشده توسط مدیر' : null;
        food_order_log($orderId, $day['calendar_id'], $action, $actorId, $old, ['calendar_item_id' => $itemId, 'food_name' => (string) $food['food_name'], 'employee_id' => $employeeId], $lateReason);
        $pdo->commit();
        food_order_audit($action, $actor, 'food_order', $orderId, $old, ['calendar_item_id' => $itemId, 'food_name' => (string) $food['food_name'], 'employee_id' => $employeeId], $lateReason);
        return ['ok' => true, 'order_id' => $orderId, 'food_name' => (string) $food['food_name'], 'message' => $isProxy ? 'سفارش برای فرد انتخاب‌شده ثبت شد.' : 'سفارش شما ثبت شد.'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ((string) $e->getCode() === '23000') {
            throw new RuntimeException('برای این نفر و این روز قبلاً سفارشی ثبت شده است؛ صفحه را تازه‌سازی کنید.');
        }
        throw $e;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function food_order_cancel(int $orderId, array $user): array
{
    food_order_schema_ensure();
    if ($orderId < 1) {
        throw new RuntimeException('سفارش پیدا نشد.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Read date first without locking; then lock day → order, matching the
        // day-save/create lock order to reduce deadlocks under concurrent use.
        $lookup = $pdo->prepare('SELECT id, employee_id, calendar_item_id, food_date, meal_type, status FROM food_orders WHERE id = ? LIMIT 1');
        $lookup->execute([$orderId]);
        $initial = $lookup->fetch(PDO::FETCH_ASSOC);
        if (!$initial || (int) $initial['employee_id'] !== (int) $user['id']) {
            throw new RuntimeException('سفارش قابل لغو نیست.');
        }
        $day = food_order_lock_cancellable_day($pdo, (string) $initial['food_date'], (string) $initial['meal_type']);
        $stmt = $pdo->prepare('SELECT id, employee_id, calendar_item_id, food_date, status FROM food_orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || (int) $order['employee_id'] !== (int) $user['id'] || (string) $order['food_date'] !== (string) $initial['food_date']) {
            throw new RuntimeException('سفارش قابل لغو نیست.');
        }
        if ((string) $order['status'] !== 'active') {
            $pdo->commit();
            return ['ok' => true, 'message' => 'این سفارش قبلاً لغو شده است.'];
        }
        if ((string) ($day['order_status'] ?? '') === 'closed') {
            throw new RuntimeException('سفارش این روز بسته شده و لغو آن ممکن نیست.');
        }
        $pdo->prepare("UPDATE food_orders SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$orderId]);
        $old = ['calendar_item_id' => (int) $order['calendar_item_id'], 'status' => 'active'];
        $new = ['status' => 'cancelled'];
        food_order_log($orderId, $day['calendar_id'], 'order_cancel', (int) $user['id'], $old, $new);
        $pdo->commit();
        food_order_audit('order_cancel', $user, 'food_order', $orderId, $old, $new);
        return ['ok' => true, 'message' => 'سفارش شما لغو شد.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function food_order_cancel_proxy(int $orderId, int $employeeId, string $nationalCode, array $actor, bool $emergency = false, bool $managerBulk = false, string $reason = ''): array
{
    food_order_schema_ensure();
    if ($orderId < 1 || $employeeId < 1) {
        throw new RuntimeException('سفارش قابل لغو نیست.');
    }
    if ($managerBulk && !$emergency) {
        throw new RuntimeException('عملیات گروهی فقط از مسیر مدیر مجاز است.');
    }
    if ($emergency && !food_order_user_can($actor, 'food.emergency')) {
        throw new RuntimeException('دسترسی سفارش اضطراری ندارید.');
    }
    if (!$managerBulk) {
        check_api_rate_limit('food_order_proxy_cancel_' . (int) ($actor['id'] ?? 0), food_order_user_can($actor, 'food.emergency') ? 120 : 80, 60);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Match the create lock order: order date → verified recipient → order row.
        $lookup = $pdo->prepare('SELECT id, employee_id, calendar_item_id, food_date, meal_type, status FROM food_orders WHERE id = ? LIMIT 1');
        $lookup->execute([$orderId]);
        $initial = $lookup->fetch(PDO::FETCH_ASSOC);
        if (!$initial || (int) $initial['employee_id'] !== $employeeId) {
            throw new RuntimeException('سفارش برای فرد تأییدشده پیدا نشد یا قابل لغو نیست.');
        }
        $day = food_order_lock_cancellable_day($pdo, (string) $initial['food_date'], (string) $initial['meal_type'], $managerBulk && $emergency);
        $statusStmt = $pdo->prepare('SELECT order_status FROM food_calendar WHERE food_date = ? AND meal_type = ? LIMIT 1');
        $statusStmt->execute([(string) $initial['food_date'], (string) $initial['meal_type']]);
        if ((string) $statusStmt->fetchColumn() === 'closed' && !$emergency) {
            throw new RuntimeException('سفارش این روز بسته شده و لغو آن فقط از برنامهٔ غذایی توسط مدیر پشتیبانی ممکن است.');
        }
        if ($managerBulk) {
            $targetStmt = $pdo->prepare("SELECT id, full_name, first_name, last_name, employee_number, is_active FROM users WHERE id = ? AND is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> '' LIMIT 1 FOR UPDATE");
            $targetStmt->execute([$employeeId]);
            $target = $targetStmt->fetch(PDO::FETCH_ASSOC);
            if (!$target) {
                throw new RuntimeException('پرسنل فعال با شمارهٔ پرسنلی معتبر پیدا نشد.');
            }
        } else {
            $target = food_order_verify_employee_national_code($employeeId, $nationalCode, true, $pdo);
        }
        $stmt = $pdo->prepare('SELECT id, employee_id, calendar_item_id, food_date, status FROM food_orders WHERE id = ? AND employee_id = ? FOR UPDATE');
        $stmt->execute([$orderId, $employeeId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || (int) $order['employee_id'] !== (int) $target['id'] || (string) $order['food_date'] !== (string) $initial['food_date']) {
            throw new RuntimeException('سفارش برای فرد تأییدشده پیدا نشد یا قابل لغو نیست.');
        }
        if ((string) $order['status'] !== 'active') {
            $pdo->commit();
            return ['ok' => true, 'message' => 'این سفارش قبلاً لغو شده است.'];
        }
        $update = $pdo->prepare("UPDATE food_orders SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND employee_id = ? AND status = 'active'");
        $update->execute([$orderId, $employeeId]);
        if ($update->rowCount() !== 1) {
            throw new RuntimeException('وضعیت سفارش هم‌زمان تغییر کرد؛ فهرست را تازه کنید و دوباره تلاش کنید.');
        }
        $old = ['calendar_item_id' => (int) $order['calendar_item_id'], 'status' => 'active', 'employee_id' => $employeeId];
        $new = ['status' => 'cancelled', 'employee_id' => $employeeId];
        food_order_log($orderId, $day['calendar_id'], $managerBulk ? 'order_bulk_cancel' : 'order_proxy_cancel', (int) $actor['id'], $old, $new, $reason !== '' ? $reason : null);
        $pdo->commit();
        food_order_audit($managerBulk ? 'order_bulk_cancel' : 'order_proxy_cancel', $actor, 'food_order', $orderId, $old, $new, $reason !== '' ? $reason : null);
        return ['ok' => true, 'message' => 'سفارش فرد تأییدشده لغو شد.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function food_order_cancel_request_ensure(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    food_order_schema_ensure();
    try {
        db()->exec('CREATE TABLE IF NOT EXISTS food_order_cancel_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id BIGINT UNSIGNED NOT NULL,
            employee_id INT UNSIGNED NOT NULL,
            requested_by INT UNSIGNED NOT NULL,
            food_date DATE NOT NULL,
            note VARCHAR(300) NULL,
            status ENUM(\'pending\',\'approved\',\'rejected\') NOT NULL DEFAULT \'pending\',
            decided_by INT UNSIGNED NULL,
            decided_at DATETIME NULL,
            decision_note VARCHAR(300) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_food_cancel_req_status (status, created_at),
            INDEX idx_food_cancel_req_order (order_id),
            INDEX idx_food_cancel_req_employee (employee_id, food_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $ready = true;
    } catch (Throwable $e) {
        app_error_log('[food-order] cancel-request table: ' . $e->getMessage());
        throw new RuntimeException('جدول درخواست لغو سفارش در دسترس نیست.');
    }
}

function food_order_require_cancel_reviewer(array $user): void
{
    if (!$user || !food_order_user_is_cancel_reviewer($user)) {
        throw new RuntimeException('دسترسی شما به رسیدگی درخواست لغو سفارش مجاز نیست.');
    }
}

function food_order_cancel_inbox_url(): string
{
    return 'index.php?page=dashboard#food-cancel-requests';
}

/** مدیر اصلی یا مدیر پشتیبانی — تنها مخاطبان اعلان زنگوله و کارت داشبورد. food.order_close به‌تنهایی کافی نیست. */
function food_order_user_is_cancel_reviewer(array $user): bool
{
    if (!$user) {
        return false;
    }
    if (function_exists('food_order_is_primary_admin') && food_order_is_primary_admin($user)) {
        return true;
    }
    if (function_exists('user_is_primary_admin') && user_is_primary_admin($user)) {
        return true;
    }
    if ((int) ($user['is_primary_admin'] ?? 0) === 1) {
        return true;
    }
    $role = (string) ($user['role'] ?? '');
    return $role === 'primary_admin' || $role === 'support_manager';
}

/** @return list<int> */
function food_order_cancel_reviewer_ids(): array
{
    $ids = [];
    try {
        $stmt = db()->query("SELECT id FROM users WHERE is_active = 1 AND (is_primary_admin = 1 OR role IN ('primary_admin','support_manager'))");
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $ids[(int) $id] = true;
        }
    } catch (Throwable $e) {
        if (function_exists('app_error_log')) {
            app_error_log('[food-order] cancel reviewers: ' . $e->getMessage());
        }
    }
    return array_keys($ids);
}

function food_order_notify_cancel_reviewers(string $type, string $title, string $body, int $excludeUserId = 0): void
{
    $ids = food_order_cancel_reviewer_ids();
    if ($ids === []) {
        if (function_exists('app_error_log')) {
            app_error_log('[food-order] no cancel reviewers to notify');
        }
        return;
    }
    if (!function_exists('notify_user')) {
        if (function_exists('app_error_log')) {
            app_error_log('[food-order] notify_user missing');
        }
        return;
    }
    $sent = 0;
    foreach ($ids as $id) {
        if ($excludeUserId > 0 && (int) $id === $excludeUserId) {
            continue;
        }
        try {
            notify_user((int) $id, $type, $title, $body, null);
            $sent++;
        } catch (Throwable $e) {
            if (function_exists('app_error_log')) {
                app_error_log('[food-order] notify ' . $id . ': ' . $e->getMessage());
            }
        }
    }
    if ($sent === 0 && function_exists('app_error_log')) {
        app_error_log('[food-order] cancel notification sent to 0 reviewers');
    }
}

function food_order_notify_support_managers(string $type, string $title, string $body): void
{
    food_order_notify_cancel_reviewers($type, $title, $body);
}

function food_order_render_pending_cancels_card(array $user): string
{
    if (!food_order_user_is_cancel_reviewer($user)) {
        return '';
    }
    try {
        $items = food_order_cancel_request_list();
    } catch (Throwable) {
        return '';
    }
    $html = '<section class="list-section" id="food-cancel-requests"><div class="section-title"><div><h2>درخواست‌های لغو غذا</h2><p>سفارش روز بسته‌شده که منتظر تأیید مدیر پشتیبانی یا مدیر اصلی است. اعلان هم در «اعلان‌ها» ثبت می‌شود.</p></div>';
    if (food_order_user_can($user, 'food.order_close')) {
        $html .= '<a href="index.php?page=food-ticket">برنامهٔ غذایی ←</a>';
    }
    $html .= '</div>';
    if ($items === []) {
        $html .= '<div class="card empty-state"><p>درخواست لغو بازی نیست.</p></div></section>';
        return $html;
    }
    $html .= '<div class="card ticket-table"><div class="table-head"><span>تاریخ غذا</span><span>نام پرسنل</span><span>غذا</span><span>درخواست‌دهنده</span><span>عملیات</span></div>';
    foreach ($items as $row) {
        $id = (int) ($row['id'] ?? 0);
        $html .= '<div class="table-row"><span>' . e((string) ($row['food_date_jalali'] ?: $row['food_date'] ?? '')) . '</span>'
            . '<span>' . e((string) ($row['employee_name'] ?? '')) . '</span>'
            . '<span>' . e((string) ($row['food_name'] ?? '')) . '</span>'
            . '<span>' . e((string) ($row['requester_name'] ?? '')) . '<small class="food-cancel-request-time">زمان ثبت درخواست: ' . e((string) ($row['requested_at_label'] ?? $row['created_at'] ?? '')) . '</small></span>'
            . '<span class="supervisor-actions">'
            . '<form method="post" class="inline-action">' . csrf_field() . '<input type="hidden" name="action" value="food_order_cancel_decide"><input type="hidden" name="request_id" value="' . $id . '"><input type="hidden" name="decision" value="approve"><button class="mini-button" type="submit">لغو سفارش</button></form> '
            . '<form method="post" class="inline-action">' . csrf_field() . '<input type="hidden" name="action" value="food_order_cancel_decide"><input type="hidden" name="request_id" value="' . $id . '"><input type="hidden" name="decision" value="reject"><button class="mini-button" type="submit">رد درخواست</button></form>'
            . '</span></div>';
    }
    $html .= '</div></section>';
    return $html;
}

/** @param list<int> $orderIds @return array<int,array{id:int,status:string}> */
function food_order_pending_cancel_map(array $orderIds): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $orderIds), static fn (int $id): bool => $id > 0)));
    if ($ids === []) {
        return [];
    }
    try {
        food_order_cancel_request_ensure();
    } catch (Throwable) {
        return [];
    }
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT id, order_id, status, created_at FROM food_order_cancel_requests WHERE order_id IN ($in) AND status = 'pending'");
    $stmt->execute($ids);
    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map[(int) $row['order_id']] = [
            'id' => (int) $row['id'],
            'status' => (string) $row['status'],
            'created_at' => (string) $row['created_at'],
            'requested_at_label' => function_exists('jalali_date') ? jalali_date((string) $row['created_at']) : (string) $row['created_at'],
        ];
    }
    return $map;
}

function food_order_cancel_request_create(int $orderId, array $actor, ?array $subject = null): array
{
    food_order_cancel_request_ensure();
    if ($orderId < 1) {
        throw new RuntimeException('سفارش پیدا نشد.');
    }
    $subject ??= $actor;
    $employeeId = (int) ($subject['id'] ?? 0);
    $actorId = (int) ($actor['id'] ?? 0);
    if ($employeeId < 1 || $actorId < 1) {
        throw new RuntimeException('سفارش پیدا نشد.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $lookup = $pdo->prepare('SELECT id, employee_id, calendar_item_id, food_date, meal_type, status FROM food_orders WHERE id = ? LIMIT 1');
        $lookup->execute([$orderId]);
        $order = $lookup->fetch(PDO::FETCH_ASSOC);
        if (!$order || (int) $order['employee_id'] !== $employeeId) {
            throw new RuntimeException('سفارش برای این فرد پیدا نشد.');
        }
        $day = food_order_lock_cancellable_day($pdo, (string) $order['food_date'], (string) $order['meal_type']);
        if ((string) ($day['order_status'] ?? '') !== 'closed') {
            throw new RuntimeException('برای روز باز، سفارش را مستقیم لغو کنید؛ درخواست لغو فقط برای روز بسته‌شده است.');
        }
        $locked = $pdo->prepare('SELECT id, employee_id, food_date, status FROM food_orders WHERE id = ? FOR UPDATE');
        $locked->execute([$orderId]);
        $order = $locked->fetch(PDO::FETCH_ASSOC);
        if (!$order || (int) $order['employee_id'] !== $employeeId) {
            throw new RuntimeException('سفارش برای این فرد پیدا نشد.');
        }
        if ((string) $order['status'] !== 'active') {
            throw new RuntimeException('این سفارش فعال نیست.');
        }
        $dup = $pdo->prepare("SELECT id FROM food_order_cancel_requests WHERE order_id = ? AND status = 'pending' LIMIT 1");
        $dup->execute([$orderId]);
        if ($dup->fetchColumn()) {
            $pdo->commit();
            return ['ok' => true, 'message' => 'درخواست لغو این سفارش قبلاً برای مدیر پشتیبانی و مدیر اصلی ثبت شده است.', 'pending' => true];
        }
        $pdo->prepare('INSERT INTO food_order_cancel_requests (order_id, employee_id, requested_by, food_date, status) VALUES (?, ?, ?, ?, \'pending\')')
            ->execute([$orderId, $employeeId, $actorId, (string) $order['food_date']]);
        $requestId = (int) $pdo->lastInsertId();
        $createdAtQuery = $pdo->prepare('SELECT created_at FROM food_order_cancel_requests WHERE id = ? LIMIT 1');
        $createdAtQuery->execute([$requestId]);
        $requestCreatedAt = (string) ($createdAtQuery->fetchColumn() ?: date('Y-m-d H:i:s'));
        $pdo->commit();
        try {
            food_order_log($orderId, $day['calendar_id'], 'order_cancel_request', $actorId, ['status' => 'active'], ['request_id' => $requestId, 'status' => 'pending', 'requested_at' => $requestCreatedAt]);
        } catch (Throwable $logError) {
            if (function_exists('app_error_log')) {
                app_error_log('[food-order] cancel-request log: ' . $logError->getMessage());
            }
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    $who = food_order_full_name($subject);
    $dateLabel = food_order_jalali_label((string) $order['food_date']);
    $requestCreatedAtLabel = function_exists('jalali_date') ? jalali_date($requestCreatedAt) : $requestCreatedAt;
    food_order_audit('order_cancel_request', $actor, 'food_order', $orderId, null, ['request_id' => $requestId, 'food_date' => (string) $order['food_date'], 'requested_at' => $requestCreatedAt]);
    food_order_notify_cancel_reviewers(
        'food_cancel_request',
        'درخواست لغو سفارش غذا',
        'برای سفارش ' . $who . ' در تاریخ ' . $dateLabel . ' درخواست لغو ثبت شد. زمان ثبت درخواست: ' . $requestCreatedAtLabel . '. از داشبورد سامانه (درخواست‌های لغو غذا) یا برنامهٔ غذایی ← درخواست‌های لغو می‌توانید آن را لغو یا رد کنید.',
        $actorId
    );
    if (function_exists('system_log')) {
        try {
            system_log('info', 'food_order', 'درخواست لغو سفارش روز بسته', [
                'order_id' => $orderId,
                'request_id' => $requestId,
                'employee_id' => $employeeId,
                'requested_by' => $actorId,
                'food_date' => (string) $order['food_date'],
                'requested_at' => $requestCreatedAt,
            ]);
        } catch (Throwable) {
        }
    }
    return ['ok' => true, 'message' => 'درخواست لغو برای مدیر پشتیبانی و مدیر اصلی ثبت شد.', 'request_id' => $requestId, 'pending' => true];
}

function food_order_cancel_request_list(): array
{
    food_order_cancel_request_ensure();
    $stmt = db()->query("SELECT r.id, r.order_id, r.employee_id, r.requested_by, r.food_date, r.status, r.created_at,
            emp.full_name AS employee_name, req.full_name AS requester_name, c.food_name, o.meal_type, o.status AS order_status
        FROM food_order_cancel_requests r
        JOIN food_orders o ON o.id = r.order_id
        JOIN users emp ON emp.id = r.employee_id
        JOIN users req ON req.id = r.requested_by
        JOIN food_calendar_items i ON i.id = o.calendar_item_id
        JOIN food_catalog c ON c.id = i.food_id
        WHERE r.status = 'pending'
        ORDER BY r.created_at ASC, r.id ASC
        LIMIT 200");
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $items[] = [
            'id' => (int) $row['id'],
            'order_id' => (int) $row['order_id'],
            'employee_id' => (int) $row['employee_id'],
            'employee_name' => (string) ($row['employee_name'] ?? ''),
            'requester_name' => (string) ($row['requester_name'] ?? ''),
            'food_name' => (string) ($row['food_name'] ?? ''),
            'meal_type' => (string) ($row['meal_type'] ?? 'lunch'),
            'food_date' => (string) $row['food_date'],
            'food_date_jalali' => food_order_jalali_label((string) $row['food_date']),
            'order_status' => (string) ($row['order_status'] ?? ''),
            'created_at' => (string) $row['created_at'],
            'requested_at' => (string) $row['created_at'],
            'requested_at_label' => function_exists('jalali_date') ? jalali_date((string) $row['created_at']) : (string) $row['created_at'],
        ];
    }
    return $items;
}

function food_order_cancel_request_decide(int $requestId, string $decision, array $actor, string $note = ''): array
{
    food_order_cancel_request_ensure();
    food_order_require_cancel_reviewer($actor);
    $decision = $decision === 'reject' ? 'reject' : 'approve';
    $note = trim($note);
    if (function_exists('mb_substr')) {
        $note = mb_substr($note, 0, 300, 'UTF-8');
    } else {
        $note = substr($note, 0, 300);
    }
    $actorId = (int) ($actor['id'] ?? 0);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, order_id, employee_id, requested_by, food_date, status FROM food_order_cancel_requests WHERE id = ? FOR UPDATE');
        $stmt->execute([$requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$request) {
            throw new RuntimeException('درخواست لغو پیدا نشد.');
        }
        if ((string) $request['status'] !== 'pending') {
            throw new RuntimeException('این درخواست قبلاً رسیدگی شده است.');
        }
        $orderId = (int) $request['order_id'];
        $newStatus = $decision === 'approve' ? 'approved' : 'rejected';
        $day = null;
        if ($decision === 'approve') {
            $mealLookup = $pdo->prepare('SELECT meal_type FROM food_orders WHERE id = ? LIMIT 1');
            $mealLookup->execute([(int) $request['order_id']]);
            $requestMeal = food_order_meal_type($mealLookup->fetchColumn() ?: 'lunch');
            $day = food_order_lock_cancellable_day($pdo, (string) $request['food_date'], $requestMeal);
        }
        $orderStmt = $pdo->prepare('SELECT id, employee_id, calendar_item_id, food_date, status FROM food_orders WHERE id = ? FOR UPDATE');
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
        if ($decision === 'approve') {
            if ($order && (string) $order['status'] === 'active') {
                $pdo->prepare("UPDATE food_orders SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'active'")
                    ->execute([$orderId]);
                $old = ['calendar_item_id' => (int) $order['calendar_item_id'], 'status' => 'active'];
                $new = ['status' => 'cancelled', 'via' => 'cancel_request'];
                food_order_log($orderId, $day['calendar_id'], 'order_cancel_request_approved', $actorId, $old, $new, $note !== '' ? $note : null);
            }
        }
        $pdo->prepare('UPDATE food_order_cancel_requests SET status = ?, decided_by = ?, decided_at = NOW(), decision_note = ? WHERE id = ?')
            ->execute([$newStatus, $actorId, $note !== '' ? $note : null, $requestId]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    food_order_audit(
        $decision === 'approve' ? 'order_cancel_request_approved' : 'order_cancel_request_rejected',
        $actor,
        'food_order',
        $orderId,
        ['request_id' => $requestId, 'status' => 'pending'],
        ['status' => $newStatus, 'note' => $note]
    );
    $dateLabel = food_order_jalali_label((string) $request['food_date']);
    $message = $decision === 'approve'
        ? 'سفارش تاریخ ' . $dateLabel . ' با تأیید مسئول لغو شد.'
        : 'درخواست لغو سفارش تاریخ ' . $dateLabel . ' رد شد.';
    if (function_exists('notify_user')) {
        try {
            notify_user((int) $request['requested_by'], 'food_cancel_decision', $decision === 'approve' ? 'سفارش غذا لغو شد' : 'درخواست لغو سفارش رد شد', $message, null);
        } catch (Throwable) {
        }
    }
    return ['ok' => true, 'message' => $decision === 'approve' ? 'سفارش لغو شد.' : 'درخواست لغو رد شد.'];
}

function food_order_catalog_save(array $body, array $user): array
{
    food_order_schema_ensure();
    food_order_require_permission($user, 'food.menu_edit');
    $id = max(0, (int) ($body['id'] ?? 0));
    $name = trim((string) ($body['food_name'] ?? ''));
    $active = !empty($body['active']) ? 1 : 0;
    $name = (string) preg_replace('/\s+/u', ' ', strtr($name, ['ي' => 'ی', 'ك' => 'ک']));
    if ($name === '' || mb_strlen($name, 'UTF-8') > 190) {
        throw new RuntimeException('نام غذا الزامی و حداکثر ۱۹۰ نویسه است.');
    }
    if (preg_match('/[<>\x00-\x1F]/u', $name)) {
        throw new RuntimeException('نام غذا نویسهٔ نامعتبر دارد.');
    }
    $categoryRaw = trim((string) ($body['category'] ?? ''));
    if ($categoryRaw !== '' && $categoryRaw !== 'سایر' && !in_array($categoryRaw, FOOD_CATEGORIES, true)) {
        throw new RuntimeException('دسته‌بندی انتخاب‌شده معتبر نیست.');
    }
    $category = ($categoryRaw === '' || $categoryRaw === 'سایر') ? null : $categoryRaw;
    $hasCategory = food_order_catalog_has_category();
    $pdo = db();
    $pdo->beginTransaction();
    try {
        // نام تکراری (حتی با فرق فاصله یا حروف عربی) قبول نمی‌شود.
        $dupStmt = $pdo->prepare('SELECT id, food_name FROM food_catalog WHERE id <> ? FOR UPDATE');
        $dupStmt->execute([$id]);
        $key = food_order_name_key($name);
        foreach ($dupStmt->fetchAll(PDO::FETCH_ASSOC) as $other) {
            if (food_order_name_key((string) $other['food_name']) === $key) {
                throw new RuntimeException('این غذا قبلاً در بانک غذا ثبت شده است: «' . $other['food_name'] . '». همان را انتخاب یا ویرایش کنید.');
            }
        }
        if ($id > 0) {
            $find = $pdo->prepare('SELECT id, food_name, active FROM food_catalog WHERE id = ? LIMIT 1 FOR UPDATE');
            $find->execute([$id]);
            $old = $find->fetch(PDO::FETCH_ASSOC);
            if (!$old) {
                throw new RuntimeException('غذا پیدا نشد.');
            }
            if ((string) $old['food_name'] !== $name) {
                $usage = $pdo->prepare('SELECT COUNT(*) FROM food_calendar_items WHERE food_id = ?');
                $usage->execute([$id]);
                if ((int) $usage->fetchColumn() > 0) {
                    throw new RuntimeException('نام غذایی که در برنامهٔ روزانه استفاده شده تغییر نمی‌کند؛ غذای تازه بسازید و مورد قبلی را غیرفعال کنید تا سوابق تاریخی حفظ شوند.');
                }
            }
            if ($hasCategory) {
                $pdo->prepare('UPDATE food_catalog SET food_name = ?, category = ?, active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$name, $category, $active, $id]);
            } else {
                $pdo->prepare('UPDATE food_catalog SET food_name = ?, active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$name, $active, $id]);
            }
            $action = $active ? 'food_catalog_update' : 'food_catalog_deactivate';
            food_order_log(null, null, $action, (int) $user['id'], $old, ['id' => $id, 'food_name' => $name, 'active' => (bool) $active]);
            $oldForAudit = $old;
        } else {
            if ($hasCategory) {
                $pdo->prepare('INSERT INTO food_catalog (food_name, category, active, created_by) VALUES (?, ?, ?, ?)')->execute([$name, $category, $active, (int) $user['id']]);
            } else {
                $pdo->prepare('INSERT INTO food_catalog (food_name, active, created_by) VALUES (?, ?, ?)')->execute([$name, $active, (int) $user['id']]);
            }
            $id = (int) $pdo->lastInsertId();
            $action = $active ? 'food_catalog_create' : 'food_catalog_deactivate';
            food_order_log(null, null, $action, (int) $user['id'], null, ['id' => $id, 'food_name' => $name, 'active' => (bool) $active]);
            $oldForAudit = null;
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            throw new RuntimeException('این نام غذا قبلاً در بانک غذا ثبت شده است.');
        }
        throw $e;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    food_order_audit($action, $user, 'food_catalog', $id, $oldForAudit, ['food_name' => $name, 'category' => $category, 'active' => (bool) $active]);
    return ['ok' => true, 'items' => food_order_catalog(true), 'message' => 'بانک غذا ذخیره شد.'];
}

function food_order_save_day(array $body, array $user): array
{
    food_order_schema_ensure();
    food_order_require_permission($user, 'food.menu_edit');
    $iso = food_order_iso_date($body['date'] ?? '');
    $mealType = food_order_meal_type($body['meal_type'] ?? 'lunch');
    if ($iso === null) {
        throw new RuntimeException('تاریخ برنامه معتبر نیست.');
    }
    $foodIds = array_values(array_unique(array_filter(array_map(static fn ($x): int => max(0, (int) $x), (array) ($body['food_ids'] ?? []))))) ;
    if (count($foodIds) > 3) {
        throw new RuntimeException('برای هر روز حداکثر سه گزینهٔ غذا می‌توان ثبت کرد.');
    }
    $foodIds = array_values(array_filter($foodIds, static fn (int $id): bool => $id > 0));
    $note = trim((string) ($body['note'] ?? ''));
    if (mb_strlen($note, 'UTF-8') > 255) {
        throw new RuntimeException('یادداشت حداکثر ۲۵۵ نویسه باشد.');
    }
    $rawStatus = trim((string) ($body['order_status'] ?? ''));
    if ($rawStatus !== '' && !in_array($rawStatus, ['open', 'closed'], true)) {
        throw new RuntimeException('وضعیت سفارش روز معتبر نیست.');
    }
    $status = $rawStatus;
    if ($status === 'closed' || $status === 'open') {
        if (!food_order_user_can($user, 'food.order_close')) {
            throw new RuntimeException('بستن یا بازکردن روز به دسترسی food.order_close نیاز دارد.');
        }
    }
    $today = new DateTimeImmutable(food_order_today(), new DateTimeZone('UTC'));
    $dateObj = new DateTimeImmutable($iso, new DateTimeZone('UTC'));
    if ($dateObj < $today && !food_order_is_primary_admin($user)) {
        throw new RuntimeException('ویرایش برنامهٔ روز گذشته فقط برای ادمین اصلی مجاز است.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $find = $pdo->prepare('SELECT id, order_status, note, closed_by, closed_at FROM food_calendar WHERE food_date = ? AND meal_type = ? FOR UPDATE');
        $find->execute([$iso, $mealType]);
        $oldDay = $find->fetch(PDO::FETCH_ASSOC);
        if (!$oldDay) {
            try {
                $pdo->prepare('INSERT INTO food_calendar (food_date, meal_type, order_status, note, created_by) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$iso, $mealType, $status === 'closed' ? 'closed' : 'open', $note !== '' ? $note : null, (int) $user['id']]);
            } catch (PDOException $e) {
                if ((string) $e->getCode() !== '23000') {
                    throw $e;
                }
            }
            $find->execute([$iso, $mealType]);
            $oldDay = $find->fetch(PDO::FETCH_ASSOC);
        }
        if (!$oldDay) {
            throw new RuntimeException('ساخت ردیف تقویم انجام نشد.');
        }
        $calendarId = (int) $oldDay['id'];
        $wasClosed = (string) $oldDay['order_status'] === 'closed';
        if ($wasClosed && !food_order_user_can($user, 'food.order_close')) {
            throw new RuntimeException('ویرایش روز بسته به دسترسی food.order_close نیاز دارد.');
        }

        if ($foodIds !== []) {
            $placeholders = implode(',', array_fill(0, count($foodIds), '?'));
            $activeFoods = $pdo->prepare("SELECT id FROM food_catalog
                WHERE id IN ({$placeholders}) AND (active = 1 OR id IN (SELECT food_id FROM food_calendar_items WHERE calendar_id = ?)) FOR UPDATE");
            $activeFoods->execute(array_merge($foodIds, [$calendarId]));
            $valid = array_map('intval', $activeFoods->fetchAll(PDO::FETCH_COLUMN));
            sort($valid);
            $expected = $foodIds;
            sort($expected);
            if ($valid !== $expected) {
                throw new RuntimeException('یکی از غذاهای انتخاب‌شده غیرفعال یا نامعتبر است.');
            }
        }

        $existingStmt = $pdo->prepare('SELECT id, food_id, active FROM food_calendar_items WHERE calendar_id = ? FOR UPDATE');
        $existingStmt->execute([$calendarId]);
        $existingItems = $existingStmt->fetchAll(PDO::FETCH_ASSOC);
        $removed = [];
        foreach ($existingItems as $item) {
            if ((int) $item['active'] === 1 && !in_array((int) $item['food_id'], $foodIds, true)) {
                $removed[] = ['item_id' => (int) $item['id'], 'food_id' => (int) $item['food_id']];
            }
        }
        $affected = 0;
        foreach ($removed as $item) {
            $count = $pdo->prepare("SELECT COUNT(*) FROM food_orders WHERE calendar_item_id = ? AND food_date = ? AND status = 'active'");
            $count->execute([$item['item_id'], $iso]);
            $affected += (int) $count->fetchColumn();
        }
        $confirmedImpact = !empty($body['confirm_impact']);
        $expectedImpact = isset($body['expected_impact']) ? max(0, (int) $body['expected_impact']) : -1;
        if ($affected > 0 && (!$confirmedImpact || $expectedImpact !== $affected)) {
            $pdo->rollBack();
            return [
                'ok' => false,
                'confirmation_required' => true,
                'affected_orders' => $affected,
                'message' => "با حذف یا جایگزینی این غذا، {$affected} سفارش فعال لغو می‌شود. تعداد سفارش‌ها از زمان بررسی تغییر کرده یا باید دوباره تأیید شود.",
            ];
        }

        foreach ($removed as $item) {
            $ordersStmt = $pdo->prepare("SELECT id FROM food_orders WHERE calendar_item_id = ? AND food_date = ? AND status = 'active' FOR UPDATE");
            $ordersStmt->execute([$item['item_id'], $iso]);
            $orderIds = array_map('intval', $ordersStmt->fetchAll(PDO::FETCH_COLUMN));
            if ($orderIds !== []) {
                $pdo->prepare("UPDATE food_orders SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE calendar_item_id = ? AND food_date = ? AND status = 'active'")
                    ->execute([$item['item_id'], $iso]);
                foreach ($orderIds as $orderId) {
                    food_order_log($orderId, $calendarId, 'order_cancel', (int) $user['id'], ['status' => 'active', 'calendar_item_id' => $item['item_id']], ['status' => 'cancelled'], 'غذا از برنامهٔ روز حذف شد');
                }
            }
            $pdo->prepare('UPDATE food_calendar_items SET active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$item['item_id']]);
        }
        foreach ($foodIds as $index => $foodId) {
            $pdo->prepare('INSERT INTO food_calendar_items (calendar_id, food_id, sort_order, active) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order), active = 1, updated_at = CURRENT_TIMESTAMP')
                ->execute([$calendarId, $foodId, $index + 1]);
        }

        $newStatus = $status !== '' ? $status : (string) $oldDay['order_status'];
        $closedBy = $newStatus === 'closed' ? (int) $user['id'] : null;
        $closedAt = $newStatus === 'closed' ? date('Y-m-d H:i:s') : null;
        $pdo->prepare('UPDATE food_calendar SET order_status = ?, note = ?, closed_by = ?, closed_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
            ->execute([$newStatus, $note !== '' ? $note : null, $closedBy, $closedAt, $calendarId]);

        $newDay = ['order_status' => $newStatus, 'note' => $note, 'food_ids' => $foodIds];
        food_order_log(null, $calendarId, 'day_save', (int) $user['id'], ['order_status' => (string) $oldDay['order_status'], 'note' => (string) ($oldDay['note'] ?? '')], $newDay);
        if ((string) $oldDay['order_status'] !== $newStatus) {
            food_order_log(null, $calendarId, $newStatus === 'closed' ? 'day_close' : 'day_open', (int) $user['id'], (string) $oldDay['order_status'], $newStatus);
        }
        $pdo->commit();
        food_order_audit('food_order_day_saved', $user, 'food_calendar', $calendarId, ['order_status' => (string) $oldDay['order_status'], 'note' => (string) ($oldDay['note'] ?? '')], $newDay);
        return ['ok' => true, 'calendar_id' => $calendarId, 'order_status' => $newStatus, 'message' => 'برنامهٔ روز ذخیره شد.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function food_order_status_summary(): array
{
    food_order_schema_ensure();
    $today = food_order_today();
    $stmt = db()->prepare("SELECT meal_type, COUNT(*) AS order_count FROM food_orders WHERE food_date = ? AND status = 'active' GROUP BY meal_type");
    $stmt->execute([$today]);
    $counts = ['lunch' => 0, 'dinner' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $meal = (string) ($row['meal_type'] ?? 'lunch');
        if (isset($counts[$meal])) {
            $counts[$meal] = (int) $row['order_count'];
        }
    }
    return [
        'today' => $today,
        'active_orders_today' => $counts['lunch'], // backward-compatible lunch total
        'active_lunch_orders_today' => $counts['lunch'],
        'active_dinner_orders_today' => $counts['dinner'],
        'active_orders_total_today' => $counts['lunch'] + $counts['dinner'],
    ];
}

function food_order_api_body(): array
{
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || trim($raw) === '') {
        return is_array($_POST ?? null) ? $_POST : [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function food_order_send_json(array $payload, int $status = 200): never
{
    if (function_exists('food_ticket_api_json')) {
        food_ticket_api_json($payload, $status);
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function food_order_safe_error(Throwable $e): string
{
    if ($e instanceof PDOException) {
        app_error_log('[food-order] database API error: ' . $e->getMessage());
        return 'خطای پایگاه داده رخ داد؛ عملیات ثبت نشد. پس از بررسی وضعیت دیتابیس دوباره تلاش کنید.';
    }
    return $e->getMessage() !== '' ? $e->getMessage() : 'عملیات سفارش غذا انجام نشد.';
}

function food_order_export_safe_value(mixed $value): string
{
    $text = (string) $value;
    // Prevent formula execution when CSV/XLS is opened in spreadsheet software.
    if (preg_match('/^[\s\x{FEFF}]*[=+\-@]/u', $text)) {
        return "'" . $text;
    }
    return $text;
}

function food_order_export_manager_orders(string $date, array $user, string $mealType = 'lunch', array $selectedFields = []): never
{
    food_order_require_permission($user, 'food.order_close');
    $iso = food_order_iso_date($date);
    if ($iso === null) {
        food_order_send_json(['error' => 'تاریخ روز سفارش معتبر نیست.'], 400);
    }
    $mealType = food_order_meal_type($mealType);
    $items = food_order_manager_orders($iso, $mealType);
    $dateLabel = food_order_jalali_label($iso);
    $fieldLabels = [
        'row' => 'ردیف', 'national_code' => 'کد ملی', 'employee_number' => 'کد پرسنلی',
        'full_name' => 'نام و نام خانوادگی', 'food_name' => 'نوع غذا', 'food_date' => 'تاریخ غذا',
        'reserve_date' => 'تاریخ رزرو', 'reserve_time' => 'ساعت رزرو', 'status_label' => 'وضعیت سفارش',
        'record_type' => 'نوع ثبت', 'menu_no' => 'شماره منو', 'meal_type' => 'وعده',
    ];
    if ($selectedFields === []) {
        $selectedFields = array_keys($fieldLabels);
    }
    $selectedFields = array_values(array_unique(array_filter(array_map('strval', $selectedFields), static fn (string $field): bool => isset($fieldLabels[$field]))));
    if ($selectedFields === []) {
        food_order_send_json(['error' => 'حداقل یک فیلد معتبر برای گزارش انتخاب کنید.'], 400);
    }
    $headers = array_map(static fn (string $field): string => $fieldLabels[$field], $selectedFields);
    $data = [];
    foreach ($items as $index => $item) {
        $row = [];
        foreach ($selectedFields as $field) {
            $row[] = match ($field) {
                'row' => $index + 1,
                'national_code' => $item['national_code'] ?? '',
                'employee_number' => $item['employee_number'] ?? '',
                'full_name' => $item['full_name'] ?? '',
                'food_name' => $item['food_name'] ?? '',
                'food_date' => food_order_jalali_label((string) ($item['food_date'] ?? $iso)),
                'reserve_date' => food_order_jalali_label((string) ($item['reserve_date'] ?? '')),
                'reserve_time' => substr((string) ($item['reserve_time'] ?? ''), 0, 5),
                'status_label' => (string) ($item['status'] ?? '') === 'active' ? 'فعال' : 'لغوشده',
                'record_type' => !empty($item['is_proxy']) ? 'نیابتی' : 'شخصی',
                'menu_no' => (int) ($item['menu_no'] ?? 0) > 0 ? 'منوی ' . (int) $item['menu_no'] : 'بدون منو',
                'meal_type' => $mealType === 'dinner' ? 'شام' : 'ناهار',
                default => '',
            };
        }
        $data[] = $row;
    }
    foreach ($data as &$row) {
        foreach ($row as &$value) {
            $value = food_order_export_safe_value($value);
        }
        unset($value);
    }
    unset($row);
    food_order_audit('food_order_daily_orders_export', $user, 'food_orders', 0, null, [
        'food_date' => $iso,
        'meal_type' => $mealType,
        'fields' => $selectedFields,
        'row_count' => count($data),
    ]);
    $rangeToken = str_replace('-', '', $iso);
    $mealLabel = $mealType === 'dinner' ? 'شام' : 'ناهار';
    $title = 'فهرست سفارش‌های ' . $mealLabel . ' روز ' . $dateLabel;
    food_order_send_xlsx('سفارش‌های-روز-' . $rangeToken . '.xlsx', $title, $headers, $data, $dateLabel);
}

/** خروجی XLSX واقعی برای سفارش‌ها و آمار غذا (بدون وابستگی به index.php). */
function food_order_send_xlsx(string $filename, string $title, array $headers, array $data, string $dateLabel = ''): never
{
    require_once __DIR__ . '/xlsx-writer.php';
    $columnCount = max(1, count($headers));
    $rows = [];
    $rows[] = [$title];
    if ($dateLabel !== '') {
        $rows[] = [$dateLabel];
    }
    $rows[] = $headers;
    foreach ($data as $row) {
        $rows[] = array_values($row);
    }
    $bytes = xlsx_build([[
        'name' => 'گزارش',
        'rows' => $rows,
        'merges' => ['A1:' . xlsx_col_name($columnCount - 1) . '1'],
        'widths' => array_fill(0, $columnCount, 20),
    ]]);
    xlsx_send($filename, $bytes);
}

function food_order_export_statistics_range(string $fromDate, string $toDate, array $user, string $mode = 'food', string $mealType = 'lunch'): never
{
    $mode = strtolower(trim($mode));
    $mealType = food_order_meal_type($mealType);
    if (!in_array($mode, ['food', 'employee'], true)) {
        food_order_send_json(['error' => 'نوع آمار سفارش معتبر نیست.'], 400);
    }
    $from = food_order_iso_date($fromDate);
    $to = food_order_iso_date($toDate);
    if ($from === null || $to === null || $from > $to) {
        food_order_send_json(['error' => 'بازهٔ تاریخ آمار معتبر نیست.'], 400);
    }
    $items = food_order_statistics_range($from, $to, $mode, $mealType);
    $fromJalali = food_order_jalali_label($from);
    $toJalali = food_order_jalali_label($to);
    $rangeToken = str_replace('-', '', $from) . '-' . str_replace('-', '', $to);
    $rangeLabel = $from === $to ? $fromJalali : $fromJalali . ' تا ' . $toJalali;
    $mealLabel = $mealType === 'dinner' ? 'شام' : 'ناهار';
    $title = ($mode === 'employee' ? 'آمار سفارش نفرات' : 'آمار نوع غذا') . ' — ' . $mealLabel;
    $headers = $mode === 'employee'
        ? ['ردیف', 'نام پرسنل', 'تعداد سفارش فعال']
        : ['ردیف', 'نوع غذا', 'تعداد سفارش فعال'];
    $data = [];
    if ($mode === 'employee') {
        foreach ($items as $index => $item) {
            $data[] = [$index + 1, $item['full_name'], $item['count']];
        }
    } else {
        $headers = ['روز هفته', 'تاریخ شمسی', 'نوع غذا', 'تعداد سفارش فعال'];
        foreach ($items as $day) {
            foreach ($day['foods'] as $food) {
                $data[] = [$day['weekday'], $day['food_date_jalali'], $food['food_name'], $food['count']];
            }
        }
    }
    foreach ($data as &$row) {
        foreach ($row as &$value) {
            $value = food_order_export_safe_value($value);
        }
        unset($value);
    }
    unset($row);
    food_order_audit('food_order_statistics_export', $user, 'food_orders', 0, null, [
        'mode' => $mode,
        'meal_type' => $mealType,
        'from_date' => $from,
        'to_date' => $to,
        'row_count' => count($data),
    ]);
    food_order_send_xlsx('آمار-سفارش-' . $mode . '-' . $rangeToken . '.xlsx', $title . ' ' . $rangeLabel, $headers, $data);
}

/** نقش‌های سامانه‌ای که ممکن است برای مهمان درخواست ثبت کنند؛ دسترسی واقعی همچنان از چارت سازمانی کنترل می‌شود. */
function food_guest_request_roles(): array
{
    return ['primary_admin', 'supervisor', 'support_manager', 'manager', 'inspector'];
}

/** فقط کسانی که جایگاه مدیریتی در چارت دارند (مدیر واحدی که زیر یک معاونت است)؛ نقش سامانه ملاک نیست. */
function food_guest_request_is_reviewer(array $user): bool
{
    if (function_exists('food_order_user_is_cancel_reviewer') && food_order_user_is_cancel_reviewer($user)) {
        return true;
    }
    return (string) ($user['role'] ?? '') === 'manager'
        && function_exists('user_service_group')
        && user_service_group($user) === 'support';
}

function food_guest_request_allowed(array $user): bool
{
    // مدیرِ معاونت درخواست ثبت می‌کند؛ مدیر پشتیبانی و ادمین اصلی هم برای رسیدگی مجازند.
    return food_guest_request_is_reviewer($user) || food_guest_user_deputy_id($user, true) > 0;
}

/** آیا کاربر می‌تواند درخواست مشخصی را مدیریت کند؟ مدیر عادی فقط درخواست خودش را؛
 * مدیر پشتیبانی/ادمین اصلی همهٔ درخواست‌ها را. */
function food_guest_request_can_manage(array $user, array $row): bool
{
    if (food_guest_request_is_reviewer($user)) {
        return true;
    }
    $uid = (int) ($user['id'] ?? 0);
    return $uid > 0
        && (int) ($row['created_by'] ?? 0) === $uid
        && food_guest_user_deputy_id($user, true) > 0;
}

/** ویرایش درخواست روز جاری فقط تا مهلت ثبت مهمان مجاز است؛ سوابق گذشته برای ویرایش قفل‌اند. */
function food_guest_request_change_window_open(string $date, ?DateTimeImmutable $now = null): bool
{
    $now ??= food_order_now();
    $today = $now->format('Y-m-d');
    if ($date < $today) {
        return false;
    }
    return $date > $today || food_guest_request_same_day_is_open($now);
}

/** آخرین دقیقهٔ مجاز ثبت درخواست مهمان برای همان روز؛ مستقل از ساعت قطع سقف فیش مهمان است. */
function food_guest_request_same_day_cutoff(): string
{
    $raw = function_exists('setting') ? setting('food_guest_request_same_day_cutoff', '09:30') : '09:30';
    $raw = trim(food_order_normalize_digits($raw ?? ''));
    if (!preg_match('/^(\d{1,2}):(\d{2})$/', $raw, $parts)) {
        return '09:30';
    }
    $hour = (int) $parts[1];
    $minute = (int) $parts[2];
    if ($hour > 23 || $minute > 59) {
        return '09:30';
    }
    return sprintf('%02d:%02d', $hour, $minute);
}

/** دقیقهٔ تنظیم‌شده هم مجاز است؛ مثلاً با ۰۹:۳۰، ثبت تا ۰۹:۳۰:۵۹ پذیرفته می‌شود. */
function food_guest_request_same_day_is_open(?DateTimeImmutable $now = null): bool
{
    $now ??= food_order_now();
    return $now->format('H:i') <= food_guest_request_same_day_cutoff();
}

/** خطای اعتبارسنجی تاریخ درخواست؛ null یعنی تاریخ در این لحظه قابل ثبت است. */
function food_guest_request_date_error(string $date, ?DateTimeImmutable $now = null): ?string
{
    $now ??= food_order_now();
    $today = $now->format('Y-m-d');
    if ($date < $today) {
        return 'درخواست غذای مهمان برای روزهای گذشته ثبت نمی‌شود.';
    }
    if ($date === $today && !food_guest_request_same_day_is_open($now)) {
        $cutoff = food_guest_request_same_day_cutoff();
        if (function_exists('food_ticket_fa_digits')) {
            $cutoff = food_ticket_fa_digits($cutoff);
        } else {
            $cutoff = strtr($cutoff, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
        }
        return 'مهلت ثبت درخواست غذای مهمان برای امروز تا ساعت ' . $cutoff . ' است؛ پس از آن فقط برای روزهای آینده می‌توانید درخواست ثبت کنید.';
    }
    return null;
}

/** تعداد مهمان‌های درخواستی فعال برای یک روز (سقف حالت «درخواست مهمان»). null یعنی جدول در دسترس نیست. */
function food_guest_requested_total(string $isoDate): ?int
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $isoDate)) {
        return null;
    }
    try {
        $stmt = db()->prepare("SELECT COALESCE(SUM(guest_count), 0) FROM food_guest_requests WHERE request_date = ? AND status = 'active'");
        $stmt->execute([$isoDate]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        app_error_log('[food-order] guest request total failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * موارد ناقص سفارش مهمان را برمی‌گرداند (خالی یعنی آماده). بدون پرتاب خطا.
 * نام دیتابیس فعلی هم در پیام می‌آید تا اگر سامانه به دیتابیس دیگری وصل است معلوم شود.
 */
function food_guest_schema_missing(): array
{
    try {
        $db = (string) db()->query('SELECT DATABASE()')->fetchColumn();
        $tableStmt = db()->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_guest_requests'");
        if ((int) $tableStmt->fetchColumn() === 0) {
            return ['جدول food_guest_requests (upgrade-1.39)', 'دیتابیس فعلی: ' . $db];
        }
        $colStmt = db()->query(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_guest_requests'"
        );
        $have = array_map('strval', $colStmt->fetchAll(PDO::FETCH_COLUMN));
        $missing = [];
        foreach (['request_date', 'organization', 'guest_count', 'food_id', 'requester_name', 'deputy_unit_id', 'status'] as $col) {
            if (!in_array($col, $have, true)) {
                $missing[] = 'ستون ' . $col . ' (upgrade-' . ($col === 'deputy_unit_id' ? '1.40' : '1.39') . ')';
            }
        }
        return $missing;
    } catch (Throwable $e) {
        app_error_log('[food-order] guest schema check failed: ' . $e->getMessage());
        return ['بررسی دیتابیس انجام نشد'];
    }
}

/** جدول و ستون‌های سفارش مهمان آماده‌اند؟ */
function food_guest_schema_ready(): bool
{
    return food_guest_schema_missing() === [];
}

/** برای API سفارش مهمان: اگر migration اجرا نشده، پیام فارسی روشن می‌دهد. */
function food_guest_schema_ensure(): void
{
    if (!food_guest_schema_ready()) {
        throw new RuntimeException('بخش سفارش مهمان هنوز فعال نشده است؛ ابتدا upgrade-1.39-food-guest-requests.sql و upgrade-1.40-food-guest-deputy.sql را روی دیتابیس اجرا کنید.');
    }
}

/** معاونت‌های فعال چارت سازمانی (فهرست انتخاب در فرم و گزارش). */
function food_guest_deputies(): array
{
    if (!function_exists('org_deputy_units')) {
        return [];
    }
    return array_map(
        static fn (array $u): array => ['id' => (int) $u['id'], 'name' => (string) $u['name']],
        org_deputy_units()
    );
}

/**
 * معاونتِ کاربر از چارت سازمانی: از واحد خود کاربر (users.org_unit_id) و واحدهایی که مدیرشان است
 * شروع می‌کند و در سلسلهٔ والدها بالا می‌رود تا به واحد نوع deputy برسد. 0 یعنی پیدا نشد.
 */
function food_guest_user_deputy_id(array $user, bool $managedOnly = false): int
{
    $uid = (int) ($user['id'] ?? 0);
    if ($uid <= 0) {
        return 0;
    }
    $starts = [];
    try {
        if ($managedOnly) {
            throw new RuntimeException('managed-only');
        }
        $own = db()->prepare('SELECT org_unit_id FROM users WHERE id = ?');
        $own->execute([$uid]);
        $ownUnit = (int) ($own->fetchColumn() ?: 0);
        if ($ownUnit > 0) {
            $starts[] = $ownUnit;
        }
    } catch (Throwable $e) {
        // ستون org_unit_id هنوز ساخته نشده است؛ فقط مسیر مدیریت واحد بررسی می‌شود.
    }
    try {
        $managed = db()->prepare('SELECT id FROM org_units WHERE manager_user_id = ? AND is_active = 1 UNION SELECT unit_id FROM org_unit_managers WHERE user_id = ?');
        $managed->execute([$uid, $uid]);
        foreach ($managed->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $starts[] = (int) $id;
        }
    } catch (Throwable $e) {
        // جدول‌های چارت سازمانی در دسترس نیستند.
    }
    $unitStmt = db()->prepare('SELECT id, parent_id, unit_type, is_active FROM org_units WHERE id = ?');
    foreach (array_values(array_unique(array_filter($starts))) as $start) {
        $current = (int) $start;
        for ($depth = 0; $depth < 10 && $current > 0; $depth++) {
            $unitStmt->execute([$current]);
            $unit = $unitStmt->fetch(PDO::FETCH_ASSOC);
            if (!$unit) {
                break;
            }
            if ((string) $unit['unit_type'] === 'deputy' && (int) $unit['is_active'] === 1) {
                return (int) $unit['id'];
            }
            $current = (int) ($unit['parent_id'] ?? 0);
        }
    }
    return 0;
}

function food_guest_request_list(array $user, bool $includePast = false): array
{
    food_guest_schema_ensure();
    $now = food_order_now();
    $today = $now->format('Y-m-d');
    $uid = (int) ($user['id'] ?? 0);
    $isReviewer = food_guest_request_is_reviewer($user);
    $myDeputyId = $isReviewer ? 0 : food_guest_user_deputy_id($user, true);
    $dateFilter = $includePast ? '' : 'WHERE r.request_date >= ?';
    $sortOrder = $includePast ? 'DESC' : 'ASC';
    $idSortOrder = $includePast ? 'DESC' : 'ASC';
    $rowLimit = $includePast ? '' : 'LIMIT 300';
    $stmt = db()->prepare(
        "SELECT r.id, r.request_date, r.organization, r.guest_count, r.requester_name, r.note, r.status,
                r.created_by, r.created_at, r.food_id, c.food_name, r.deputy_unit_id, ou.name AS deputy_name,
                TRIM(CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,''))) AS created_by_name
           FROM food_guest_requests r
           LEFT JOIN food_catalog c ON c.id = r.food_id
           LEFT JOIN org_units ou ON ou.id = r.deputy_unit_id
           LEFT JOIN users u ON u.id = r.created_by
          $dateFilter
          ORDER BY r.request_date $sortOrder, r.id $idSortOrder
          $rowLimit"
    );
    $stmt->execute($includePast ? [] : [$today]);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $canManage = $isReviewer || ($uid > 0 && $myDeputyId > 0 && (int) ($r['created_by'] ?? 0) === $uid);
        $date = (string) $r['request_date'];
        $active = (string) $r['status'] === 'active';
        $canEdit = $canManage && $active && food_guest_request_change_window_open($date, $now);
        $canCancel = $canManage && $active && $date > $today;
        // سوابق گذشته برای مدیران مجاز قابل حذف است؛ محدودیت زمانی حذف امروز/آینده حفظ می‌شود.
        $canDelete = $canManage && ($date < $today || food_guest_request_change_window_open($date, $now));
        $items[] = [
            'id' => (int) $r['id'],
            'can_manage' => $canManage,
            'can_edit' => $canEdit,
            'can_cancel' => $canCancel,
            'can_delete' => $canDelete,
            'request_date' => (string) $r['request_date'],
            'request_jalali' => food_order_jalali_label((string) $r['request_date']),
            'organization' => (string) $r['organization'],
            'guest_count' => (int) $r['guest_count'],
            'food_id' => $r['food_id'] === null ? null : (int) $r['food_id'],
            'food_name' => $r['food_name'] === null ? '' : (string) $r['food_name'],
            'deputy_unit_id' => $r['deputy_unit_id'] === null ? null : (int) $r['deputy_unit_id'],
            'deputy_name' => (string) ($r['deputy_name'] ?? ''),
            'requester_name' => (string) $r['requester_name'],
            'note' => (string) ($r['note'] ?? ''),
            'status' => (string) $r['status'],
            'created_by_name' => (string) ($r['created_by_name'] ?? ''),
            'created_at' => (string) $r['created_at'],
            'editable' => $canEdit,
        ];
    }
    $foods = array_values(array_map(
        static fn (array $f): array => ['id' => $f['id'], 'name' => $f['food_name']],
        array_filter(food_order_catalog(false), static fn (array $f): bool => $f['active'])
    ));
    $myDeputy = null;
    foreach (food_guest_deputies() as $d) {
        if ($d['id'] === $myDeputyId) {
            $myDeputy = $d;
        }
    }
    return [
        'items' => $items,
        'foods' => $foods,
        'my_deputy' => $myDeputy,
        'today' => $today,
        'today_jalali' => food_order_jalali_label($today),
        'same_day_cutoff' => food_guest_request_same_day_cutoff(),
        'same_day_open' => food_guest_request_same_day_is_open($now),
        'mode' => function_exists('food_guest_cap_mode') ? food_guest_cap_mode() : 'requested',
    ];
}

/** ثبت درخواست مهمان؛ برای امروز تا پایان دقیقهٔ مهلت تنظیمی و برای آینده در هر ساعتی مجاز است. */
function food_guest_request_create(array $user, array $body): array
{
    food_guest_schema_ensure();
    $date = food_order_iso_date($body['request_date'] ?? '');
    if ($date === null) {
        throw new RuntimeException('تاریخ شمسی معتبر نیست.');
    }
    $dateError = food_guest_request_date_error($date);
    if ($dateError !== null) {
        throw new RuntimeException($dateError);
    }
    $organization = trim((string) preg_replace('/\s+/u', ' ', (string) ($body['organization'] ?? '')));
    if (mb_strlen($organization) < 2 || mb_strlen($organization) > 190) {
        throw new RuntimeException('نام سازمان یا شرکت باید بین ۲ تا ۱۹۰ نویسه باشد.');
    }
    $count = filter_var(food_order_normalize_digits($body['guest_count'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
    if ($count === false) {
        throw new RuntimeException('تعداد مهمان باید عددی بین ۱ تا ۵۰۰ باشد.');
    }
    // نوع غذا حذف شده است؛ غذای مهمان را سلف طبق تشخیص مدیر تعیین می‌کند.
    $foodId = null;
    // معاونت از چارت سازمانی کاربر خوانده می‌شود؛ مقدار ارسال‌شدهٔ فرم نادیده گرفته می‌شود.
    $deputyId = food_guest_user_deputy_id($user, true);
    if ($deputyId <= 0) {
        throw new RuntimeException('برای ثبت سفارش مهمان باید شما در چارت سازمانی زیر یک معاونت تعریف شده باشید (به‌عنوان عضو واحد یا مدیر واحد). با مدیر سامانه هماهنگ کنید.');
    }
    $requester = trim((string) preg_replace('/\s+/u', ' ', (string) ($body['requester_name'] ?? '')));
    if (mb_strlen($requester) < 2 || mb_strlen($requester) > 150) {
        throw new RuntimeException('نام درخواست‌دهنده باید بین ۲ تا ۱۵۰ نویسه باشد.');
    }
    $note = trim((string) ($body['note'] ?? ''));
    if (mb_strlen($note) > 500) {
        throw new RuntimeException('یادداشت نباید بیش از ۵۰۰ نویسه باشد.');
    }
    $stmt = db()->prepare(
        'INSERT INTO food_guest_requests (request_date, organization, guest_count, food_id, requester_name, deputy_unit_id, note, status, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, \'active\', ?)'
    );
    $stmt->execute([$date, $organization, $count, $foodId, $requester, $deputyId, $note === '' ? null : $note, (int) $user['id']]);
    $id = (int) db()->lastInsertId();
    food_order_audit('food_guest_request_create', $user, 'food_guest_request', $id, null, [
        'request_date' => $date, 'organization' => $organization, 'guest_count' => $count, 'food_id' => $foodId, 'requester_name' => $requester, 'deputy_unit_id' => $deputyId,
    ]);
    return ['id' => $id, 'request_date' => $date];
}

function food_guest_request_cancel(array $user, int $id): void
{
    food_guest_schema_ensure();
    $today = food_order_today();
    $stmt = db()->prepare('SELECT * FROM food_guest_requests WHERE id = ? FOR UPDATE');
    db()->beginTransaction();
    try {
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new RuntimeException('درخواست پیدا نشد.');
        }
        if (!food_guest_request_can_manage($user, $row)) {
            throw new RuntimeException('فقط مدیر ثبت‌کننده یا مدیر پشتیبانی می‌تواند این درخواست را مدیریت کند.');
        }
        if ($row['status'] !== 'active') {
            throw new RuntimeException('این درخواست قبلاً لغو شده است.');
        }
        if ((string) $row['request_date'] <= $today) {
            throw new RuntimeException('درخواست روزهای گذشته یا امروز قابل لغو نیست.');
        }
        db()->prepare("UPDATE food_guest_requests SET status = 'cancelled', cancelled_by = ?, cancelled_at = NOW() WHERE id = ?")
            ->execute([(int) $user['id'], $id]);
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
    food_order_audit('food_guest_request_cancel', $user, 'food_guest_request', $id, $row, ['status' => 'cancelled']);
}

/** ویرایش درخواست فعال توسط مدیر ثبت‌کننده یا مدیر پشتیبانی/ادمین اصلی. */
function food_guest_request_update(array $user, int $id, array $body): array
{
    food_guest_schema_ensure();
    $date = food_order_iso_date($body['request_date'] ?? '');
    if ($date === null) {
        throw new RuntimeException('تاریخ شمسی معتبر نیست.');
    }
    $dateError = food_guest_request_date_error($date);
    if ($dateError !== null) {
        throw new RuntimeException($dateError);
    }
    $organization = trim((string) preg_replace('/\s+/u', ' ', (string) ($body['organization'] ?? '')));
    if (mb_strlen($organization) < 2 || mb_strlen($organization) > 190) {
        throw new RuntimeException('نام سازمان یا شرکت باید بین ۲ تا ۱۹۰ نویسه باشد.');
    }
    $count = filter_var(food_order_normalize_digits($body['guest_count'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
    if ($count === false) {
        throw new RuntimeException('تعداد مهمان باید عددی بین ۱ تا ۵۰۰ باشد.');
    }
    $requester = trim((string) preg_replace('/\s+/u', ' ', (string) ($body['requester_name'] ?? '')));
    if (mb_strlen($requester) < 2 || mb_strlen($requester) > 150) {
        throw new RuntimeException('نام درخواست‌دهنده باید بین ۲ تا ۱۵۰ نویسه باشد.');
    }
    $note = trim((string) ($body['note'] ?? ''));
    if (mb_strlen($note) > 500) {
        throw new RuntimeException('یادداشت نباید بیش از ۵۰۰ نویسه باشد.');
    }

    db()->beginTransaction();
    try {
        $stmt = db()->prepare('SELECT * FROM food_guest_requests WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new RuntimeException('درخواست پیدا نشد.');
        }
        if (!food_guest_request_can_manage($user, $row)) {
            throw new RuntimeException('فقط مدیر ثبت‌کننده یا مدیر پشتیبانی می‌تواند این درخواست را ویرایش کند.');
        }
        if ((string) ($row['status'] ?? '') !== 'active') {
            throw new RuntimeException('درخواست لغوشده قابل ویرایش نیست.');
        }
        if (!food_guest_request_change_window_open((string) ($row['request_date'] ?? ''))) {
            throw new RuntimeException('مهلت ویرایش این درخواست گذشته است.');
        }
        db()->prepare(
            'UPDATE food_guest_requests SET request_date = ?, organization = ?, guest_count = ?, requester_name = ?, note = ? WHERE id = ?'
        )->execute([$date, $organization, $count, $requester, $note === '' ? null : $note, $id]);
        db()->commit();
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        throw $e;
    }
    $newValue = ['request_date' => $date, 'organization' => $organization, 'guest_count' => (int) $count, 'requester_name' => $requester, 'note' => $note];
    food_order_audit('food_guest_request_update', $user, 'food_guest_request', $id, $row, $newValue);
    return ['ok' => true, 'message' => 'درخواست غذای مهمان ویرایش شد.'];
}

/** حذف قطعی درخواست؛ پیش از حذف برای ثبت مقدار قبلی در Audit تلاش می‌شود. */
function food_guest_request_delete(array $user, int $id): array
{
    food_guest_schema_ensure();
    db()->beginTransaction();
    try {
        $stmt = db()->prepare('SELECT * FROM food_guest_requests WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new RuntimeException('درخواست پیدا نشد.');
        }
        if (!food_guest_request_can_manage($user, $row)) {
            throw new RuntimeException('فقط مدیر ثبت‌کننده یا مدیر پشتیبانی می‌تواند این درخواست را حذف کند.');
        }
        // حذف سوابق گذشته مجاز است؛ محدودیت زمانی حذف امروز/آینده و دسترسی مالک/بازبین حفظ می‌شود.
        $requestDate = (string) ($row['request_date'] ?? '');
        if ($requestDate >= food_order_today() && !food_guest_request_change_window_open($requestDate)) {
            throw new RuntimeException('مهلت حذف درخواست امروز گذشته است.');
        }
        // پیش از حذف، تلاش برای ثبت Audit مقدار قبلی انجام می‌شود.
        food_order_audit('food_guest_request_delete', $user, 'food_guest_request', $id, $row, null);
        db()->prepare('DELETE FROM food_guest_requests WHERE id = ?')->execute([$id]);
        db()->commit();
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        throw $e;
    }
    return ['ok' => true, 'message' => 'درخواست غذای مهمان حذف شد.'];
}

function food_order_api_handle(string $route, array $user): never
{
    if (!$user) {
        food_order_send_json(['error' => 'برای استفاده از سفارش غذا ابتدا وارد سامانه شوید.'], 401);
    }
    $route = trim(rawurldecode($route), '/');
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'POST') {
        require_csrf();
    }
    try {
        if ($method === 'GET' && $route === 'month') {
            $month = (string) ($_GET['month'] ?? '');
            food_order_send_json(food_order_month_status($month, $user, false, false, food_order_meal_type($_GET['meal_type'] ?? 'lunch')));
        }
        if ($method === 'POST' && $route === 'proxy-month') {
            check_api_rate_limit('food_order_proxy_month_' . (int) $user['id'], 80, 60);
            $body = food_order_api_body();
            $target = food_order_verify_employee_national_code(
                max(0, (int) ($body['employee_id'] ?? 0)),
                (string) ($body['national_code'] ?? ''),
                true
            );
            food_order_send_json(food_order_month_status((string) ($body['month'] ?? ''), $target, false, false, food_order_meal_type($body['meal_type'] ?? 'lunch')));
        }
        if ($method === 'GET' && $route === 'guest-requests') {
            if (!food_guest_request_allowed($user)) {
                food_order_send_json(['error' => 'این بخش فقط برای مدیران و بالاتر فعال است.'], 403);
            }
            food_order_send_json(food_guest_request_list($user) + ['allowed' => true]);
        }
        if ($method === 'POST' && $route === 'guest-request') {
            if (!food_guest_request_allowed($user)) {
                food_order_send_json(['error' => 'این بخش فقط برای مدیران و بالاتر فعال است.'], 403);
            }
            check_api_rate_limit('food_guest_request_' . (int) $user['id'], 30, 60);
            $created = food_guest_request_create($user, food_order_api_body());
            food_order_send_json(['ok' => true, 'message' => 'درخواست غذای مهمان ثبت شد.'] + $created);
        }
        if ($method === 'POST' && $route === 'guest-request-cancel') {
            if (!food_guest_request_allowed($user)) {
                food_order_send_json(['error' => 'این بخش فقط برای مدیر درخواست‌کننده یا مدیر پشتیبانی فعال است.'], 403);
            }
            $body = food_order_api_body();
            food_guest_request_cancel($user, (int) ($body['id'] ?? 0));
            food_order_send_json(['ok' => true, 'message' => 'درخواست غذای مهمان لغو شد.']);
        }
        if ($method === 'POST' && $route === 'guest-request-update') {
            if (!food_guest_request_allowed($user)) {
                food_order_send_json(['error' => 'این بخش فقط برای مدیر درخواست‌کننده یا مدیر پشتیبانی فعال است.'], 403);
            }
            $body = food_order_api_body();
            food_order_send_json(food_guest_request_update($user, (int) ($body['id'] ?? 0), $body));
        }
        if ($method === 'POST' && $route === 'guest-request-delete') {
            if (!food_guest_request_allowed($user)) {
                food_order_send_json(['error' => 'این بخش فقط برای مدیر درخواست‌کننده یا مدیر پشتیبانی فعال است.'], 403);
            }
            $body = food_order_api_body();
            food_order_send_json(food_guest_request_delete($user, (int) ($body['id'] ?? 0)));
        }
        if ($method === 'GET' && $route === 'my-orders') {
            $range = food_order_list_range((string) ($_GET['from'] ?? ''), (string) ($_GET['to'] ?? ''), (int) $user['id']);
            food_order_send_json([
                'items' => food_order_my_orders((int) $user['id'], 400, $range['from'], $range['to']),
                'range' => $range,
            ]);
        }
        if ($method === 'POST' && $route === 'proxy-orders') {
            check_api_rate_limit('food_order_proxy_orders_' . (int) $user['id'], 80, 60);
            $body = food_order_api_body();
            $target = food_order_verify_employee_national_code(
                max(0, (int) ($body['employee_id'] ?? 0)),
                (string) ($body['national_code'] ?? ''),
                true
            );
            $range = food_order_list_range((string) ($body['from'] ?? ''), (string) ($body['to'] ?? ''), (int) $target['id']);
            food_order_send_json([
                'items' => food_order_my_orders((int) $target['id'], 400, $range['from'], $range['to']),
                'range' => $range,
            ]);
        }
        if ($method === 'GET' && $route === 'search-users') {
            check_api_rate_limit('food_order_search_' . (int) $user['id'], 30, 60);
            food_order_send_json(['items' => food_order_employee_search((string) ($_GET['q'] ?? ''))]);
        }
        if ($method === 'POST' && $route === 'verify-proxy') {
            check_api_rate_limit('food_order_proxy_verify_' . (int) $user['id'], 80, 60);
            $body = food_order_api_body();
            $target = food_order_verify_employee_national_code(
                max(0, (int) ($body['employee_id'] ?? 0)),
                (string) ($body['national_code'] ?? ''),
                true
            );
            food_order_send_json(['ok' => true, 'person' => [
                'id' => (int) $target['id'],
                'name' => food_order_full_name($target),
            ]]);
        }
        if ($method === 'POST' && $route === 'order') {
            $body = food_order_api_body();
            $employeeId = max(0, (int) ($body['employee_id'] ?? $user['id']));
            $result = food_order_create_or_change(
                $employeeId,
                (int) $user['id'],
                (string) ($body['food_date'] ?? ''),
                (int) ($body['calendar_item_id'] ?? 0),
                $user,
                isset($body['national_code']) ? (string) $body['national_code'] : null,
                !empty($body['emergency']),
                food_order_meal_type($body['meal_type'] ?? 'lunch')
            );
            food_order_send_json($result);
        }
        if ($method === 'POST' && $route === 'cancel') {
            $body = food_order_api_body();
            food_order_send_json(food_order_cancel((int) ($body['order_id'] ?? 0), $user));
        }
        if ($method === 'POST' && $route === 'cancel-proxy') {
            $body = food_order_api_body();
            food_order_send_json(food_order_cancel_proxy(
                (int) ($body['order_id'] ?? 0),
                (int) ($body['employee_id'] ?? 0),
                (string) ($body['national_code'] ?? ''),
                $user
            ));
        }
        if ($method === 'POST' && $route === 'cancel-request') {
            check_api_rate_limit('food_order_cancel_request_' . (int) $user['id'], 10, 60);
            $body = food_order_api_body();
            food_order_send_json(food_order_cancel_request_create((int) ($body['order_id'] ?? 0), $user));
        }
        if ($method === 'POST' && $route === 'cancel-request-proxy') {
            check_api_rate_limit('food_order_cancel_request_' . (int) $user['id'], 10, 60);
            $body = food_order_api_body();
            $target = food_order_verify_employee_national_code(
                max(0, (int) ($body['employee_id'] ?? 0)),
                (string) ($body['national_code'] ?? ''),
                true
            );
            food_order_send_json(food_order_cancel_request_create((int) ($body['order_id'] ?? 0), $user, $target));
        }
        if ($method === 'GET' && $route === 'day-visit') {
            food_order_require_permission($user, 'foodorder.self');
            $subject = food_order_day_visit_subject($user, [
                'employee_id' => (int) ($_GET['employee_id'] ?? 0),
                'national_code' => (string) ($_GET['national_code'] ?? ''),
            ]);
            food_order_send_json(food_ticket_day_visit_state($subject, '', $user));
        }
        if ($method === 'POST' && $route === 'day-visit') {
            food_order_require_permission($user, 'foodorder.self');
            check_api_rate_limit('food_day_visit_' . (int) $user['id'], 20, 60);
            $body = food_order_api_body();
            $subject = food_order_day_visit_subject($user, $body);
            food_order_send_json(food_ticket_day_visit_save($subject, (int) ($body['group_id'] ?? 0)));
        }
        if ($method === 'POST' && $route === 'day-visit-cancel') {
            food_order_require_permission($user, 'foodorder.self');
            check_api_rate_limit('food_day_visit_' . (int) $user['id'], 20, 60);
            $body = food_order_api_body();
            $subject = food_order_day_visit_subject($user, $body);
            food_order_send_json(food_ticket_day_visit_clear($subject, '', $user));
        }
        food_order_send_json(['error' => 'مسیر API سفارش غذا پیدا نشد.'], 404);
    } catch (Throwable $e) {
        $status = str_contains($e->getMessage(), 'دسترسی') ? 403 : 400;
        food_order_send_json(['error' => food_order_safe_error($e)], $status);
    }
}


/** مسیرهای «سفارش اضطراری» در فرم برنامه غذایی (فقط food.emergency). */
function food_order_emergency_handle(string $route, string $method, array $user): never
{
    if (!food_order_user_can($user, 'food.emergency')) {
        food_order_send_json(['error' => 'دسترسی سفارش اضطراری ندارید.'], 403);
    }
    $body = $method === 'POST' ? food_order_api_body() : [];
    try {
        if ($method === 'GET' && $route === 'food-menu/emergency/people') {
            // فهرست کاربران فعال دارای کد پرسنلی (بدون کد ملی) برای انتخاب در سفارش اضطراری.
            $rows = db()->query("SELECT id, username, full_name, first_name, last_name FROM users WHERE is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> '' ORDER BY full_name, username LIMIT 3000")->fetchAll(PDO::FETCH_ASSOC);
            $items = array_map(static fn(array $r): array => ['id' => (int) $r['id'], 'name' => food_order_full_name($r), 'username' => (string) $r['username']], $rows);
            food_order_send_json(['items' => $items]);
        }
        if ($method === 'POST' && $route === 'food-menu/emergency/lookup') {
            check_api_rate_limit('food_order_emergency_lookup_' . (int) $user['id'], 30, 60);
            $employeeId = max(0, (int) ($body['employee_id'] ?? 0));
            if ($employeeId < 1) {
                throw new RuntimeException('ابتدا کاربر را انتخاب کنید.');
            }
            $nc = food_order_digits((string) ($body['national_code'] ?? ''));
            if (strlen($nc) !== 10) {
                throw new RuntimeException('کد ملی باید ۱۰ رقم باشد.');
            }
            // کد ملی باید با همان کاربر انتخاب‌شده تطبیق داشته باشد؛ فقط افراد دارای کد پرسنلی قابل سفارش‌اند.
            $stmt = db()->prepare("SELECT id, username, full_name, first_name, last_name FROM users WHERE id = ? AND national_code = ? AND is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> '' LIMIT 1");
            $stmt->execute([$employeeId, $nc]);
            $person = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$person) {
                throw new RuntimeException('کد ملی با کاربر انتخاب‌شده تطبیق ندارد.');
            }
            food_order_send_json(['ok' => true, 'person' => ['id' => (int) $person['id'], 'name' => food_order_full_name($person), 'username' => (string) $person['username']]]);
        }
        if ($method === 'GET' && $route === 'food-menu/emergency/orders') {
            $date = food_order_iso_date((string) ($_GET['date'] ?? ''));
            $employeeId = max(0, (int) ($_GET['employee_id'] ?? 0));
            if ($date === null || $employeeId < 1) {
                throw new RuntimeException('تاریخ یا فرد معتبر نیست.');
            }
            $orders = food_order_my_orders($employeeId, 50, $date, $date);
            $mealType = food_order_meal_type($_GET['meal_type'] ?? 'lunch');
            food_order_send_json(['items' => array_values(array_filter($orders, static fn(array $order): bool => ($order['meal_type'] ?? 'lunch') === $mealType))]);
        }
        if ($method === 'POST' && $route === 'food-menu/emergency/order') {
            $result = food_order_create_or_change(
                max(0, (int) ($body['employee_id'] ?? 0)),
                (int) $user['id'],
                (string) ($body['food_date'] ?? ''),
                (int) ($body['calendar_item_id'] ?? 0),
                $user,
                isset($body['national_code']) ? (string) $body['national_code'] : null,
                true,
                food_order_meal_type($body['meal_type'] ?? 'lunch')
            );
            food_order_send_json($result);
        }
        if ($method === 'POST' && $route === 'food-menu/emergency/cancel') {
            food_order_send_json(food_order_cancel_proxy(
                (int) ($body['order_id'] ?? 0),
                (int) ($body['employee_id'] ?? 0),
                (string) ($body['national_code'] ?? ''),
                $user,
                true
            ));
        }
        food_order_send_json(['error' => 'مسیر سفارش اضطراری پیدا نشد.'], 404);
    } catch (Throwable $e) {
        $status = str_contains($e->getMessage(), 'دسترسی') ? 403 : 400;
        food_order_send_json(['error' => food_order_safe_error($e)], $status);
    }
}

/** Snapshot and batch actions used by the manager-only bulk food operations tab. */
function food_order_bulk_snapshot(string $dateInput, string $mealType, array $actor): array
{
    food_order_require_permission($actor, 'food.emergency');
    food_order_schema_ensure();
    $date = food_order_iso_date($dateInput);
    $mealType = food_order_meal_type($mealType);
    if ($date === null) {
        throw new RuntimeException('تاریخ عملیات گروهی معتبر نیست.');
    }
    if (!food_ticket_groups_ensure()) {
        throw new RuntimeException('جدول گروه‌های غذایی در دسترس نیست.');
    }

    $calendarStmt = db()->prepare('SELECT id, order_status FROM food_calendar WHERE food_date = ? AND meal_type = ? LIMIT 1');
    $calendarStmt->execute([$date, $mealType]);
    $calendar = $calendarStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $lateWindow = $calendar ? food_order_emergency_window_state((int) $calendar['id']) : ['active' => false, 'expires_at' => '', 'reason' => ''];
    $items = [];
    if ($calendar) {
        $itemStmt = db()->prepare("SELECT i.id AS item_id, i.food_id, i.sort_order, c.food_name
            FROM food_calendar_items i JOIN food_catalog c ON c.id = i.food_id
            WHERE i.calendar_id = ? AND i.active = 1 AND c.active = 1
            ORDER BY i.sort_order, i.id");
        $itemStmt->execute([(int) $calendar['id']]);
        foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $items[] = ['item_id' => (int) $row['item_id'], 'food_id' => (int) $row['food_id'], 'food_name' => (string) $row['food_name'], 'sort_order' => (int) $row['sort_order']];
        }
    }

    $orders = [];
    $orderStmt = db()->prepare("SELECT o.id, o.employee_id, o.calendar_item_id, o.status, c.food_name
        FROM food_orders o JOIN food_calendar_items i ON i.id = o.calendar_item_id
        JOIN food_catalog c ON c.id = i.food_id
        WHERE o.food_date = ? AND o.meal_type = ? ORDER BY o.id DESC");
    $orderStmt->execute([$date, $mealType]);
    foreach ($orderStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int) $row['employee_id'];
        if (!isset($orders[$id])) {
            $orders[$id] = ['order_id' => (int) $row['id'], 'item_id' => (int) $row['calendar_item_id'], 'food_name' => (string) $row['food_name'], 'status' => (string) $row['status']];
        }
    }

    $visits = [];
    $visitLocks = [];
    $visitStmt = db()->prepare('SELECT v.user_id, v.group_id, g.title FROM food_ticket_day_visits v JOIN food_ticket_groups g ON g.id = v.group_id WHERE v.visit_date = ?');
    $visitStmt->execute([$date]);
    foreach ($visitStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $groupId = (int) $row['group_id'];
        if (!array_key_exists($groupId, $visitLocks)) {
            $visitLocks[$groupId] = food_ticket_group_absence_locked($groupId, $date);
        }
        $visits[(int) $row['user_id']] = ['group_id' => $groupId, 'title' => (string) $row['title'], 'locked' => $visitLocks[$groupId]];
    }
    $homeGroups = [];
    $homeGroupLocks = [];
    $homeStmt = db()->query('SELECT m.user_id, m.group_id, g.title FROM food_ticket_group_members m JOIN food_ticket_groups g ON g.id = m.group_id');
    foreach ($homeStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $homeGroupId = (int) $row['group_id'];
        if (!array_key_exists($homeGroupId, $homeGroupLocks)) {
            $homeGroupLocks[$homeGroupId] = food_ticket_group_absence_locked($homeGroupId, $date);
        }
        $homeGroups[(int) $row['user_id']] = [
            'group_id' => $homeGroupId,
            'title' => (string) $row['title'],
            'locked' => $homeGroupLocks[$homeGroupId],
        ];
    }

    // Include both domain and local active staff with an employee number. National codes are never returned.
    $peopleStmt = db()->query("SELECT u.id, u.username, u.full_name, u.first_name, u.last_name, u.employee_number,
            COALESCE(NULLIF(d.name, ''), NULLIF(u.department, ''), '') AS department_name
        FROM users u LEFT JOIN departments d ON d.id = u.department_id
        WHERE u.is_active = 1 AND u.employee_number IS NOT NULL AND TRIM(u.employee_number) <> ''
        ORDER BY u.full_name, u.username LIMIT 3000");
    $people = [];
    foreach ($peopleStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int) $row['id'];
        $people[] = [
            'id' => $id,
            'name' => food_order_full_name($row),
            'employee_number' => (string) $row['employee_number'],
            'department' => (string) $row['department_name'],
            'order' => $orders[$id] ?? null,
            'visit' => $visits[$id] ?? null,
            'home_group' => $homeGroups[$id] ?? null,
        ];
    }
    $groupStmt = db()->query("SELECT id, title, visit_change_until FROM food_ticket_groups WHERE status = 'active' ORDER BY title, id");
    $groups = [];
    foreach ($groupStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $groupId = (int) $row['id'];
        $groups[] = ['id' => $groupId, 'title' => (string) $row['title'], 'visit_change_until' => food_ticket_visit_change_hhmm($row['visit_change_until'] ?? ''), 'locked' => food_ticket_group_absence_locked($groupId, $date)];
    }
    $today = food_order_today();
    return [
        'date' => $date,
        'meal_type' => $mealType,
        'today' => $today,
        'is_past' => $date < $today,
        'has_calendar' => $calendar !== null,
        'order_status' => $calendar ? (string) $calendar['order_status'] : '',
        'late_order_window_open' => !empty($lateWindow['active']),
        'late_order_window_expires_at' => (string) ($lateWindow['expires_at'] ?? ''),
        'items' => $items,
        'groups' => $groups,
        'people' => $people,
    ];
}

function food_order_bulk_apply(array $body, array $actor): array
{
    food_order_require_permission($actor, 'food.emergency');
    food_order_schema_ensure();
    check_api_rate_limit('food_order_bulk_' . (int) ($actor['id'] ?? 0), 12, 60);
    $date = food_order_iso_date((string) ($body['date'] ?? ''));
    $mealType = food_order_meal_type($body['meal_type'] ?? 'lunch');
    $operation = strtolower(trim((string) ($body['operation'] ?? '')));
    $ids = is_array($body['employee_ids'] ?? null) ? array_values(array_unique(array_filter(array_map('intval', $body['employee_ids']), static fn (int $id): bool => $id > 0))) : [];
    if ($date === null || !in_array($operation, ['order', 'cancel', 'canteen'], true) || $ids === [] || count($ids) > 100) {
        throw new RuntimeException('تاریخ، نوع عملیات یا فهرست پرسنل معتبر نیست؛ حداکثر ۱۰۰ نفر را در یک نوبت انتخاب کنید.');
    }
    if ($date < food_order_today()) {
        if (!in_array($operation, ['order', 'cancel'], true)) {
            throw new RuntimeException('برای تاریخ گذشته فقط ثبت یا لغو سفارش در مهلت اضطراری همان روز و وعده مجاز است.');
        }
        $calendarStmt = db()->prepare('SELECT id FROM food_calendar WHERE food_date = ? AND meal_type = ? LIMIT 1');
        $calendarStmt->execute([$date, $mealType]);
        $calendarId = (int) $calendarStmt->fetchColumn();
        if ($calendarId < 1 || empty(food_order_emergency_window_state($calendarId)['active'])) {
            throw new RuntimeException('عملیات گروهی برای تاریخ گذشته فقط هنگام فعال‌بودن مهلت اضطراری همان روز و وعده مجاز است.');
        }
    }
    $itemId = max(0, (int) ($body['item_id'] ?? 0));
    $groupId = max(0, (int) ($body['group_id'] ?? 0));
    $reason = trim((string) ($body['reason'] ?? ''));
    $reason = function_exists('mb_substr') ? mb_substr($reason, 0, 300, 'UTF-8') : substr($reason, 0, 300);
    if ($operation === 'order' && $itemId < 1) {
        throw new RuntimeException('برای ثبت یا تغییر سفارش، غذا را انتخاب کنید.');
    }
    if ($operation === 'canteen' && $groupId < 1) {
        throw new RuntimeException('سلف مقصد را انتخاب کنید.');
    }
    if ($operation === 'cancel' && $reason === '') {
        throw new RuntimeException('برای لغو گروهی، ثبت دلیل الزامی است.');
    }

    $results = [];
    $actorId = (int) ($actor['id'] ?? 0);
    foreach ($ids as $employeeId) {
        $name = 'پرسنل #' . $employeeId;
        try {
            $personStmt = db()->prepare("SELECT id, username, full_name, first_name, last_name, employee_number, role, is_active
                FROM users WHERE id = ? AND is_active = 1 AND employee_number IS NOT NULL AND TRIM(employee_number) <> '' LIMIT 1");
            $personStmt->execute([$employeeId]);
            $person = $personStmt->fetch(PDO::FETCH_ASSOC);
            if (!$person) {
                throw new RuntimeException('کاربر فعال با شمارهٔ پرسنلی معتبر پیدا نشد.');
            }
            $name = food_order_full_name($person);
            if ($operation === 'order') {
                $result = food_order_create_or_change($employeeId, $actorId, $date, $itemId, $actor, null, true, $mealType, true);
                $message = (string) ($result['message'] ?? 'سفارش ثبت شد.');
            } elseif ($operation === 'cancel') {
                $orderStmt = db()->prepare("SELECT id FROM food_orders WHERE employee_id = ? AND food_date = ? AND meal_type = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
                $orderStmt->execute([$employeeId, $date, $mealType]);
                $orderId = (int) ($orderStmt->fetchColumn() ?: 0);
                if ($orderId < 1) {
                    throw new RuntimeException('برای این نفر سفارش فعال پیدا نشد.');
                }
                $result = food_order_cancel_proxy($orderId, $employeeId, '', $actor, true, true, $reason);
                $message = (string) ($result['message'] ?? 'سفارش لغو شد.');
            } else {
                $result = food_ticket_day_visit_save($person, $groupId, $date, $actor, true);
                $message = (string) ($result['message'] ?? 'تغییر سلف ثبت شد.');
            }
            $results[] = ['employee_id' => $employeeId, 'name' => $name, 'ok' => true, 'message' => $message];
        } catch (Throwable $e) {
            $results[] = ['employee_id' => $employeeId, 'name' => $name, 'ok' => false, 'message' => food_order_safe_error($e)];
        }
    }
    $succeeded = count(array_filter($results, static fn (array $item): bool => $item['ok']));
    return [
        'ok' => true,
        'total' => count($results),
        'succeeded' => $succeeded,
        'failed' => count($results) - $succeeded,
        'items' => $results,
        'message' => $succeeded === count($results)
            ? 'عملیات برای همهٔ افراد انتخاب‌شده انجام شد.'
            : ($succeeded > 0 ? 'عملیات کامل شد؛ وضعیت تک‌تک افراد را بررسی کنید.' : 'هیچ عملیاتی انجام نشد؛ علت هر مورد در فهرست مشخص است.'),
    ];
}

function food_order_bulk_api_handle(string $route, string $method, array $user): never
{
    try {
        food_order_require_permission($user, 'food.emergency');
        if ($method === 'GET' && $route === 'food-menu/bulk-snapshot') {
            food_order_send_json(food_order_bulk_snapshot((string) ($_GET['date'] ?? ''), (string) ($_GET['meal_type'] ?? 'lunch'), $user));
        }
        if ($method === 'POST' && $route === 'food-menu/bulk-apply') {
            food_order_send_json(food_order_bulk_apply(food_order_api_body(), $user));
        }
        food_order_send_json(['error' => 'مسیر عملیات گروهی پیدا نشد.'], 404);
    } catch (Throwable $e) {
        $status = str_contains($e->getMessage(), 'دسترسی') ? 403 : 400;
        food_order_send_json(['error' => food_order_safe_error($e)], $status);
    }
}

function food_order_menu_api_handle(string $route, array $user): never
{
    $route = trim(rawurldecode($route), '/');
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'POST') {
        require_csrf();
    }
    if (str_starts_with($route, 'food-menu/bulk-')) {
        food_order_bulk_api_handle($route, $method, $user);
    }
    if (str_starts_with($route, 'food-menu/emergency/')) {
        food_order_emergency_handle($route, $method, $user);
    }
    try {
        if (in_array($route, ['food-menu/guest-requests', 'food-menu/guest-request-update', 'food-menu/guest-request-cancel', 'food-menu/guest-request-delete'], true)) {
            if (!food_guest_request_is_reviewer($user)) {
                throw new RuntimeException('دسترسی مدیریت همهٔ درخواست‌های مهمان فقط برای مدیر پشتیبانی و ادمین اصلی مجاز است.');
            }
            if ($method === 'GET' && $route === 'food-menu/guest-requests') {
                food_order_send_json(food_guest_request_list($user, true) + ['allowed' => true]);
            }
            if ($method === 'POST' && $route === 'food-menu/guest-request-update') {
                $body = food_order_api_body();
                food_order_send_json(food_guest_request_update($user, (int) ($body['id'] ?? 0), $body));
            }
            if ($method === 'POST' && $route === 'food-menu/guest-request-cancel') {
                $body = food_order_api_body();
                food_guest_request_cancel($user, (int) ($body['id'] ?? 0));
                food_order_send_json(['ok' => true, 'message' => 'درخواست غذای مهمان لغو شد.']);
            }
            if ($method === 'POST' && $route === 'food-menu/guest-request-delete') {
                $body = food_order_api_body();
                food_order_send_json(food_guest_request_delete($user, (int) ($body['id'] ?? 0)));
            }
        }
        if ($method === 'GET' && $route === 'food-menu/status') {
            if (!food_order_user_can($user, 'food.menu') && !food_order_user_can($user, 'food.order_close')) {
                throw new RuntimeException('دسترسی food.menu یا food.order_close لازم است.');
            }
            food_order_send_json(food_order_status_summary());
        }
        if ($method === 'GET' && $route === 'food-menu/month') {
            if (!food_order_user_can($user, 'food.menu') && !food_order_user_can($user, 'food.order_close')) {
                throw new RuntimeException('دسترسی food.menu یا food.order_close لازم است.');
            }
            $includeCounts = food_order_can_view_order_counts($user);
            food_order_send_json(food_order_month_status((string) ($_GET['month'] ?? ''), $user, $includeCounts, true, food_order_meal_type($_GET['meal_type'] ?? 'lunch')));
        }
        if ($method === 'GET' && $route === 'food-menu/catalog') {
            food_order_require_permission($user, 'food.menu');
            food_order_send_json(['items' => food_order_catalog(true)]);
        }
        if ($method === 'POST' && $route === 'food-menu/catalog') {
            food_order_send_json(food_order_catalog_save(food_order_api_body(), $user));
        }
        if ($method === 'POST' && $route === 'food-menu/day') {
            $result = food_order_save_day(food_order_api_body(), $user);
            if (!empty($result['confirmation_required'])) {
                food_order_send_json($result, 409);
            }
            food_order_send_json($result);
        }
        if ($method === 'GET' && $route === 'food-menu/cancel-requests') {
            food_order_require_cancel_reviewer($user);
            food_order_send_json(['items' => food_order_cancel_request_list()]);
        }
        if ($method === 'POST' && $route === 'food-menu/cancel-request-decide') {
            food_order_require_cancel_reviewer($user);
            $body = food_order_api_body();
            food_order_send_json(food_order_cancel_request_decide(
                (int) ($body['id'] ?? 0),
                (string) ($body['decision'] ?? ''),
                $user,
                (string) ($body['note'] ?? '')
            ));
        }
        if ($method === 'GET' && $route === 'food-menu/emergency-window-state') {
            food_order_require_primary_admin($user);
            food_order_schema_ensure();
            $date = food_order_iso_date((string) ($_GET['date'] ?? ''));
            $mealType = food_order_meal_type($_GET['meal_type'] ?? 'lunch');
            if ($date === null || $date > food_order_today()) {
                throw new RuntimeException('تاریخ روز سفارش اضطراری معتبر نیست.');
            }
            $calendar = db()->prepare('SELECT id FROM food_calendar WHERE food_date = ? AND meal_type = ? LIMIT 1');
            $calendar->execute([$date, $mealType]);
            $calendarId = (int) $calendar->fetchColumn();
            $state = $calendarId > 0 ? food_order_emergency_window_state($calendarId) : ['active' => false, 'expires_at' => '', 'reason' => ''];
            food_order_send_json(['date' => $date, 'meal_type' => $mealType, 'calendar_exists' => $calendarId > 0] + $state);
        }
        if ($method === 'POST' && $route === 'food-menu/emergency-window') {
            $body = food_order_api_body();
            $action = strtolower(trim((string) ($body['action'] ?? 'grant')));
            if ($action === 'revoke') {
                food_order_send_json(food_order_emergency_window_revoke($body, $user));
            }
            if ($action !== 'grant') {
                throw new RuntimeException('عملیات مهلت اضطراری معتبر نیست.');
            }
            food_order_send_json(food_order_emergency_window_grant($body, $user));
        }
        if ($method === 'GET' && $route === 'food-menu/orders') {
            food_order_require_permission($user, 'food.order_close');
            $date = food_order_iso_date((string) ($_GET['date'] ?? ''));
            if ($date === null) {
                throw new RuntimeException('تاریخ روز سفارش معتبر نیست.');
            }
            food_order_send_json(['date' => $date, 'meal_type' => food_order_meal_type($_GET['meal_type'] ?? 'lunch'), 'items' => food_order_manager_orders($date, food_order_meal_type($_GET['meal_type'] ?? 'lunch'))]);
        }
        if ($method === 'GET' && $route === 'food-menu/employee-search') {
            food_order_require_permission($user, 'food.order_close');
            $query = trim((string) ($_GET['q'] ?? ''));
            if ($query !== '' && mb_strlen($query, 'UTF-8') < 2) {
                food_order_send_json(['items' => [], 'requires_query' => true]);
            }
            check_api_rate_limit('food_order_report_employee_search_' . (int) $user['id'], 40, 60);
            food_order_send_json(['items' => food_order_report_employee_search($query)]);
        }
        if ($method === 'GET' && $route === 'food-menu/groups') {
            food_order_require_permission($user, 'food.order_close');
            if (!function_exists('food_ticket_groups_load')) {
                throw new RuntimeException('فهرست گروه‌های غذا در دسترس نیست.');
            }
            $groups = array_values(array_map(static fn (array $group): array => ['id' => (int) $group['id'], 'title' => (string) $group['title']], array_filter(food_ticket_groups_load(true), static fn (array $group): bool => ($group['status'] ?? '') === 'active')));
            food_order_send_json(['items' => $groups]);
        }
        if ($method === 'GET' && $route === 'food-menu/group-orders') {
            food_order_require_permission($user, 'food.order_close');
            $date = food_order_iso_date((string) ($_GET['date'] ?? ''));
            $groupRaw = strtolower(trim((string) ($_GET['group_id'] ?? '')));
            $groupId = $groupRaw === 'all' ? 0 : (ctype_digit($groupRaw) ? (int) $groupRaw : -1);
            if ($date === null || ($groupRaw !== 'all' && $groupId <= 0)) {
                throw new RuntimeException('گروه و تاریخ روز سفارش معتبر نیست.');
            }
            food_order_send_json(food_order_group_orders($groupId, $date, food_order_meal_type($_GET['meal_type'] ?? 'lunch')));
        }
        if ($method === 'GET' && $route === 'food-menu/orders-export') {
            food_order_require_permission($user, 'food.order_close');
            $date = food_order_iso_date((string) ($_GET['date'] ?? ''));
            if ($date === null) {
                throw new RuntimeException('تاریخ روز سفارش معتبر نیست.');
            }
            $fieldsRaw = trim((string) ($_GET['fields'] ?? ''));
            $fields = $fieldsRaw === '' ? [] : explode(',', $fieldsRaw);
            food_order_export_manager_orders($date, $user, food_order_meal_type($_GET['meal_type'] ?? 'lunch'), $fields);
        }
        if ($method === 'GET' && $route === 'food-menu/printed-delivery') {
            food_order_require_permission($user, 'food.order_close');
            food_order_send_json(food_order_printed_delivery_report((string) ($_GET['from'] ?? ''), (string) ($_GET['to'] ?? '')));
        }
        if ($method === 'GET' && $route === 'food-menu/printed-delivery-export') {
            food_order_require_permission($user, 'food.order_close');
            $rawFields = trim((string) ($_GET['fields'] ?? ''));
            $fields = $rawFields === '' ? [] : explode(',', $rawFields);
            food_order_export_printed_delivery_report(
                (string) ($_GET['from'] ?? ''), (string) ($_GET['to'] ?? ''), $user, $fields, (string) ($_GET['view'] ?? 'list')
            );
        }
        if ($method === 'GET' && $route === 'food-menu/statistics') {
            food_order_require_permission($user, 'food.order_close');
            $from = (string) ($_GET['from'] ?? '');
            $to = (string) ($_GET['to'] ?? '');
            $mode = strtolower(trim((string) ($_GET['mode'] ?? 'food')));
            if (!in_array($mode, ['food', 'employee', 'selected'], true)) {
                throw new RuntimeException('نوع آمار سفارش معتبر نیست.');
            }
            $fromIso = food_order_iso_date($from);
            $toIso = food_order_iso_date($to);
            if ($fromIso === null || $toIso === null || $fromIso > $toIso) {
                throw new RuntimeException('بازهٔ تاریخ آمار معتبر نیست.');
            }
            $mealType = food_order_meal_type($_GET['meal_type'] ?? 'lunch');
            $items = $mode === 'selected'
                ? food_order_selected_orders_range($fromIso, $toIso, explode(',', (string) ($_GET['employee_ids'] ?? '')), $mealType)
                : food_order_statistics_range($fromIso, $toIso, $mode, $mealType);
            food_order_send_json([
                'from' => $fromIso,
                'to' => $toIso,
                'mode' => $mode,
                'items' => $items,
            ]);
        }
        if ($method === 'GET' && $route === 'food-menu/export') {
            food_order_require_permission($user, 'food.order_close');
            $mode = strtolower(trim((string) ($_GET['mode'] ?? 'food')));
            if ($mode === 'selected') {
                food_order_export_selected_orders_range(
                    (string) ($_GET['from'] ?? ''),
                    (string) ($_GET['to'] ?? ''),
                    explode(',', (string) ($_GET['employee_ids'] ?? '')),
                    $user,
                    food_order_meal_type($_GET['meal_type'] ?? 'lunch')
                );
            }
            food_order_export_statistics_range(
                (string) ($_GET['from'] ?? ''),
                (string) ($_GET['to'] ?? ''),
                $user,
                $mode,
                food_order_meal_type($_GET['meal_type'] ?? 'lunch')
            );
        }
        food_order_send_json(['error' => 'مسیر API برنامه غذایی پیدا نشد.'], 404);
    } catch (Throwable $e) {
        $status = str_contains($e->getMessage(), 'دسترسی') || str_contains($e->getMessage(), 'فقط ادمین اصلی') ? 403 : 400;
        food_order_send_json(['error' => food_order_safe_error($e)], $status);
    }
}

function food_order_render_page(array $user): never
{
    food_order_schema_ensure();
    $profile = food_order_user_row((int) ($user['id'] ?? 0)) ?? $user;
    $today = food_order_today();
    $todayJalali = food_order_jalali_label($today);
    $selfNational = food_order_digits($profile['national_code'] ?? '');
    // کاربر محلی (پرسنل غذا) کد ملی را از فهرست پرسنل غذا دارد و برای سفارش خودش نیازی به ثبت دوباره نیست.
    $hasNational = lac_is_local_role($user) || strlen($selfNational) === 10;
    $nationalIssue = $selfNational === ''
        ? 'کد ملی شما در سامانه ثبت نیست؛ ثبت سفارش برای خودتان غیرفعال است. برای اصلاح اطلاعات با منابع انسانی هماهنگ کنید.'
        : 'کد ملی ثبت‌شدهٔ شما معتبر نیست؛ ثبت سفارش برای خودتان غیرفعال است. برای اصلاح اطلاعات با منابع انسانی هماهنگ کنید.';
    $canProxy = true; // هر کاربر وارد‌شده می‌تواند برای دیگری سفارش ثبت کند (با کد ملی فرد)
    render_header('سفارش غذا', $user);
    echo '<link rel="stylesheet" href="assets/food-order.css?v=17">';
    echo '<section class="food-order-page"><header class="page-heading"><div><span class="eyebrow">برنامه غذایی سازمان</span><h1>سفارش غذا</h1></div><a class="button secondary" href="index.php">بازگشت به داشبورد</a></header>';
    if (!$hasNational) {
        echo '<div class="alert info" role="status">' . e($nationalIssue) . '</div>';
    }
    echo '<div id="food-order-app" data-api="index.php?page=food-order&amp;food_api=" data-csrf="' . e(csrf_token()) . '" data-self-id="' . (int) ($user['id'] ?? 0) . '" data-self-name="' . e(food_order_full_name($profile)) . '" data-today="' . e($today) . '" data-guest-allowed="' . (food_guest_request_allowed($user) && food_guest_schema_ready() ? '1' : '0') . '" data-today-jalali="' . e($todayJalali) . '" data-has-national="' . ($hasNational ? '1' : '0') . '" data-can-proxy="' . ($canProxy ? '1' : '0') . '"><div class="food-order-loading card">در حال بارگذاری تقویم و سفارش‌های شما…</div></div>';
    echo '</section>';
    if (food_guest_request_allowed($user) && !food_guest_schema_ready()) {
        $missingItems = food_guest_schema_missing();
        echo '<section class="food-guest-page"><div class="alert danger" role="alert"><b>بخش سفارش مهمان فعال نیست.</b> موارد ناقص: ' . e(implode('، ', $missingItems)) . '. migration را روی همان دیتابیسی اجرا کنید که سامانه به آن وصل است.</div></section>';
    }
    if (food_guest_request_allowed($user) && food_guest_schema_ready()) {
        echo '<div hidden id="food-guest-app" data-api="index.php?page=food-order&amp;food_api=" data-csrf="' . e(csrf_token()) . '" data-self-name="' . e(food_order_full_name($profile)) . '"></div>';
    }
    echo '<script defer src="assets/food-order-calendar.js?v=1"></script><script defer src="assets/food-order.js?v=21"></script><script defer src="assets/food-order-guest.js?v=5"></script>';
    render_footer();
    exit;
}
