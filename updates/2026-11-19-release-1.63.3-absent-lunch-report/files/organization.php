<?php
declare(strict_types=1);

function org_ensure_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
    db()->exec(
        'CREATE TABLE IF NOT EXISTS org_units (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(190) NOT NULL,
            code VARCHAR(60) NULL,
            parent_id INT UNSIGNED NULL,
            unit_type VARCHAR(20) NOT NULL DEFAULT "department",
            manager_user_id INT UNSIGNED NULL,
            is_primary_admin TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_org_parent (parent_id),
            INDEX idx_org_type (unit_type),
            INDEX idx_org_manager (manager_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS org_unit_managers (
            unit_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            is_primary TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (unit_id, user_id),
            INDEX idx_org_mgr_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    foreach (['org_unit_id INT UNSIGNED NULL', 'manager_user_id INT UNSIGNED NULL'] as $column) {
        try {
            db()->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS ' . $column);
        } catch (Throwable $ignored) {
            try {
                db()->exec('ALTER TABLE users ADD COLUMN ' . $column);
            } catch (Throwable $alsoIgnored) {
            }
        }
    }
    // ۱.۶۲.۱۰: جایگاه نمایش واحدهای مستقل (نمایندگی) کنار مدیرعامل — right یا left.
    try {
        db()->exec('ALTER TABLE org_units ADD COLUMN IF NOT EXISTS chart_side VARCHAR(10) NULL');
    } catch (Throwable $ignored) {
        try {
            db()->exec('ALTER TABLE org_units ADD COLUMN chart_side VARCHAR(10) NULL');
        } catch (Throwable $alsoIgnored) {
        }
    }
    $hasCeo = (int) db()->query('SELECT COUNT(*) FROM org_units WHERE unit_type = "ceo"')->fetchColumn();
    if ($hasCeo === 0) {
        db()->prepare('INSERT INTO org_units (name, code, parent_id, unit_type, is_active) VALUES (?, ?, NULL, "ceo", 1)')
            ->execute(['مدیرعامل', 'CEO']);
    }
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'org_schema', $exception->getMessage());
        }
    }
}


/**
 * ستون مستقل «معاونت درخواست‌کننده» روی درخواست‌ها (جدا از department_id که مسیر رسیدگی است).
 */
function org_ticket_schema_ensure(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $columns = db()->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('requesting_unit_id', array_map('strtolower', $columns), true)) {
            db()->exec('ALTER TABLE tickets ADD COLUMN requesting_unit_id INT UNSIGNED NULL');
            db()->exec('ALTER TABLE tickets ADD INDEX idx_tickets_requesting_unit (requesting_unit_id)');
        }
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'org_ticket_schema', $exception->getMessage());
        }
    }
}

function org_has_company_schema(): bool
{
    static $available = null;
    if ($available !== null) return $available;
    try {
        $tables = db()->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'org_companies'")->fetchColumn();
        $columns = db()->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'org_units' AND COLUMN_NAME = 'company_id'")->fetchColumn();
        $userColumn = db()->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'company_id'")->fetchColumn();
        $available = (int) $tables > 0 && (int) $columns > 0 && (int) $userColumn > 0;
    } catch (Throwable $ignored) { $available = false; }
    return $available;
}

function org_companies_all(bool $activeOnly = true): array
{
    if (!org_has_company_schema()) return [];
    $sql = 'SELECT id, code, name, company_type, is_active FROM org_companies';
    if ($activeOnly) $sql .= ' WHERE is_active = 1';
    $companies = db()->query($sql . ' ORDER BY id')->fetchAll();
    foreach ($companies as &$company) $company['name'] = org_company_display_name($company);
    unset($company);
    return $companies;
}

function org_company_display_name(array $company): string
{
    $name = trim((string) ($company['name'] ?? ''));
    if ($name !== '') return $name;
    return match ((string) ($company['code'] ?? '')) {
        'own' => 'شرکت اصلی',
        'partner' => 'شرکت همکار',
        default => 'شرکت',
    };
}

function org_company_user_label(int $companyId): string
{
    if ($companyId <= 0 || !org_has_company_schema()) {
        return 'بدون شرکت';
    }
    $display = org_company_display_name_for_id($companyId);
    return $display !== '' ? $display : 'بدون شرکت';
}

/** نام نمایشی شرکت شریک از جدول org_companies (با کش static). */
function org_partner_company_display_name(): string
{
    static $partner = null;
    if ($partner === null) {
        $partner = '';
        foreach (org_companies_all() as $company) {
            if ((string) ($company['company_type'] ?? '') === 'partner') {
                $partner = trim((string) $company['name']);
                break;
            }
        }
    }
    return $partner;
}

/** نام نمایشی یک شرکت بر اساس شناسهٔ آن؛ '' اگر شرکت فعال یافت نشد. */
function org_company_display_name_for_id(int $companyId): string
{
    if ($companyId <= 0) {
        return '';
    }
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (org_companies_all() as $company) {
            $cache[(int) $company['id']] = trim((string) $company['name']);
        }
    }
    return $cache[$companyId] ?? '';
}

/**
 * نام نمایشی هر واحد چارت برای نمایش در همهٔ فهرست‌ها/گزارش‌ها:
 *  - «مدیر شرکت همکار» (company_manager) همیشه با نام تنظیماتی شرکت همکار دیده می‌شود؛
 *  - «واحد مستقل (نمایندگی)» پسوند «(مستقل)» می‌گیرد؛
 *  - هر واحدِ شرکت همکار (به‌جز سرشاخهٔ شرکت) با پیشوند «نام شرکت همکار.» دیده می‌شود
 *    (مثل «آریا ۲.اداری») تا معاونت/ادارهٔ هم‌نامِ شرکت اصلی و شرکت همکار در همه‌جا
 *    قابل تشخیص باشند؛ نام شرکت اصلی بدون تغییر می‌ماند.
 */
function org_unit_display_name(string $name, string $type, int $companyId): string
{
    $label = trim($name);
    $type = trim($type);
    $suffix = '';
    if ($type === 'liaison') {
        $label = $label === '' ? 'واحد مستقل' : $label;
        $suffix = ' (مستقل)';
    } elseif ($label === '') {
        $label = $type === 'company_manager' ? 'شرکت همکار' : 'بخش سازمانی';
    }
    $companyType = $companyId > 0 ? org_company_type_for_id($companyId) : '';
    $companyName = $companyId > 0 ? org_company_display_name_for_id($companyId) : '';
    if ($companyName === '' && $companyType === 'partner') {
        $companyName = org_partner_company_display_name();
    }
    if ($type === 'company_manager') {
        return $companyName !== '' ? $companyName : $label;
    }
    if ($companyType === 'partner' && $companyName !== '') {
        return $companyName . '.' . $label . $suffix;
    }
    return $label . $suffix;
}

