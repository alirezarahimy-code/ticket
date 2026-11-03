<?php
declare(strict_types=1);

/**
 * lib/tickets-sla.php — شمارهٔ درخواست، تقویم شمسی و SLA/OLA — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: ticket_number_* (کش، پیشوند، اسکیما، تولید شماره) / jalali_* و تبدیل تاریخ / find_or_create_department / سیاست‌های OLA و مهلت‌ها / ساعات کاری / گذار SLA و OLA / record_asset_history / برچسب وضعیت و اولویت.
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
}

function &ticket_number_cache_store(): array
{
    static $cache = [];
    return $cache;
}

function ticket_number_cache_set(int $id, string $value): void
{
    $cache = &ticket_number_cache_store();
    $cache[$id] = $value;
}

function ticket_number(int $id): string
{
    $cache = &ticket_number_cache_store();
    if ($id <= 0) {
        return '';
    }
    if (!array_key_exists($id, $cache)) {
        $cache[$id] = '';
        try {
            $query = db()->prepare('SELECT ticket_no FROM tickets WHERE id = ? LIMIT 1');
            $query->execute([$id]);
            $cache[$id] = trim((string) ($query->fetchColumn() ?: ''));
        } catch (Throwable $exception) {
            $cache[$id] = '';
        }
    }
    if ($cache[$id] !== '') {
        return $cache[$id];
    }
    return 'SBU1-86-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

function ticket_number_prefix(string $serviceGroup): string
{
    if ($serviceGroup === 'it') {
        return 'IT';
    }
    if ($serviceGroup === 'support') {
        return 'SUP';
    }
    return 'TK';
}

/**
 * آماده‌سازی جدول شماره‌گذاری درخواست — بدون DDL غیرضروری.
 *
 * ⚠️ درس گرفته‌شده (خطای «There is no active transaction»):
 * هر دستور DDL در MySQL یک COMMIT ضمنی می‌زند. قبلاً این تابع در هر بار «شماره‌گذاری»
 * یک CREATE TABLE IF NOT EXISTS اجرا می‌کرد و چون ticket_number_next() داخل تراکنش
 * ثبت درخواست صدا زده می‌شود، تراکنش همان‌جا بسته می‌شد؛ در پایان، db()->commit()
 * با خطای «There is no active transaction» سر می‌خورد — هرچند درخواست ثبت شده بود.
 *
 * حالا: اول با SELECT وجود جدول/ستون بررسی می‌شود (بدون COMMIT ضمنی) و DDL فقط اگر
 * واقعاً لازم باشد و «تراکنشی باز نباشد» اجرا می‌شود.
 */
function ticket_number_ensure_schema(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    ticket_number_ensure_schema_run();
    ticket_hot_schema_ensure();
    $ready = true;
}

/**
 * ستون‌ها و مقدارهای ENUM که صفحات پرترافیک (صف کاری، داشبورد، جزئیات درخواست، ورود) به آن‌ها وابسته‌اند.
 * مهاجرت جاافتاده صفحه را نمی‌خواباند؛ هر ALTER جدا و بی‌صدا است.
 */