function org_company_by_id(int $companyId): ?array
{
    if ($companyId <= 0 || !org_has_company_schema()) return null;
    $query = db()->prepare('SELECT id, code, name, company_type, is_active FROM org_companies WHERE id = ? LIMIT 1');
    $query->execute([$companyId]);
    $company = $query->fetch() ?: null;
    if ($company !== null) $company['name'] = org_company_display_name($company);
    return $company;
}

function org_user_company_memberships(int $userId): array
{
    if ($userId <= 0 || !org_has_company_schema()) return [];
    $query = db()->prepare('SELECT company_id FROM (SELECT ou.company_id FROM users u JOIN org_units ou ON ou.id = u.org_unit_id AND ou.is_active = 1 WHERE u.id = ? UNION SELECT ou.company_id FROM org_units ou WHERE ou.manager_user_id = ? AND ou.is_active = 1 UNION SELECT ou.company_id FROM org_unit_managers m JOIN org_units ou ON ou.id = m.unit_id AND ou.is_active = 1 WHERE m.user_id = ?) memberships ORDER BY company_id');
    $query->execute([$userId, $userId, $userId]);
    return array_values(array_unique(array_map('intval', $query->fetchAll(PDO::FETCH_COLUMN))));
}

function org_user_company_id(array $user): int
{
    $memberships = org_user_company_memberships((int) ($user['id'] ?? 0));
    return count($memberships) === 1 ? $memberships[0] : 0;
}

function org_user_available_for_company(int $userId, int $companyId): bool
{
    $memberships = org_user_company_memberships($userId);
    return count($memberships) === 0 || (count($memberships) === 1 && $memberships[0] === $companyId);
}

function org_user_company_membership_join_sql(string $userAlias = 'u'): string
{
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $userAlias)) throw new InvalidArgumentException('نام مستعار جدول معتبر نیست.');
    if (!org_has_company_schema()) return 'LEFT JOIN (SELECT NULL AS user_id, 0 AS company_id) org_membership ON 1 = 0';
    return 'LEFT JOIN (SELECT linked.user_id, CASE WHEN COUNT(DISTINCT linked.company_id) = 1 THEN MIN(linked.company_id) ELSE 0 END AS company_id FROM (SELECT u2.id AS user_id, ou.company_id FROM users u2 JOIN org_units ou ON ou.id = u2.org_unit_id AND ou.is_active = 1 UNION SELECT ou.manager_user_id AS user_id, ou.company_id FROM org_units ou WHERE ou.manager_user_id IS NOT NULL AND ou.is_active = 1 UNION SELECT m.user_id, ou.company_id FROM org_unit_managers m JOIN org_units ou ON ou.id = m.unit_id AND ou.is_active = 1) linked GROUP BY linked.user_id) org_membership ON org_membership.user_id = ' . $userAlias . '.id';
}

function org_company_member_user_ids(int $companyId): array
{
    if ($companyId <= 0 || !org_has_company_schema()) return [];
    $query = db()->query('SELECT u.id FROM users u ' . org_user_company_membership_join_sql('u') . ' WHERE org_membership.company_id = ' . $companyId . ' ORDER BY u.id');
    return array_values(array_unique(array_map('intval', $query->fetchAll(PDO::FETCH_COLUMN))));
}

function org_company_context_id(array $user, int $requestedId = 0): int
{
    if (!org_has_company_schema()) return 1;
    $isPrimary = function_exists('user_is_primary_admin') ? user_is_primary_admin($user) : (int) ($user['is_primary_admin'] ?? 0) === 1;
    if ($isPrimary) {
        $companyId = $requestedId > 0 ? $requestedId : 1;
    } else {
        $memberships = org_user_company_memberships((int) ($user['id'] ?? 0));
        if (count($memberships) === 0) throw new RuntimeException('این حساب هنوز در هیچ‌یک از درخت‌های سازمانی ثبت نشده است؛ دسترسی شرکت ندارد.');
        if (count($memberships) !== 1) throw new RuntimeException('این حساب در بیش از یک درخت سازمانی ثبت شده؛ ابتدا عضویت متداخل را اصلاح کنید.');
        $companyId = $memberships[0];
    }
    $company = org_company_by_id($companyId);
    if ($company === null || (int) ($company['is_active'] ?? 0) !== 1) throw new RuntimeException('شرکت انتخاب‌شده غیرفعال یا در دسترس نیست.');
    return $companyId;
}

function org_current_company_id(): int
{
    if (!function_exists('current_user')) return 1;
    $user = current_user();
    if (!is_array($user)) return 1;
    $isPrimary = function_exists('user_is_primary_admin') ? user_is_primary_admin($user) : (int) ($user['is_primary_admin'] ?? 0) === 1;
    if ($isPrimary) return org_company_context_id($user);
    $memberships = org_user_company_memberships((int) ($user['id'] ?? 0));
    return count($memberships) === 1 ? $memberships[0] : 0;
}

function org_company_root_type(int $companyId): string
{
    $company = org_company_by_id($companyId);
    return (string) ($company['company_type'] ?? '') === 'partner' ? 'company_manager' : 'ceo';
}

function org_company_root_title(int $companyId): string
{
    $company = org_company_by_id($companyId);
    return org_company_root_type($companyId) === 'company_manager' ? 'مدیر شرکت' : 'مدیرعامل';
}