function ticket_hot_schema_ensure(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;
    try {
        $ticketAdds = [
            'ticket_no' => 'ALTER TABLE tickets ADD COLUMN ticket_no VARCHAR(40) NULL',
            'cancel_requested_at' => 'ALTER TABLE tickets ADD COLUMN cancel_requested_at DATETIME NULL',
            'cancel_requested_by' => 'ALTER TABLE tickets ADD COLUMN cancel_requested_by INT UNSIGNED NULL',
            'cancel_request_reason' => 'ALTER TABLE tickets ADD COLUMN cancel_request_reason TEXT NULL',
            'requesting_unit_id' => 'ALTER TABLE tickets ADD COLUMN requesting_unit_id INT UNSIGNED NULL',
            'sla_paused_at' => 'ALTER TABLE tickets ADD COLUMN sla_paused_at DATETIME NULL',
            'sla_pause_minutes' => 'ALTER TABLE tickets ADD COLUMN sla_pause_minutes INT UNSIGNED NOT NULL DEFAULT 0',
            'ola_policy_id' => 'ALTER TABLE tickets ADD COLUMN ola_policy_id INT UNSIGNED NULL',
            'ola_response_due_at' => 'ALTER TABLE tickets ADD COLUMN ola_response_due_at DATETIME NULL',
            'ola_due_at' => 'ALTER TABLE tickets ADD COLUMN ola_due_at DATETIME NULL',
            'ola_escalation_1_at' => 'ALTER TABLE tickets ADD COLUMN ola_escalation_1_at DATETIME NULL',
            'ola_escalation_2_at' => 'ALTER TABLE tickets ADD COLUMN ola_escalation_2_at DATETIME NULL',
            'first_response_at' => 'ALTER TABLE tickets ADD COLUMN first_response_at DATETIME NULL',
            'resolved_at' => 'ALTER TABLE tickets ADD COLUMN resolved_at DATETIME NULL',
            'closed_at' => 'ALTER TABLE tickets ADD COLUMN closed_at DATETIME NULL',
            'assigned_at' => 'ALTER TABLE tickets ADD COLUMN assigned_at DATETIME NULL',
            'supervisor_id' => 'ALTER TABLE tickets ADD COLUMN supervisor_id INT UNSIGNED NULL',
            'supervisor_approved_at' => 'ALTER TABLE tickets ADD COLUMN supervisor_approved_at DATETIME NULL',
            'supervisor_note' => 'ALTER TABLE tickets ADD COLUMN supervisor_note TEXT NULL',
            'support_location' => 'ALTER TABLE tickets ADD COLUMN support_location VARCHAR(255) NULL',
            'support_equipment' => 'ALTER TABLE tickets ADD COLUMN support_equipment TEXT NULL',
            'handling_unit_id' => 'ALTER TABLE tickets ADD COLUMN handling_unit_id INT UNSIGNED NULL',
            'service_id' => 'ALTER TABLE tickets ADD COLUMN service_id INT UNSIGNED NULL',
        ];
        foreach ($ticketAdds as $column => $ddl) {
            db_add_missing_column('tickets', $column, $ddl);
        }
        db_ensure_enum_values('tickets', 'status', ['cancelled'], 'manager_review');
        $userAdds = [
            'must_change_password' => 'ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0',
            'first_name' => 'ALTER TABLE users ADD COLUMN first_name VARCHAR(100) NULL',
            'last_name' => 'ALTER TABLE users ADD COLUMN last_name VARCHAR(100) NULL',
            'employee_number' => 'ALTER TABLE users ADD COLUMN employee_number VARCHAR(100) NULL',
            'national_code' => 'ALTER TABLE users ADD COLUMN national_code VARCHAR(30) NULL',
            'is_primary_admin' => 'ALTER TABLE users ADD COLUMN is_primary_admin TINYINT(1) NOT NULL DEFAULT 0',
            'is_it_agent' => 'ALTER TABLE users ADD COLUMN is_it_agent TINYINT(1) NOT NULL DEFAULT 0',
            'handling_unit_id' => 'ALTER TABLE users ADD COLUMN handling_unit_id INT UNSIGNED NULL',
            'org_unit_id' => 'ALTER TABLE users ADD COLUMN org_unit_id INT UNSIGNED NULL',
            'manager_user_id' => 'ALTER TABLE users ADD COLUMN manager_user_id INT UNSIGNED NULL',
            'requesting_unit_id' => 'ALTER TABLE users ADD COLUMN requesting_unit_id INT UNSIGNED NULL',
            'auth_source' => "ALTER TABLE users ADD COLUMN auth_source ENUM('local','ldap','food') NOT NULL DEFAULT 'local'",
        ];
        foreach ($userAdds as $column => $ddl) {
            db_add_missing_column('users', $column, $ddl);
        }
        db_ensure_enum_values('users', 'role', ['local_user', 'guard', 'primary_admin', 'support_manager'], 'user');
        db_ensure_enum_values('users', 'auth_source', ['food'], 'local');
        if (function_exists('org_ensure_schema')) {
            org_ensure_schema();
        }
        if (function_exists('org_ticket_schema_ensure')) {
            org_ticket_schema_ensure();
        }
        if (function_exists('lac_ensure_schema')) {
            lac_ensure_schema();
        }
    } catch (Throwable) {
    }
}

function ticket_number_ensure_schema_run(): void
{
    $inTransaction = function_exists('db_in_transaction') ? db_in_transaction() : false;

    // ۱) جدول دنباله — با SELECT بررسی می‌شود (نه DDL)
    $tableExists = false;
    try {
        db()->query('SELECT 1 FROM ticket_number_seq LIMIT 1');
        $tableExists = true;
    } catch (Throwable) {
        $tableExists = false;
    }

    // ۲) ستون ticket_no در tickets
    $columnExists = true;
    try {
        $columns = db()->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets'")->fetchAll(PDO::FETCH_COLUMN);
        $columnExists = in_array('ticket_no', array_map('strtolower', $columns), true);
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'ticket_number_schema', $exception->getMessage());
        }
        $columnExists = true; // اگر نتوانستیم بررسی کنیم، DDL هم نمی‌زنیم
    }

    if ($tableExists && $columnExists) {
        return;
    }
    if ($inTransaction) {
        // DDL داخل تراکنش = بسته‌شدن بی‌صدا. به‌جای آن، بعد از پایان تراکنش تلاش می‌کنیم.
        if (function_exists('system_log')) {
            system_log('warning', 'ticket_number_schema', 'نیاز به DDL در میانهٔ تراکنش بود؛ به بعد موکول شد.', ['in_transaction' => true]);
        }
        register_shutdown_function(static function (): void {
            try {
                ticket_number_ensure_schema_run();
            } catch (Throwable) {
            }
        });
        return;
    }

    try {
        if (!$tableExists) {
            db()->exec('CREATE TABLE IF NOT EXISTS ticket_number_seq (prefix VARCHAR(20) NOT NULL PRIMARY KEY, seq INT UNSIGNED NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }
        if (!$columnExists) {
            db()->exec('ALTER TABLE tickets ADD COLUMN ticket_no VARCHAR(40) NULL AFTER id');
        }
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'ticket_number_schema', $exception->getMessage());
        }
    }
}

function ticket_number_next(string $prefix): string
{
    ticket_number_ensure_schema();
    $prefix = strtoupper(substr(trim($prefix) !== '' ? trim($prefix) : 'TK', 0, 12));
    $ownsTransaction = !db()->inTransaction();
    if ($ownsTransaction) {
        db()->beginTransaction();
    }
    try {
        // Lock the prefix row explicitly and read its value in this transaction. This
        // avoids relying on connection-scoped LAST_INSERT_ID() after concurrent upserts.
        $ensure = db()->prepare('INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, 0) ON DUPLICATE KEY UPDATE prefix = VALUES(prefix)');
        $ensure->execute([$prefix]);
        $read = db()->prepare('SELECT seq FROM ticket_number_seq WHERE prefix = ? FOR UPDATE');
        $read->execute([$prefix]);
        $currentSequence = (int) ($read->fetchColumn() ?: 0);

        // Heal a stale sequence left by legacy/manual ticket numbers before allocating
        // the next number; the ticket_no index keeps this prefix-scoped lookup bounded.
        $pattern = '^' . preg_quote($prefix, '/') . '-[0-9]+$';
        $maxQuery = db()->prepare('SELECT MAX(CAST(SUBSTRING_INDEX(ticket_no, "-", -1) AS UNSIGNED)) FROM tickets WHERE ticket_no LIKE ? AND ticket_no REGEXP ?');
        $maxQuery->execute([$prefix . '-%', $pattern]);
        $existingMaximum = (int) ($maxQuery->fetchColumn() ?: 0);
        $sequence = max($currentSequence, $existingMaximum) + 1;
        $write = db()->prepare('UPDATE ticket_number_seq SET seq = ? WHERE prefix = ?');
        $write->execute([$sequence, $prefix]);
        if ($ownsTransaction) {
            db()->commit();
        }
        return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    } catch (Throwable $exception) {
        if ($ownsTransaction && db()->inTransaction()) {
            db()->rollBack();
        }
        throw $exception;
    }
}