function org_has_sort_order(): bool
{
    static $available = null;
    if ($available !== null) {
        return $available;
    }
    try {
        $stmt = db()->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'org_units' AND COLUMN_NAME = 'sort_order'");
        $available = (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $ignored) {
        $available = false;
    }
    return $available;
}

function org_next_sort_order(int $parentId, ?int $companyId = null): int
{
    if ($parentId <= 0 || !org_has_sort_order()) return 0;
    $sql = 'SELECT COALESCE(MAX(sort_order), 0) + 10 FROM org_units WHERE parent_id = ? AND is_active = 1';
    $params = [$parentId];
    if ($companyId !== null && org_has_company_schema()) { $sql .= ' AND company_id = ?'; $params[] = $companyId; }
    $query = db()->prepare($sql); $query->execute($params);
    return (int) $query->fetchColumn();
}

function org_units_all(bool $activeOnly = true, ?int $companyId = null): array
{
    $sql = 'SELECT ou.*, m.full_name AS manager_name FROM org_units ou LEFT JOIN users m ON m.id = ou.manager_user_id';
    $conditions = [];
    if ($activeOnly) $conditions[] = 'ou.is_active = 1';
    if ($companyId !== null && org_has_company_schema()) $conditions[] = 'ou.company_id = ' . (int) $companyId;
    if ($conditions) $sql .= ' WHERE ' . implode(' AND ', $conditions);
    $sql .= org_has_sort_order() ? ' ORDER BY ou.unit_type, COALESCE(ou.sort_order, 0), ou.name, ou.id' : ' ORDER BY ou.unit_type, ou.name, ou.id';
    return db()->query($sql)->fetchAll();
}

/**
 * معاونت‌های فعال چارت سازمانی برای فهرست «معاونت درخواست‌کننده» در فرم درخواست.
 * همهٔ نقش‌ها این فهرست را می‌بینند تا درخواست به همان معاونت مسیردهی شود.
 */
function org_deputy_units(?int $companyId = null): array
{
    static $cache = [];
    org_ensure_schema();
    $companyId = $companyId ?? org_current_company_id();
    if (org_has_company_schema() && $companyId <= 0) return [];
    $cacheKey = $companyId > 0 ? $companyId : 0;
    if (isset($cache[$cacheKey])) return $cache[$cacheKey];
    $companyFilter = org_has_company_schema() && $companyId > 0 ? ' AND ou.company_id = ?' : '';
    $query = db()->prepare('SELECT ou.id, ou.name, ou.code, ou.parent_id, m.full_name AS manager_name FROM org_units ou LEFT JOIN users m ON m.id = ou.manager_user_id WHERE ou.is_active = 1 AND ou.unit_type = "deputy"' . $companyFilter . ' ORDER BY ou.name');
    $query->execute($companyFilter !== '' ? [$companyId] : []);
    return $cache[$cacheKey] = $query->fetchAll();
}

/**
 * ۱.۶۲.۱۰ — واحدهای مستقل (نمایندگی) کنار مدیرعامل: unit_type = 'liaison'، بدون والد
 * و بدون هیچ اتصالی به زیرمجموعه‌های مدیرعامل. هر سمت (right/left) حداکثر یک واحد فعال دارد.
 *
 * @return array<string,array> کلید = سمت ('right'|'left')
 */
function org_liaison_units(?int $companyId = null): array
{
    org_ensure_schema();
    $companyId = $companyId ?? org_current_company_id();
    if (org_has_company_schema() && $companyId <= 0) return [];
    $result = [];
    try {
        $sql = 'SELECT ou.*, m.full_name AS manager_name FROM org_units ou LEFT JOIN users m ON m.id = ou.manager_user_id WHERE ou.is_active = 1 AND ou.unit_type = "liaison"';
        if (org_has_company_schema() && $companyId > 0) $sql .= ' AND ou.company_id = ' . (int) $companyId;
        $rows = db()->query($sql . ' ORDER BY ou.id')->fetchAll();
        foreach ($rows as $row) {
            $side = (string) ($row['chart_side'] ?? '');
            if (in_array($side, ['right', 'left', 'center'], true) && !isset($result[$side])) {
                $result[$side] = $row;
            }
        }
    } catch (Throwable $ignored) {
    }
    return $result;
}

/** نوع‌های سرشاخهٔ «بخش‌های سازمان» (۶ بخش): مدیرعامل، واحدهای مستقل، معاونت‌ها، مدیریت مستقل، دفتر مدیرعامل، شرکت همکار. */
function org_root_types(): array
{
    return ['ceo', 'liaison', 'deputy', 'management', 'office', 'company_manager'];
}

/** نام نمایشی سرشاخهٔ ۶ بخشی (همان منطق org_unit_display_name برای هر دو شرکت). */
function org_root_display_name(array $unit): string
{
    return org_unit_display_name((string) ($unit['name'] ?? ''), (string) ($unit['unit_type'] ?? ''), (int) ($unit['company_id'] ?? 0));
}

/** فهرست سرشاخه‌های فعال هر دو درخت (۶ بخش) برای فهرست‌های یک‌دست «بخش سازمانی». ترتیب: مدیرعامل، مستقل‌ها، معاونت‌ها، مدیریت مستقل، دفتر، شرکت همکار. */
function org_root_units(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    org_ensure_schema();
    $types = org_root_types();
    $in = implode(',', array_fill(0, count($types), '?'));
    try {
        $query = db()->prepare('SELECT ou.id, ou.name, ou.unit_type, ou.parent_id, ou.company_id, ou.chart_side FROM org_units ou WHERE ou.is_active = 1 AND ou.unit_type IN (' . $in . ') ORDER BY FIELD(ou.unit_type, "ceo","liaison","deputy","management","office","company_manager"), ou.chart_side, ou.name');
        $query->execute($types);
        $rows = [];
        foreach ($query->fetchAll() as $unit) {
            $rows[] = [
                'id' => (int) $unit['id'],
                'name' => org_root_display_name($unit),
                'unit_type' => (string) $unit['unit_type'],
                'company_id' => (int) ($unit['company_id'] ?? 0),
                'chart_side' => (string) ($unit['chart_side'] ?? ''),
            ];
        }
        return $cache = $rows;
    } catch (Throwable $e) {
        if (function_exists('app_error_log')) {
            app_error_log('[org] root units failed: ' . $e->getMessage());
        }
        return $cache = [];
    }
}

/** نقش‌هایی که می‌توانند «بخش سازمانی / معاونت / بخش» را دستی انتخاب کنند (ثبت به‌جای دیگران): کارشناس (agent)، سوپروایزر، مدیر پشتیبانی و ادمین اصلی. بقیهٔ نقش‌ها — حتی مدیر — مقدار را از چارت می‌گیرند. */
function org_section_override_allowed(?string $role): bool
{
    return in_array((string) $role, ['agent', 'supervisor', 'support_manager', 'primary_admin'], true);
}

/** ریشهٔ ۶ بخشیِ کاربر از چارت؛ 0 یعنی جایگاه فعال ندارد (بدون جایگاه). */
function user_org_root_id(array $user): int
{
    $unitId = user_org_unit_id($user);
    if ($unitId <= 0) {
        return 0;
    }
    return org_nearest_root_id($unitId, true);
}

/** نزدیک‌ترین سرشاخهٔ ۶ بخشی از یک واحد (بالا رفتن سلسلهٔ والدها). 0 یعنی جایگاه یا سرشاخه‌ای ندارد. */
function org_nearest_root_id(int $startUnitId, bool $activeOnly = true): int
{
    if ($startUnitId <= 0) {
        return 0;
    }
    org_ensure_schema();
    $types = org_root_types();
    $stmt = db()->prepare('SELECT id, parent_id, unit_type, is_active FROM org_units WHERE id = ? LIMIT 1');
    $current = $startUnitId;
    for ($depth = 0; $depth < 12 && $current > 0; $depth++) {
        try {
            $stmt->execute([$current]);
            $unit = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return 0;
        }
        if (!$unit) {
            return 0;
        }
        if (in_array((string) $unit['unit_type'], $types, true)
            && (!$activeOnly || (int) ($unit['is_active'] ?? 0) === 1)) {
            return (int) $unit['id'];
        }
        $current = (int) ($unit['parent_id'] ?? 0);
    }
    return 0;
}

/** نرمال‌سازی شناسهٔ ذخیره‌شدهٔ انتساب (هر سطحی از چارت، حتی قدیمی) به نزدیک‌ترین سرشاخهٔ ۶ بخشی؛ برای داده‌های تاریخی بازنویسی نمی‌شود، فقط برای گزارش محاسبه می‌شود. 0 یعنی بی‌جایگاه. */
function org_stored_attribution_root_id($storedId): int
{
    $id = (int) ($storedId ?? 0);
    if ($id <= 0) {
        return 0;
    }
    return org_nearest_root_id($id, false);
}

/** نوع شرکت بر اساس شناسه (own|partner|'') با کش یک‌باره از org_companies. */
function org_company_type_for_id(int $companyId): string
{
    if ($companyId <= 0) {
        return '';
    }
    static $map = null;
    if ($map === null) {
        $map = [];
        try {
            foreach (db()->query('SELECT id, company_type FROM org_companies WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $map[(int) $row['id']] = (string) ($row['company_type'] ?? '');
            }
        } catch (Throwable $ignored) {
            $map = [];
        }
    }
    return $map[$companyId] ?? '';
}

/** نوع شرکتِ یک واحد چارت (own|partner|'') */
function org_unit_company_type(array $unit): string
{
    return org_company_type_for_id((int) ($unit['company_id'] ?? 0));
}

/**
 * نام ردیف رسیدگی (departments) برای یک سرشاخهٔ سازمانی.
 * شرکت اصلی: همان نام چارت (بدون تغییر). شرکت همکار: «نام شرکت همکار.نام سرشاخه»
 * (مثل «آریا ۲.اداری») تا با معاونت هم‌نامِ شرکت اصلی تداخل نکند؛ سرشاخهٔ company_manager
 * خودِ شرکت است و همان نام پیکربندی‌شده را می‌گیرد. اگر نام شرکت همکار ثبت نشده باشد، نام چارت برگردانده می‌شود.
 */
function org_routing_department_name(array $unit): string
{
    $rawName = trim((string) ($unit['name'] ?? ''));
    $rawType = trim((string) ($unit['unit_type'] ?? ''));
    $display = $rawName;
    if ($rawType === 'liaison' && $display === '') {
        $display = 'واحد مستقل';
    }
    if ($rawType === 'liaison') {
        $display .= ' (مستقل)';
    }
    if ($display === '') {
        $display = function_exists('org_root_display_name') ? org_root_display_name($unit) : '';
    }
    if ((string) ($unit['unit_type'] ?? '') === '') {
        return $display;
    }
    if (org_unit_company_type($unit) !== 'partner') {
        return $display;
    }
    $companyId = (int) ($unit['company_id'] ?? 0);
    $companyName = $companyId > 0 ? org_company_display_name_for_id($companyId) : '';
    if ($companyName === '') {
        $companyName = org_partner_company_display_name();
    }
    if ($companyName === '') {
        return $display;
    }
    if ((string) ($unit['unit_type'] ?? '') === 'company_manager') {
        return $companyName;
    }
    return $companyName . '.' . $display;
}

/** آیا ستون نگاشت org_root_unit_id روی جدول departments وجود دارد؟ (idempotent) */
function org_routing_map_available(): bool
{
    static $available = null;
    if ($available !== null) {
        return $available;
    }
    try {
        db()->query('SELECT org_root_unit_id FROM departments LIMIT 1');
        $available = true;
    } catch (Throwable $ignored) {
        $available = false;
    }
    return $available;
}

/**
 * تبدیل معاونت سازمانی انتخاب‌شده به معاونتِ مسیر رسیدگی (departments).
 * پایداری: ردیف رسیدگی با شناسهٔ واحد چارت (org_root_unit_id) شناسایی می‌شود؛ تغییر نام چارت یا شرکت
 * دیگر «ردیف رسیدگی جدید» نمی‌سازد. ردیف‌های قبلی (هم‌نام) در اولین فراخوانی «ادعا» (claim) می‌شوند:
 * شرکت اصلی → ردیف هم‌نام را نگه می‌دارد؛ شرکت همکار (غیر از سرشاخهٔ شرکت) → ردیف هم‌نام شرکت همکار
 * به ردیف «نام شرکت.نام سرشاخه» تغییرنام می‌یابد؛ سرشاخهٔ شرکت همکار ردیف قدیمی «شرکت همکار» را ادعا و
 * با نام پیکربندی‌شدهٔ فعلی جایگزین می‌کند. هیچ ارجاعی (tickets/cd_dvd/OLA/کاربران) چون با id است نمی‌شکند.
 */
function org_unit_routing_department_id(int $unitId): int
{
    if ($unitId <= 0) {
        return 0;
    }
    $unit = org_unit_by_id($unitId);
    if ($unit === null || !in_array((string) ($unit['unit_type'] ?? ''), org_root_types(), true)) {
        return 0;
    }
    $routingName = org_routing_department_name($unit);
    if ($routingName === '') {
        $routingName = trim((string) ($unit['name'] ?? ''));
    }
    if ($routingName === '') {
        return 0;
    }
    $mapReady = org_routing_map_available();

    // ۱) شناسایی با نگاشت پایدار — تغییر نام چارت/شرکت دیگر اثر نمی‌گذارد
    if ($mapReady) {
        $query = db()->prepare('SELECT id FROM departments WHERE org_root_unit_id = ? LIMIT 1');
        $query->execute([$unitId]);
        $hit = (int) ($query->fetchColumn() ?: 0);
        if ($hit > 0) {
            return $hit;
        }
    }

    // ۲) ادعای ردیف‌های موجود (هم‌نام) — تعیین ترتیب کاندیدها بر اساس نوع سرشاخه
    $isPartner = org_unit_company_type($unit) === 'partner';
    $isCompanyManager = (string) ($unit['unit_type'] ?? '') === 'company_manager';
    $candidates = $isPartner
        ? ($isCompanyManager ? [$routingName, 'شرکت همکار'] : [$routingName])
        : [$routingName];
    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }
        $find = db()->prepare('SELECT id FROM departments WHERE name = ? LIMIT 1');
        $find->execute([$candidate]);
        $foundId = (int) ($find->fetchColumn() ?: 0);
        if ($foundId <= 0) {
            continue;
        }
        if ($mapReady) {
            $claim = db()->prepare('UPDATE departments SET org_root_unit_id = ? WHERE id = ? AND org_root_unit_id IS NULL');
            $claim->execute([$unitId, $foundId]);
            if ($claim->rowCount() === 0) {
                // ردیف از قبل به واحد دیگری نگاشت شده است؛ این سرشاخه مسیری تازه می‌سازد
                $owner = db()->prepare('SELECT org_root_unit_id FROM departments WHERE id = ? LIMIT 1');
                $owner->execute([$foundId]);
                if ((int) ($owner->fetchColumn() ?: 0) !== $unitId) {
                    continue;
                }
            } elseif ($candidate !== $routingName) {
                // نمایش یکدست با نام پیکربندی‌شده (مثلاً «شرکت همکار» → «آریا ۲») — بهترین تلاش؛ ناموفق بودن مانع پایداری نمی‌شود
                try {
                    $rename = db()->prepare('UPDATE departments SET name = ? WHERE id = ? AND name = ?');
                    $rename->execute([$routingName, $foundId, $candidate]);
                } catch (Throwable $ignored) {
                    // تداخل نام: ردیف با همان نام قبلی و Map درست باقی می‌ماند
                }
            }
        }
        return $foundId;
    }

    // ۳) ساخت ردیف تازه
    $code = trim((string) ($unit['code'] ?? ''));
    try {
        if ($mapReady) {
            $insert = db()->prepare('INSERT INTO departments (name, code, is_active, org_root_unit_id) VALUES (?, ?, 1, ?)');
            $insert->execute([$routingName, $code !== '' ? $code : null, $unitId]);
        } else {
            $insert = db()->prepare('INSERT INTO departments (name, code, is_active) VALUES (?, ?, 1)');
            $insert->execute([$routingName, $code !== '' ? $code : null]);
        }
        return (int) db()->lastInsertId();
    } catch (Throwable $exception) {
        $retry = db()->prepare('SELECT id FROM departments WHERE name = ? AND org_root_unit_id = ? LIMIT 1');
        $retry->execute([$routingName, $unitId]);
        $deptId = (int) ($retry->fetchColumn() ?: 0);
        if ($deptId > 0) {
            return $deptId;
        }
        return 0;
    }
}