function ticket_number_peek(string $prefix): string
{
    ticket_number_ensure_schema();
    $prefix = strtoupper(substr(trim($prefix) !== '' ? trim($prefix) : 'TK', 0, 12));
    try {
        $query = db()->prepare('SELECT seq FROM ticket_number_seq WHERE prefix = ? LIMIT 1');
        $query->execute([$prefix]);
        $sequence = (int) ($query->fetchColumn() ?: 0) + 1;
    } catch (Throwable $exception) {
        $sequence = 1;
    }
    return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
}

/**
 * پرکردن شمارهٔ درخواست‌های قدیمی که ticket_no ندارند.
 *
 * ⚠️ این تابع در ابتدای هر درخواست صدا زده می‌شد و هر بار یک SELECT روی کل جدول
 * tickets + یک DDL (CREATE TABLE IF NOT EXISTS) اجرا می‌کرد؛ روی سرور پرتردد
 * یکی از دلایل کندی بارگذاری صفحات بود. حالا نتیجهٔ «چیزی برای اصلاح نیست» در
 * تنظیمات ثبت می‌شود و تا وقتی درخواستی بدون شماره ساخته نشود، دوباره اسکن نمی‌شود.
 *
 * @param bool $force true = اسکن دوباره حتی اگر نشانگر ثبت شده باشد (برای ابزار تعمیر)
 */
function ticket_number_autofill(bool $force = false): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    if (!$force) {
        try {
            if (setting('ticket_autofill_done') === '1') {
                return;
            }
        } catch (Throwable) {
        }
    }
    try {
        ticket_number_ensure_schema();
        $marker = 'ticket_autofill_done';
        $rows = db()->query("SELECT id, service_group FROM tickets WHERE ticket_no IS NULL OR ticket_no = '' ORDER BY id ASC")->fetchAll();
        if (!$rows) {
            // دفعهٔ بعد دوباره کل جدول اسکن نشود (این خط، هزینهٔ هر بارگذاری صفحه را کم می‌کند)
            try {
                save_setting('ticket_autofill_done', '1');
            } catch (Throwable) {
            }
            return;
        }
        $sequence = [];
        $update = db()->prepare('UPDATE tickets SET ticket_no = ? WHERE id = ?');
        foreach ($rows as $row) {
            $prefix = ticket_number_prefix((string) $row['service_group']);
            $sequence[$prefix] = ($sequence[$prefix] ?? 0) + 1;
            $update->execute([$prefix . '-' . str_pad((string) $sequence[$prefix], 5, '0', STR_PAD_LEFT), (int) $row['id']]);
        }
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'ticket_number_autofill', $exception->getMessage());
        }
    }
}

function is_it_agent(array $user): bool
{
    return (int) ($user['is_active'] ?? 0) === 1
        && ($user['role'] === 'agent' || (int) ($user['is_it_agent'] ?? 0) === 1);
}