function org_ceo_unit_id(?int $companyId = null): int
{
    $companyId = $companyId ?? org_current_company_id();
    if (org_has_company_schema() && $companyId <= 0) throw new RuntimeException('این حساب در هیچ درخت سازمانی معرفی نشده است.');
    $rootType = org_has_company_schema() && $companyId > 0 ? org_company_root_type($companyId) : 'ceo';
    $sql = 'SELECT id FROM org_units WHERE unit_type = ?';
    $params = [$rootType];
    if (org_has_company_schema() && $companyId > 0) { $sql .= ' AND company_id = ?'; $params[] = $companyId; }
    $sql .= ' ORDER BY id LIMIT 1';
    $query = db()->prepare($sql); $query->execute($params); $id = (int) $query->fetchColumn();
    if ($id > 0) return $id;
    // ایجاد ریشه هنگام نمایش صفحه ممنوع است؛ ریشه‌ها باید از migration ساخته شوند.
    throw new RuntimeException('ریشهٔ سازمان این شرکت در پایگاه‌داده پیدا نشد؛ migration شرکت‌ها را از مسیر رسمی به‌روزرسانی اجرا کنید.');
}

/** واحدهایی که می‌توان کارشناس مستقیم به آن‌ها وصل کرد: معاونت، مدیریت مستقل، اداره یا واحد نمایندگی. */
function org_unit_accepts_experts(?array $unit): bool
{
    if ($unit === null) {
        return false;
    }
    $type = (string) ($unit['unit_type'] ?? '');
    return in_array($type, ['department', 'deputy', 'management', 'liaison'], true) && (int) ($unit['is_active'] ?? 0) === 1;
}