function jalali_to_gregorian(int $jy, int $jm, int $jd): array
{
    $jy += 1595;
    $days = -355668 + (365 * $jy) + ((int) ($jy / 33) * 8) + (int) ((($jy % 33) + 3) / 4) + $jd;
    $days += $jm < 7 ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186;
    $gy = 400 * (int) ($days / 146097);
    $days %= 146097;
    if ($days > 36524) {
        $gy += 100 * (int) (--$days / 36524);
        $days %= 36524;
        if ($days >= 365) {
            $days++;
        }
    }
    $gy += 4 * (int) ($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $gy += (int) (($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $gd = $days + 1;
    $leap = (($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0);
    $monthDays = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $gm = 1;
    while ($gm <= 12 && $gd > $monthDays[$gm]) {
        $gd -= $monthDays[$gm++];
    }
    return [$gy, $gm, $gd];
}

function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $gDays = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $jDays = [0, 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    $gy -= 1600;
    $gm -= 1;
    $gd -= 1;
    $days = 365 * $gy + (int) (($gy + 3) / 4) - (int) (($gy + 99) / 100) + (int) (($gy + 399) / 400);
    for ($i = 0; $i < $gm; $i++) {
        $days += $gDays[$i + 1];
    }
    if ($gm > 1 && (($gy + 1600) % 4 === 0 && (($gy + 1600) % 100 !== 0 || ($gy + 1600) % 400 === 0))) {
        $days++;
    }
    $days += $gd - 79;
    $jy = 979 + (33 * (int) ($days / 12053));
    $days %= 12053;
    $jy += 4 * (int) ($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += (int) (($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $jm = $days < 186 ? 1 + (int) ($days / 31) : 7 + (int) (($days - 186) / 30);
    $jd = 1 + ($days < 186 ? $days % 31 : ($days - 186) % 30);
    return [$jy, $jm, $jd];
}

function jalali_date(string $date, bool $withTime = true): string
{
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '';
    }
    [$jy, $jm, $jd] = gregorian_to_jalali((int) date('Y', $timestamp), (int) date('n', $timestamp), (int) date('j', $timestamp));
    $result = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    return $withTime ? $result . ' ' . date('H:i', $timestamp) : $result;
}

function jalali_input_to_gregorian(string $value, bool $endOfDay = false): ?string
{
    $value = trim(str_replace('-', '/', strtr($value, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'])));
    if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $matches)) {
        return null;
    }
    $jy = (int) $matches[1];
    $jm = (int) $matches[2];
    $jd = (int) $matches[3];
    $maxDay = $jm <= 6 ? 31 : ($jm <= 11 ? 30 : 30);
    if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > $maxDay) {
        return null;
    }
    [$gy, $gm, $gd] = jalali_to_gregorian($jy, $jm, $jd);
    [$roundTripJy, $roundTripJm, $roundTripJd] = gregorian_to_jalali($gy, $gm, $gd);
    if ($roundTripJy !== $jy || $roundTripJm !== $jm || $roundTripJd !== $jd) {
        return null;
    }
    return sprintf('%04d-%02d-%02d %s', $gy, $gm, $gd, $endOfDay ? '23:59:59' : '00:00:00');
}

function find_or_create_department(?string $name): ?int
{
    $name = trim((string) $name);
    if ($name === '') {
        return null;
    }
    $query = db()->prepare('SELECT id FROM departments WHERE name = ? LIMIT 1');
    $query->execute([$name]);
    $id = $query->fetchColumn();
    if ($id !== false) {
        return (int) $id;
    }
    $insert = db()->prepare('INSERT INTO departments (name) VALUES (?)');
    $insert->execute([$name]);
    return (int) db()->lastInsertId();
}

function persian_date(string $date): string
{
    return jalali_date($date);
}

function priority_sla_minutes(string $priority): int
{
    return ['critical' => 240, 'urgent' => 2880, 'normal' => 4320][$priority] ?? 4320;
}

function ensure_default_ola_policies(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    try {
        if (!(bool) db()->query("SHOW TABLES LIKE 'ola_policies'")->fetchColumn() || (int) db()->query('SELECT COUNT(*) FROM ola_policies')->fetchColumn() > 0) {
            return;
        }
        $insert = db()->prepare('INSERT INTO ola_policies (name, department_id, priority, response_minutes, resolution_minutes, escalation_1_minutes, escalation_2_minutes) VALUES (?, NULL, ?, ?, ?, ?, ?)');
        $defaults = [
            ['OLA حیاتی سراسری', 'critical', 30, 240, 240, 480],
            ['OLA فوری سراسری', 'urgent', 120, 2880, 2880, 4320],
            ['OLA عادی سراسری', 'normal', 240, 4320, 4320, 5760],
        ];
        foreach ($defaults as $default) {
            $insert->execute($default);
        }
    } catch (Throwable) {
        // OLA is optional until the 1.8 migration is applied.
    }
}

function ola_policy_for(?int $departmentId, string $priority): ?array
{
    ensure_default_ola_policies();
    try {
        $query = db()->prepare('SELECT * FROM ola_policies WHERE is_active = 1 AND priority = ? AND (department_id = ? OR department_id IS NULL) ORDER BY department_id IS NULL, id LIMIT 1');
        $query->execute([$priority, $departmentId ?: 0]);
        return $query->fetch() ?: null;
    } catch (Throwable) {
        return null;
    }
}

function ola_deadlines(?int $departmentId, string $priority, string $createdAt): array
{
    $policy = ola_policy_for($departmentId, $priority);
    if (!$policy) {
        return ['policy_id' => null, 'response_due_at' => null, 'due_at' => null, 'escalation_1_at' => null, 'escalation_2_at' => null];
    }
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $created = new DateTimeImmutable($createdAt, $zone);
    $deadline = static fn (string $minutes): string => business_due_at((int) $minutes, $created)->format('Y-m-d H:i:s');
    return [
        'policy_id' => (int) $policy['id'],
        'response_due_at' => $deadline((string) $policy['response_minutes']),
        'due_at' => $deadline((string) $policy['resolution_minutes']),
        'escalation_1_at' => $deadline((string) $policy['escalation_1_minutes']),
        'escalation_2_at' => $deadline((string) $policy['escalation_2_minutes']),
    ];
}

function next_business_start(DateTimeImmutable $date): DateTimeImmutable
{
    $date = $date->setTimezone(new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran')));
    while (in_array((int) $date->format('N'), [4, 5], true) || is_sla_holiday($date)) {
        $date = $date->modify('+1 day')->setTime(8, 0);
    }
    if ((int) $date->format('H') < 8) {
        return $date->setTime(8, 0);
    }
    if ((int) $date->format('H') >= 16) {
        return next_business_start($date->modify('+1 day')->setTime(8, 0));
    }
    return $date;
}

function business_due_at(int $minutes, ?DateTimeImmutable $start = null): DateTimeImmutable
{
    $cursor = next_business_start($start ?: new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))));
    $remaining = max(0, $minutes);
    while ($remaining > 0) {
        $end = $cursor->setTime(16, 0);
        $available = (int) (($end->getTimestamp() - $cursor->getTimestamp()) / 60);
        if ($remaining <= $available) {
            return $cursor->modify('+' . $remaining . ' minutes');
        }
        $remaining -= $available;
        $cursor = next_business_start($cursor->modify('+1 day')->setTime(8, 0));
    }
    return $cursor;
}

function business_elapsed_minutes(DateTimeImmutable $start, DateTimeImmutable $end): int
{
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $cursor = $start->setTimezone($zone);
    $finish = $end->setTimezone($zone);
    if ($finish <= $cursor) {
        return 0;
    }
    $minutes = 0;
    while ($cursor->format('Y-m-d') <= $finish->format('Y-m-d')) {
        if (!in_array((int) $cursor->format('N'), [4, 5], true) && !is_sla_holiday($cursor)) {
            $dayStart = $cursor->setTime(8, 0);
            $dayEnd = $cursor->setTime(16, 0);
            $from = $cursor > $dayStart ? $cursor : $dayStart;
            $to = $finish < $dayEnd ? $finish : $dayEnd;
            if ($to > $from) {
                $minutes += (int) floor(($to->getTimestamp() - $from->getTimestamp()) / 60);
            }
        }
        $cursor = $cursor->modify('+1 day')->setTime(8, 0);
    }
    return $minutes;
}

/** Base of the current SLA cycle, not necessarily the original creation time. */
function ticket_sla_cycle_start(array $ticket): string
{
    $createdAt = (string) ($ticket['created_at'] ?? 'now');
    if (empty($ticket['id'])) {
        return $createdAt;
    }
    try {
        $query = db()->prepare('SELECT created_at FROM ticket_events WHERE ticket_id = ? AND (event_type IN ("reopened_by_user", "supervisor_reopened", "supervisor_rework") OR (from_status = "resolved" AND to_status IN ("assigned", "in_progress") AND event_type IN ("status_changed", "assigned", "bulk_status_changed"))) ORDER BY id DESC LIMIT 1');
        $query->execute([(int) $ticket['id']]);
        $reopenedAt = $query->fetchColumn();
        return $reopenedAt !== false && $reopenedAt !== null && $reopenedAt !== '' ? (string) $reopenedAt : $createdAt;
    } catch (Throwable) {
        // Older installations may lack history; keep the existing fallback.
        return $createdAt;
    }
}

function ticket_sla_transition(array $ticket, string $newStatus, string $priority): array
{
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $oldStatus = (string) ($ticket['status'] ?? 'new');
    if ($oldStatus === 'resolved' && in_array($newStatus, ['assigned', 'in_progress'], true)) {
        return [business_due_at(priority_sla_minutes($priority))->format('Y-m-d H:i:s'), null, 0];
    }
    $pauseMinutes = (int) ($ticket['sla_pause_minutes'] ?? 0);
    $pauseAt = !empty($ticket['sla_paused_at']) ? new DateTimeImmutable((string) $ticket['sla_paused_at'], $zone) : null;
    $wasWaiting = ticket_is_waiting_status($oldStatus);
    $isWaiting = ticket_is_waiting_status($newStatus);
    $newPauseMinutes = 0;
    if ($wasWaiting && !$isWaiting && $pauseAt) {
        $newPauseMinutes = business_elapsed_minutes($pauseAt, new DateTimeImmutable('now', $zone));
        $pauseMinutes += $newPauseMinutes;
        $pauseAt = null;
    } elseif (!$wasWaiting && $isWaiting) {
        $pauseAt = new DateTimeImmutable('now', $zone);
    }
    if ($priority === (string) ($ticket['priority'] ?? '') && !empty($ticket['due_at'])) {
        // A reopen may already have reset this deadline; never recalculate it from
        // the original creation time just because a message was sent.
        $dueAt = new DateTimeImmutable((string) $ticket['due_at'], $zone);
        if ($newPauseMinutes > 0) {
            $dueAt = business_due_at($newPauseMinutes, $dueAt);
        }
    } else {
        $cycleStart = new DateTimeImmutable(ticket_sla_cycle_start($ticket), $zone);
        $baseDue = business_due_at(priority_sla_minutes($priority), $cycleStart);
        $dueAt = $pauseMinutes > 0 ? business_due_at($pauseMinutes, $baseDue) : $baseDue;
    }
    return [$dueAt->format('Y-m-d H:i:s'), $pauseAt?->format('Y-m-d H:i:s'), $pauseMinutes];
}

/**
 * بازتنظیم/تمدید OLA هنگام تغییر اولویت یا خروج از waiting_user.
 * مهلت‌ها هنگام انتظار کاربر در cron متوقف می‌شوند و پس از پاسخ، زمان مکث به موعدها افزوده می‌شود.
 */
function ticket_ola_transition(array $ticket, string $newStatus, string $priority): array
{
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $oldStatus = (string) ($ticket['status'] ?? 'new');
    $departmentId = (int) ($ticket['department_id'] ?? 0) ?: null;
    $oldPriority = (string) ($ticket['priority'] ?? 'normal');
    if ($oldStatus === 'resolved' && in_array($newStatus, ['assigned', 'in_progress'], true)) {
        return ola_deadlines($departmentId, $priority, (new DateTimeImmutable('now', $zone))->format('Y-m-d H:i:s'));
    }
    $recalculate = $priority !== $oldPriority
        || empty($ticket['ola_policy_id'])
        || (!array_key_exists('ola_due_at', $ticket));

    if ($recalculate) {
        $deadlines = ola_deadlines($departmentId, $priority, ticket_sla_cycle_start($ticket));
    } else {
        $deadlines = [
            'policy_id' => (int) $ticket['ola_policy_id'],
            'response_due_at' => $ticket['ola_response_due_at'] ?? null,
            'due_at' => $ticket['ola_due_at'] ?? null,
            'escalation_1_at' => $ticket['ola_escalation_1_at'] ?? null,
            'escalation_2_at' => $ticket['ola_escalation_2_at'] ?? null,
        ];
    }

    $pauseMinutes = 0;
    if (ticket_is_waiting_status($oldStatus) && !ticket_is_waiting_status($newStatus) && !empty($ticket['sla_paused_at'])) {
        $pauseAt = new DateTimeImmutable((string) $ticket['sla_paused_at'], $zone);
        $pauseMinutes = business_elapsed_minutes($pauseAt, new DateTimeImmutable('now', $zone));
    }
    if ($recalculate) {
        $pauseMinutes += (int) ($ticket['sla_pause_minutes'] ?? 0);
    }
    if ($pauseMinutes > 0) {
        foreach (['response_due_at', 'due_at', 'escalation_1_at', 'escalation_2_at'] as $key) {
            if (empty($deadlines[$key])) {
                continue;
            }
            $deadline = new DateTimeImmutable((string) $deadlines[$key], $zone);
            $deadlines[$key] = business_due_at($pauseMinutes, $deadline)->format('Y-m-d H:i:s');
        }
    }
    return $deadlines;
}

function record_asset_history(int $assetId, string $eventType, string $title, ?int $userId = null, ?int $ticketId = null, string $details = '', array $before = [], array $after = []): void
{
    if ($assetId <= 0) {
        return;
    }
    $query = db()->prepare('INSERT INTO asset_history_events (asset_id, ticket_id, user_id, event_type, title, details, before_json, after_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $query->execute([$assetId, $ticketId, $userId, $eventType, $title, $details, json_encode($before, JSON_UNESCAPED_UNICODE), json_encode($after, JSON_UNESCAPED_UNICODE)]);
}

function status_label(string $status): string
{
    return ['new' => 'جدید', 'manager_review' => 'در انتظار بررسی مدیر واحد', 'assigned' => 'ارجاع‌شده به کارشناس', 'in_progress' => 'در حال انجام', 'waiting_user' => 'در انتظار پاسخ کاربر', 'open' => 'در حال بررسی', 'pending' => 'در انتظار پاسخ', 'resolved' => 'حل‌شده', 'closed' => 'بسته‌شده', 'cancelled' => 'لغوشده به درخواست ثبت‌کننده'][$status] ?? $status;
}

function priority_label(string $priority): string
{
    return ['normal' => 'عادی', 'urgent' => 'فوری', 'critical' => 'حیاتی'][$priority] ?? $priority;
}

function ticket_type_label(string $type): string
{
    return ['incident' => 'رخداد', 'request' => 'درخواست خدمت', 'problem' => 'مشکل ریشه‌ای', 'change' => 'تغییر'][$type] ?? $type;
}