function org_unit_by_id(int $id): ?array
{
    if ($id <= 0) return null;
    $query = db()->prepare('SELECT * FROM org_units WHERE id = ? LIMIT 1');
    $query->execute([$id]);
    return $query->fetch() ?: null;
}

function org_unit_by_company(int $id, int $companyId): ?array
{
    if ($id <= 0 || $companyId <= 0 || !org_has_company_schema()) return null;
    $query = db()->prepare('SELECT * FROM org_units WHERE id = ? AND company_id = ? LIMIT 1');
    $query->execute([$id, $companyId]);
    return $query->fetch() ?: null;
}

function org_company_redirect_url(int $companyId, string $fragment = ''): string
{
    $url = 'index.php?page=organization&company_id=' . max(1, $companyId);
    return $fragment !== '' ? $url . '#' . ltrim($fragment, '#') : $url;
}

function org_unit_children(int $parentId): array
{
    $order = org_has_sort_order() ? 'COALESCE(sort_order, 0), name, id' : 'name, id';
    $query = db()->prepare('SELECT * FROM org_units WHERE parent_id = ? AND is_active = 1 ORDER BY ' . $order);
    $query->execute([$parentId]);
    return $query->fetchAll();
}

function org_unit_subtree_ids(int $rootId): array
{
    $ids = [$rootId];
    $stack = [$rootId];
    while ($stack) {
        $current = array_pop($stack);
        foreach (org_unit_children((int) $current) as $child) {
            $childId = (int) $child['id'];
            if (!in_array($childId, $ids, true)) {
                $ids[] = $childId;
                $stack[] = $childId;
            }
        }
    }
    return $ids;
}

function org_unit_path_names(int $unitId): string
{
    $names = [];
    $guard = 0;
    while ($unitId > 0 && $guard < 50) {
        $guard++;
        $unit = org_unit_by_id($unitId);
        if (!$unit) {
            break;
        }
        array_unshift($names, (string) $unit['name']);
        $unitId = (int) ($unit['parent_id'] ?? 0);
    }
    return implode(' / ', $names);
}

function org_unit_manager_ids(int $unitId): array
{
    $query = db()->prepare('SELECT user_id FROM org_unit_managers WHERE unit_id = ? ORDER BY is_primary DESC, user_id');
    $query->execute([$unitId]);
    return array_map('intval', $query->fetchAll(PDO::FETCH_COLUMN));
}

function org_unit_is_primary_admin(int $unitId): bool
{
    $unit = org_unit_by_id($unitId);
    return $unit !== null && (int) $unit['is_primary_admin'] === 1;
}

function user_org_unit_id(array $user): int
{
    return (int) ($user['org_unit_id'] ?? 0);
}

function user_is_unit_manager(array $user): bool
{
    $query = db()->prepare('SELECT COUNT(*) FROM org_unit_managers WHERE user_id = ?');
    $query->execute([(int) ($user['id'] ?? 0)]);
    return (int) $query->fetchColumn() > 0;
}

function user_managed_unit_ids(array $user): array
{
    static $cache = [];
    $userId = (int) ($user['id'] ?? 0);
    if ($userId <= 0) {
        return [];
    }
    if (isset($cache[$userId])) {
        return $cache[$userId];
    }
    if ((int) ($user['is_primary_admin'] ?? 0) === 1) {
        return $cache[$userId] = array_map('intval', db()->query('SELECT id FROM org_units WHERE is_active = 1')->fetchAll(PDO::FETCH_COLUMN));
    }
    $query = db()->prepare('SELECT unit_id FROM org_unit_managers WHERE user_id = ?');
    $query->execute([$userId]);
    return $cache[$userId] = array_map('intval', $query->fetchAll(PDO::FETCH_COLUMN));
}

function user_org_scope_unit_ids(array $user): array
{
    $scope = [];
    foreach (user_managed_unit_ids($user) as $unitId) {
        foreach (org_unit_subtree_ids((int) $unitId) as $childId) {
            $scope[$childId] = $childId;
        }
    }
    return array_values($scope);
}

function user_is_org_manager_of(array $user, int $targetUserId): bool
{
    $query = db()->prepare('SELECT org_unit_id FROM users WHERE id = ? LIMIT 1');
    $query->execute([$targetUserId]);
    $targetUnit = (int) $query->fetchColumn();
    return $targetUnit > 0 && in_array($targetUnit, user_org_scope_unit_ids($user), true);
}

/**
 * ۱.۶۲.۹۴ — اصلاح «گروه‌های دسترسی» برای درخت سازمان: پیش‌تر این تابع فقط به
 * پرچم is_primary_admin یا نقش ادمین اصلی نگاه می‌کرد و مجوز `org.manage` (که در
 * کاتالوگ موجود است و گروه/نقش می‌توانندش اعطا کنند) عملاً نادیده گرفته می‌شد؛
 * نتیجه: کاربر دارای گروه با org.manage صفحه را می‌دید ولی هیچ تغییری نمی‌توانست بدهد.
 * ترتیب حفظ شده است تا دادهٔ قدیمی (is_primary_admin روی نقش‌های دیگر) نشکند.
 */
function user_can_manage_organization(array $user): bool
{
    if ((int) ($user['is_primary_admin'] ?? 0) === 1) {
        return true;
    }
    if (function_exists('user_can')) {
        return user_can($user, 'org.manage');
    }
    return function_exists('is_admin_role') ? is_admin_role((string) ($user['role'] ?? '')) : (string) ($user['role'] ?? '') === 'admin';
}

function org_snapshot(?int $companyId = null): array
{
    $hasCompanies = org_has_company_schema();
    $sortField = org_has_sort_order() ? ', sort_order' : '';
    $companyField = $hasCompanies ? ', company_id' : '';
    $unitWhere = $hasCompanies && $companyId !== null ? ' WHERE company_id = ?' : '';
    $unitsQuery = db()->prepare('SELECT id, name, code, parent_id, unit_type, chart_side, manager_user_id, is_primary_admin, is_active' . $sortField . $companyField . ' FROM org_units' . $unitWhere . ' ORDER BY id');
    $unitsQuery->execute($unitWhere !== '' ? [$companyId] : []);
    $managerSql = 'SELECT m.unit_id, m.user_id, m.is_primary FROM org_unit_managers m';
    if ($hasCompanies && $companyId !== null) $managerSql .= ' JOIN org_units ou ON ou.id = m.unit_id WHERE ou.company_id = ' . (int) $companyId;
    $usersSql = 'SELECT u.id, u.org_unit_id, u.manager_user_id FROM users u';
    if ($hasCompanies && $companyId !== null) $usersSql .= ' ' . org_user_company_membership_join_sql('u') . ' WHERE COALESCE(u.auth_source, \'\') <> \'food\' AND (org_membership.company_id = ' . (int) $companyId . ' OR org_membership.user_id IS NULL)';
    else $usersSql .= ' ORDER BY u.id';
    if ($hasCompanies && $companyId !== null) $usersSql .= ' ORDER BY u.id';
    $usersQuery = db()->query($usersSql);
    // Undo فقط انتساب سازمانی را برمی‌گرداند؛ role، ادمین اصلی و وضعیت فعال کاربر عمداً خارج‌اند.
    return [
        'units' => $unitsQuery->fetchAll(),
        'unit_managers' => db()->query($managerSql . ' ORDER BY m.unit_id, m.user_id')->fetchAll(),
        'users' => $usersQuery->fetchAll(),
        'has_sort_order' => org_has_sort_order(),
        'company_id' => $companyId,
    ];
}

function org_snapshot_maps(array $snapshot): array
{
    $maps = ['units' => [], 'unit_managers' => [], 'users' => []];
    foreach ((array) ($snapshot['units'] ?? []) as $row) $maps['units'][(int) $row['id']] = $row;
    foreach ((array) ($snapshot['unit_managers'] ?? []) as $row) $maps['unit_managers'][(int) $row['unit_id'] . ':' . (int) $row['user_id']] = $row;
    foreach ((array) ($snapshot['users'] ?? []) as $row) $maps['users'][(int) $row['id']] = $row;
    return $maps;
}

function org_snapshot_row_signature(string $table, ?array $row): string
{
    if ($row === null) return 'null';
    if ($table === 'units') {
        $keys = ['id', 'name', 'code', 'parent_id', 'unit_type', 'chart_side', 'manager_user_id', 'is_primary_admin', 'is_active'];
        if (array_key_exists('sort_order', $row)) $keys[] = 'sort_order';
        if (array_key_exists('company_id', $row)) $keys[] = 'company_id';
        $value = [];
        foreach ($keys as $key) {
            $raw = $row[$key] ?? null;
            $value[$key] = in_array($key, ['id', 'parent_id', 'manager_user_id', 'is_primary_admin', 'is_active', 'sort_order', 'company_id'], true)
                ? ($raw === null || $raw === '' ? null : (int) $raw)
                : ($raw === null || $raw === '' ? null : (string) $raw);
        }
    } elseif ($table === 'unit_managers') {
        $value = ['unit_id' => (int) $row['unit_id'], 'user_id' => (int) $row['user_id'], 'is_primary' => (int) $row['is_primary']];
    } else {
        $value = ['id' => (int) $row['id']];
        foreach (['org_unit_id', 'manager_user_id'] as $key) $value[$key] = ($row[$key] ?? null) === null || ($row[$key] ?? '') === '' ? null : (int) $row[$key];
    }
    return serialize($value);
}

function org_snapshot_delta(array $before, array $after): array
{
    $beforeMaps = org_snapshot_maps($before);
    $afterMaps = org_snapshot_maps($after);
    $beforeDelta = ['units' => [], 'unit_managers' => [], 'users' => [], 'has_sort_order' => !empty($before['has_sort_order'])];
    $afterDelta = ['units' => [], 'unit_managers' => [], 'users' => [], 'has_sort_order' => !empty($after['has_sort_order'])];
    foreach (['units', 'unit_managers', 'users'] as $table) {
        $keys = array_unique(array_merge(array_keys($beforeMaps[$table]), array_keys($afterMaps[$table])));
        foreach ($keys as $key) {
            $old = $beforeMaps[$table][$key] ?? null;
            $new = $afterMaps[$table][$key] ?? null;
            if (org_snapshot_row_signature($table, $old) === org_snapshot_row_signature($table, $new)) continue;
            $beforeDelta[$table][$key] = $old;
            $afterDelta[$table][$key] = $new;
        }
    }
    return [$beforeDelta, $afterDelta];
}

function org_record_undo(string $label, array $snapshot, ?int $companyId = null): void
{
    if ($companyId === null || $companyId <= 0) { try { $companyId = org_current_company_id(); } catch (Throwable $ignored) { $companyId = 1; } }
    [$before, $after] = org_snapshot_delta($snapshot, org_snapshot($companyId));
    $_SESSION['org_undo'] = [
        'company_id' => $companyId,
        'label' => $label,
        'before' => $before,
        'after' => $after,
        'created_at' => time(),
        'can_undo' => true,
        'can_redo' => false,
    ];
}

function org_undo_state(?int $companyId = null): ?array
{
    $state = $_SESSION['org_undo'] ?? null;
    if (!is_array($state) || !is_array($state['before'] ?? null) || !is_array($state['after'] ?? null)) return null;
    if ($companyId !== null && (int) ($state['company_id'] ?? 1) !== $companyId) return null;
    return $state;
}

/**
 * بازگردانی delta ساختاریِ یک عملیات؛ از بازنویسی تغییرات دیگر کاربران/ماژول‌ها جلوگیری می‌کند.
 * expected مقادیر مورد انتظار وضعیت فعلی است تا اگر همان رکورد بعداً تغییر کرده، Undo متوقف شود.
 */
function org_restore_snapshot(array $target, array $expected, ?int $companyId = null): void
{
    $pdo = db();
    $hasCompanyScope = org_has_company_schema() && $companyId !== null;
    $companyClause = $hasCompanyScope ? ' AND company_id = ?' : '';
    $pdo->beginTransaction();
    try {
        foreach ((array) ($target['units'] ?? []) as $key => $targetRow) {
            $id = (int) $key;
            $expectedRow = $expected['units'][$key] ?? null;
            $lockUnit = $pdo->prepare('SELECT * FROM org_units WHERE id = ?' . $companyClause . ' LIMIT 1 FOR UPDATE');
            $lockUnit->execute($hasCompanyScope ? [$id, $companyId] : [$id]);
            $current = $lockUnit->fetch() ?: null;
            if ($expectedRow === null) {
                $tombstone = $targetRow;
                if (is_array($tombstone)) { $tombstone['is_active'] = 0; $tombstone['manager_user_id'] = null; }
                if ($current !== null && (!is_array($tombstone) || org_snapshot_row_signature('units', $current) !== org_snapshot_row_signature('units', $tombstone))) throw new RuntimeException('این واحد پس از عملیات تغییر کرده است؛ برای جلوگیری از بازنویسی، Undo/Redo متوقف شد.');
            } elseif ($current === null || org_snapshot_row_signature('units', $current) !== org_snapshot_row_signature('units', $expectedRow)) {
                throw new RuntimeException('یکی از واحدها پس از عملیات تغییر کرده است؛ برای جلوگیری از بازنویسی، Undo/Redo متوقف شد.');
            }
        }
        foreach ((array) ($target['unit_managers'] ?? []) as $key => $targetRow) {
            [$unitId, $userId] = array_map('intval', explode(':', (string) $key, 2));
            $managerLockSql = 'SELECT m.unit_id, m.user_id, m.is_primary FROM org_unit_managers m';
            if ($hasCompanyScope) $managerLockSql .= ' JOIN org_units ou ON ou.id = m.unit_id';
            $managerLockSql .= ' WHERE m.unit_id = ? AND m.user_id = ?' . ($hasCompanyScope ? ' AND ou.company_id = ?' : '') . ' LIMIT 1 FOR UPDATE';
            $query = $pdo->prepare($managerLockSql);
            $query->execute($hasCompanyScope ? [$unitId, $userId, $companyId] : [$unitId, $userId]); $current = $query->fetch() ?: null;
            $expectedRow = $expected['unit_managers'][$key] ?? null;
            if (org_snapshot_row_signature('unit_managers', $current) !== org_snapshot_row_signature('unit_managers', $expectedRow)) throw new RuntimeException('انتصاب یکی از مدیران پس از عملیات تغییر کرده است؛ Undo/Redo متوقف شد.');
        }
        foreach ((array) ($target['users'] ?? []) as $key => $targetRow) {
            $id = (int) $key;
            $query = $pdo->prepare('SELECT id, org_unit_id, manager_user_id FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
            $query->execute([$id]); $current = $query->fetch() ?: null;
            $expectedRow = $expected['users'][$key] ?? null;
            if (org_snapshot_row_signature('users', $current) !== org_snapshot_row_signature('users', $expectedRow)) throw new RuntimeException('انتساب سازمانی یکی از کاربران پس از عملیات تغییر کرده است؛ Undo/Redo متوقف شد.');
        }

        $hasSort = org_has_sort_order();
        foreach ((array) ($target['units'] ?? []) as $key => $row) {
            $id = (int) $key;
            if ($row === null) {
                // رکورد جدید حذف فیزیکی نمی‌شود؛ برای حفظ تاریخچه فقط بایگانی می‌شود.
                $pdo->prepare('UPDATE org_units SET is_active = 0, manager_user_id = NULL WHERE id = ?' . $companyClause)->execute($hasCompanyScope ? [$id, $companyId] : [$id]);
                continue;
            }
            $sortSet = $hasSort && !empty($target['has_sort_order']) ? ', sort_order = ?' : '';
            $companySet = org_has_company_schema() && array_key_exists('company_id', $row) ? ', company_id = ?' : '';
            $sql = 'UPDATE org_units SET name = ?, code = ?, parent_id = ?, unit_type = ?, chart_side = ?, manager_user_id = ?, is_primary_admin = ?, is_active = ?' . $sortSet . $companySet . ' WHERE id = ?' . $companyClause;
            $values = [(string) $row['name'], ($row['code'] ?? null) !== '' ? $row['code'] : null, (int) ($row['parent_id'] ?? 0) > 0 ? (int) $row['parent_id'] : null, (string) $row['unit_type'], ($row['chart_side'] ?? null) !== '' ? ($row['chart_side'] ?? null) : null, (int) ($row['manager_user_id'] ?? 0) > 0 ? (int) $row['manager_user_id'] : null, (int) $row['is_primary_admin'], (int) $row['is_active']];
            if ($sortSet !== '') $values[] = (int) ($row['sort_order'] ?? 0);
            if ($companySet !== '') $values[] = (int) ($row['company_id'] ?? 1);
            $values[] = $id;
            if ($hasCompanyScope) $values[] = $companyId;
            $pdo->prepare($sql)->execute($values);
        }
        foreach ((array) ($target['unit_managers'] ?? []) as $key => $row) {
            [$unitId, $userId] = array_map('intval', explode(':', (string) $key, 2));
            $pdo->prepare('DELETE FROM org_unit_managers WHERE unit_id = ? AND user_id = ?')->execute([$unitId, $userId]);
            if ($row !== null) $pdo->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, ?)')->execute([$unitId, $userId, (int) $row['is_primary']]);
        }
        foreach ((array) ($target['users'] ?? []) as $key => $row) {
            if ($row === null) continue;
            $pdo->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = ? WHERE id = ?')->execute([(int) ($row['org_unit_id'] ?? 0) > 0 ? (int) $row['org_unit_id'] : null, (int) ($row['manager_user_id'] ?? 0) > 0 ? (int) $row['manager_user_id'] : null, (int) $key]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}

/**
 * ۱.۳۷.۸ — نقش خودکار بر اساس سمت در چارت سازمانی.
 * هر کاربری که مدیر (یا عضو مدیریت) یک معاونت/مدیریت/واحد فعال باشد، اگر نقشش «کاربر» یا «کارشناس» باشد
 * به «مدیر» تبدیل می‌شود. وقتی سمت برداشته شود و نقش را همین سازوکار تغییر داده باشد، به نقش قبلی برمی‌گردد.
 * نقش‌های استثنا (بازرسی، سوپروایزر، مدیر پشتیبانی، ادمین اصلی) هرگز توسط این سازوکار تغییر نمی‌کنند.
 */
function org_position_unit_types(): array
{
    // liaison (واحدهای مستقل) هم نقش خودکار manager می‌گیرد تا مدیران آنها بتوانند مهمان درخواست کنند و به باشگاه مدیرعامل/همکار اضافه شوند.
    return ['ceo', 'company_manager', 'deputy', 'management', 'office', 'department', 'liaison'];
}

function org_exempt_roles(): array
{
    return ['supervisor', 'support_manager', 'inspector', 'primary_admin'];
}

function org_ensure_role_sync_columns(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $columns = ['org_auto_role TINYINT(1) NOT NULL DEFAULT 0', 'org_prev_role VARCHAR(20) NULL'];
    foreach ($columns as $column) {
        try {
            db()->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS ' . $column);
        } catch (Throwable $ignored) {
            try {
                db()->exec('ALTER TABLE users ADD COLUMN ' . $column);
            } catch (Throwable $alsoIgnored) {
            }
        }
    }
}

/** @return int[] شناسهٔ کاربرانی که در حال حاضر سمت مدیریتی فعال دارند. */
function org_position_user_ids(): array
{
    $types = "'" . implode("','", org_position_unit_types()) . "'";
    $ids = [];
    foreach (db()->query("SELECT manager_user_id FROM org_units WHERE is_active = 1 AND manager_user_id > 0 AND unit_type IN ($types)")->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $ids[] = (int) $id;
    }
    try {
        $rows = db()->query("SELECT m.user_id FROM org_unit_managers m JOIN org_units u ON u.id = m.unit_id WHERE u.is_active = 1 AND u.unit_type IN ($types)")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as $id) {
            $ids[] = (int) $id;
        }
    } catch (Throwable $ignored) {
    }
    return array_values(array_unique(array_filter($ids, static fn (int $v): bool => $v > 0)));
}

/** @return int[] شناسهٔ کاربران عادی/کارشناسی که در واحد فعال عضو هستند. */
function org_expert_user_ids(): array
{
    $rows = db()->query("SELECT DISTINCT u.id FROM users u JOIN org_units ou ON ou.id = u.org_unit_id WHERE u.org_unit_id IS NOT NULL AND u.org_unit_id > 0 AND ou.is_active = 1 AND ou.unit_type IN ('deputy', 'management', 'department', 'liaison')")->fetchAll(PDO::FETCH_COLUMN);
    return array_map('intval', $rows);
}

/** همگام‌سازی نقش مدیر/کارشناس با جایگاه در چارت، بدون اعطای نقش ادمین اصلی. */
function org_sync_position_roles(): void
{
    if (db()->inTransaction()) {
        return;
    }
    org_ensure_schema();
    org_ensure_role_sync_columns();
    $positions = org_position_user_ids();
    $experts = org_expert_user_ids();
    $positionSet = array_fill_keys($positions, true);
    $expertSet = array_fill_keys($experts, true);
    $placeholders = $positions ? implode(',', array_fill(0, count($positions), '?')) : '';

    // ۱) مدیر: کاربر/کارشناس با جایگاه مدیریتی → نقش سراسری manager.
    // اگر کاربر قبلاً به‌عنوان کارشناس به user تبدیل شده بود، نشانگر نقش پایه حفظ می‌شود.
    if ($positions) {
        $promote = db()->prepare("UPDATE users SET org_prev_role = CASE WHEN org_auto_role = 1 AND org_prev_role = '__agent__' THEN org_prev_role ELSE role END, org_auto_role = 1, role = 'manager' WHERE id IN ($placeholders) AND role IN ('user','agent')");
        $promote->execute($positions);
    }

    // ۲) خروج از سمت مدیریت: نقش قبلی برگردد؛ اگر فرد هنوز کارشناس است، role=user بماند.
    $sql = "SELECT id, org_prev_role FROM users WHERE org_auto_role = 1 AND role = 'manager'";
    if ($positions) $sql .= " AND id NOT IN ($placeholders)";
    $stmt = db()->prepare($sql);
    $stmt->execute($positions);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $userId = (int) $row['id'];
        $previous = (string) ($row['org_prev_role'] ?? '');
        if ($previous === '__agent__') {
            if (isset($expertSet[$userId])) {
                db()->prepare("UPDATE users SET role = 'user', org_auto_role = 1, org_prev_role = '__agent__' WHERE id = ?")->execute([$userId]);
            } else {
                db()->prepare("UPDATE users SET role = 'agent', org_auto_role = 0, org_prev_role = NULL WHERE id = ?")->execute([$userId]);
            }
            continue;
        }
        $previous = in_array($previous, ['user', 'agent'], true) ? $previous : 'user';
        db()->prepare('UPDATE users SET role = ?, org_auto_role = 0, org_prev_role = NULL WHERE id = ?')->execute([$previous, $userId]);
    }

    // ۳) کارشناس عادی نقش user دارد؛ agent قبلی فقط تا وقتی در جایگاه کارشناسی است تبدیل می‌شود.
    $expertOnlyIds = array_values(array_filter($experts, static fn (int $id): bool => !isset($positionSet[$id])));
    if ($expertOnlyIds) {
        $expertPlaceholders = implode(',', array_fill(0, count($expertOnlyIds), '?'));
        db()->prepare("UPDATE users SET org_prev_role = '__agent__', org_auto_role = 1, role = 'user' WHERE id IN ($expertPlaceholders) AND role = 'agent' AND org_auto_role = 0")->execute($expertOnlyIds);
    }

    // ۴) کارشناس از درخت جدا شد: فقط نقش‌هایی که این سازوکار تغییر داده برگردانده شوند.
    $expertAuto = db()->query("SELECT id FROM users WHERE org_auto_role = 1 AND org_prev_role = '__agent__' AND role = 'user'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($expertAuto as $rawId) {
        $userId = (int) $rawId;
        if (isset($expertSet[$userId]) || isset($positionSet[$userId])) continue;
        db()->prepare("UPDATE users SET role = 'agent', org_auto_role = 0, org_prev_role = NULL WHERE id = ? AND org_auto_role = 1 AND org_prev_role = '__agent__' AND role = 'user'")->execute([$userId]);
    }
}
