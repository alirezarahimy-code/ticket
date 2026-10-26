<?php
declare(strict_types=1);

/**
 * lib/notify-users.php — اعلان‌ها، ورود دامین و کاربران — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: notify_user و notify_ticket_* / دید اعلان‌ها / شمارش نخوانده‌ها / تعطیلات SLA / ldap_authenticate (ورود دامین + نسخهٔ زمان‌سنجی‌شده) / کد پرسنلی / upsert_domain_user / اتصال دارایی‌های agent به کاربر.
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
}

function notify_user(int $userId, string $type, string $title, string $body, ?int $ticketId = null): void
{
    if ($userId <= 0) {
        return;
    }
    db()->prepare('INSERT INTO notifications (user_id, ticket_id, notification_type, title, body) VALUES (?, ?, ?, ?, ?)')->execute([$userId, $ticketId, $type, $title, $body]);
}

function notification_target_url(array $notification): string
{
    $type = (string) ($notification['notification_type'] ?? $notification['type'] ?? '');
    if ($type === 'food_cancel_request' || $type === 'food_cancel_decision') {
        return function_exists('food_order_cancel_inbox_url')
            ? food_order_cancel_inbox_url()
            : 'index.php?page=dashboard#food-cancel-requests';
    }
    if (!empty($notification['ticket_id'])) {
        return 'index.php?page=ticket&id=' . (int) $notification['ticket_id'];
    }
    return 'index.php?page=notifications';
}

function notify_ticket_parties(int $ticketId, string $type, string $title, string $body, ?int $excludeUserId = null): void
{
    $query = db()->prepare('SELECT t.requester_id, t.assigned_to, t.department_id, t.service_group, d.manager_user_id, hu.manager_user_id AS handling_manager_id FROM tickets t LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN handling_units hu ON hu.code = t.service_group WHERE t.id = ? LIMIT 1');
    $query->execute([$ticketId]);
    $ticket = $query->fetch();
    if (!$ticket) {
        return;
    }
    $supervisorQuery = db()->query('SELECT id FROM users WHERE role = "supervisor" AND is_active = 1');
    $supervisorIds = array_map('intval', $supervisorQuery->fetchAll(PDO::FETCH_COLUMN));
    $unassignedAgentIds = [];
    if ($type === 'ticket_created' && empty($ticket['assigned_to'])) {
        $agentQuery = db()->prepare('SELECT u.id FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.is_active = 1 AND (u.is_it_agent = 1 OR u.role IN ("agent", "admin")) AND (u.role = "admin" OR hu.code = ?)');
        $agentQuery->execute([(string) ($ticket['service_group'] ?? 'it')]);
        $unassignedAgentIds = array_map('intval', $agentQuery->fetchAll(PDO::FETCH_COLUMN));
    }
    $recipients = array_unique(array_filter(array_merge([(int) $ticket['requester_id'], (int) $ticket['assigned_to'], (int) $ticket['manager_user_id'], (int) $ticket['handling_manager_id']], $supervisorIds, $unassignedAgentIds)));
    foreach ($recipients as $recipientId) {
        if ($excludeUserId === null || $recipientId !== $excludeUserId) {
            notify_user($recipientId, $type, $title, $body, $ticketId);
        }
    }
}

function notify_ticket_staff(int $ticketId, string $type, string $title, string $body, ?int $excludeUserId = null): void
{
    $query = db()->prepare('SELECT t.assigned_to, t.service_group, d.manager_user_id, hu.manager_user_id AS handling_manager_id FROM tickets t LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN handling_units hu ON hu.code = t.service_group WHERE t.id = ? LIMIT 1');
    $query->execute([$ticketId]);
    $ticket = $query->fetch();
    if (!$ticket) {
        return;
    }
    $supervisorQuery = db()->query('SELECT id FROM users WHERE role = "supervisor" AND is_active = 1');
    $supervisorIds = array_map('intval', $supervisorQuery->fetchAll(PDO::FETCH_COLUMN));
    $unassignedAgentIds = [];
    if (empty($ticket['assigned_to'])) {
        $agentQuery = db()->prepare('SELECT u.id FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.is_active = 1 AND (u.is_it_agent = 1 OR u.role IN ("agent", "admin")) AND (u.role = "admin" OR hu.code = ?)');
        $agentQuery->execute([(string) ($ticket['service_group'] ?? 'it')]);
        $unassignedAgentIds = array_map('intval', $agentQuery->fetchAll(PDO::FETCH_COLUMN));
    }
    foreach (array_unique(array_filter(array_merge([(int) $ticket['assigned_to'], (int) $ticket['manager_user_id'], (int) $ticket['handling_manager_id']], $supervisorIds, $unassignedAgentIds))) as $recipientId) {
        if ($excludeUserId === null || $recipientId !== $excludeUserId) {
            notify_user($recipientId, $type, $title, $body, $ticketId);
        }
    }
}

/** اعلان درخواست لغو غذا فقط برای مدیر پشتیبانی و مدیر اصلی است؛ کاربر عادی نباید در زنگوله ببیند. */
function notification_user_sees_food_cancel(int $userId, ?array $user = null): bool
{
    static $cache = [];
    if (array_key_exists($userId, $cache)) {
        return $cache[$userId];
    }
    $row = $user;
    if (!$row || (int) ($row['id'] ?? 0) !== $userId) {
        try {
            $stmt = db()->prepare('SELECT id, role, is_primary_admin FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            $row = [];
        }
    }
    if (function_exists('food_order_user_is_cancel_reviewer')) {
        return $cache[$userId] = food_order_user_is_cancel_reviewer($row);
    }
    if (function_exists('user_is_primary_admin') && user_is_primary_admin($row)) {
        return $cache[$userId] = true;
    }
    $role = (string) ($row['role'] ?? '');
    return $cache[$userId] = ($role === 'primary_admin' || $role === 'support_manager' || (int) ($row['is_primary_admin'] ?? 0) === 1);
}

function notification_visibility_sql(int $userId, ?array $user = null, string $alias = ''): string
{
    if (notification_user_sees_food_cancel($userId, $user)) {
        return '';
    }
    $col = ($alias !== '' ? $alias . '.' : '') . 'notification_type';
    return " AND {$col} <> 'food_cancel_request'";
}

/**
 * شمارش اعلان‌های نخوانده + آخرین شناسه با یک کوئری (برای کارایی؛ در هدر استفاده می‌شود).
 */
function notification_header_summary(int $userId, ?array $user = null): array
{
    static $cache = [];
    if (array_key_exists($userId, $cache)) {
        return $cache[$userId];
    }
    try {
        $query = db()->prepare('SELECT COALESCE(SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END), 0) AS unread, COALESCE(MAX(id), 0) AS latest FROM notifications WHERE user_id = ?' . notification_visibility_sql($userId, $user));
        $query->execute([$userId]);
        $row = $query->fetch() ?: [];
        return $cache[$userId] = [
            'unread' => (int) ($row['unread'] ?? 0),
            'latest' => (int) ($row['latest'] ?? 0),
        ];
    } catch (Throwable) {
        return $cache[$userId] = ['unread' => 0, 'latest' => 0];
    }
}

function is_sla_holiday(DateTimeImmutable $date): bool
{
    $query = db()->prepare('SELECT 1 FROM holidays WHERE holiday_date = ? AND is_active = 1 LIMIT 1');
    $query->execute([$date->format('Y-m-d')]);
    return (bool) $query->fetchColumn();
}

function ldap_authenticate(string $username, string $password): ?array
{
    if (!ldap_cfg('enabled', false) || !function_exists('ldap_connect') || $password === '') {
        return ldap_authenticate_measured($username, $password, static function (string $phase): void {});
    }
    $startedAt = microtime(true);
    $phase = 'ldap-connect';
    $phaseStartedAt = $startedAt;
    $phaseDurations = [];
    $markPhase = static function (string $nextPhase) use (&$phase, &$phaseStartedAt, &$phaseDurations): void {
        $now = microtime(true);
        $phaseDurations[$phase] = ($phaseDurations[$phase] ?? 0) + (int) round(($now - $phaseStartedAt) * 1000);
        $phase = $nextPhase;
        $phaseStartedAt = $now;
    };
    try {
        return ldap_authenticate_measured($username, $password, $markPhase);
    } finally {
        $markPhase('finished');
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        if ($durationMs >= 1000) {
            $stageSummary = [];
            foreach ($phaseDurations as $stage => $duration) {
                $stageSummary[] = $stage . ':' . $duration . 'ms';
            }
            error_log('ITSM LDAP login timing: duration_ms=' . $durationMs . '; stages=' . implode(',', $stageSummary));
        }
    }
}

function ldap_authenticate_measured(string $username, string $password, callable $markPhase): ?array
{
    if (!ldap_cfg('enabled', false) || !function_exists('ldap_connect') || $password === '') {
        return null;
    }
    $host = (string) ldap_cfg('host', '');
    if ($host === '') {
        return null;
    }
    $protocol = ldap_cfg('ssl', false) ? 'ldaps://' : 'ldap://';
    $connection = ldap_connect($protocol . $host, (int) ldap_cfg('port', ldap_cfg('ssl', false) ? 636 : 389));
    if (!$connection) {
        return null;
    }
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) { @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 8); }
    if (defined('LDAP_OPT_TIMELIMIT')) { @ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 8); }
    $bindDn = (string) ldap_cfg('bind_dn', '');
    $bindPassword = (string) ldap_cfg('bind_password', '');
    $serviceBindFailed = false;
    if ($bindDn !== '' && !@ldap_bind($connection, $bindDn, $bindPassword)) {
        ldap_auth_log('service-bind-failed', $username, $connection);
        $serviceBindFailed = true;
    }
    $markPhase('rootdse-discovery');
    $lookup = preg_replace('/^.*\\\\/', '', $username) ?: $username;
    $lookup = explode('@', $lookup, 2)[0];
    $escapedLookup = ldap_escape($lookup, '', LDAP_ESCAPE_FILTER);
    $escapedUsername = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
    $configuredFilter = (string) cfg('ldap.user_filter', '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))');
    $filter = ldap_person_filter(sprintf($configuredFilter, $escapedLookup));
    $fallbackFilter = '(&(objectCategory=person)(objectClass=user)(|(sAMAccountName=' . $escapedLookup . ')(userPrincipalName=' . $escapedUsername . ')))';
    $filters = array_values(array_unique([$filter, $fallbackFilter]));
    $attributes = ['distinguishedName', 'userPrincipalName', 'displayName', 'mail', 'department', 'sAMAccountName', 'employeeID', 'telephoneNumber', 'memberOf'];
    $searchBases = [(string) ldap_cfg('base_dn', '')];
    $domainSuffix = trim((string) ldap_cfg('domain_suffix', ''));
    $addDefaultNamingContext = static function () use (&$searchBases, &$domainSuffix, $connection): void {
        $rootDse = @ldap_read($connection, '', '(objectClass=*)', ['defaultNamingContext']);
        if (!$rootDse) {
            return;
        }
        $rootEntries = ldap_get_entries($connection, $rootDse);
        $defaultNamingContext = trim((string) ($rootEntries[0]['defaultnamingcontext'][0] ?? ''));
        if ($defaultNamingContext !== '' && !in_array($defaultNamingContext, $searchBases, true)) {
            $searchBases[] = $defaultNamingContext;
        }
        if ($domainSuffix === '' && $defaultNamingContext !== '') {
            $labels = [];
            foreach (explode(',', $defaultNamingContext) as $part) {
                if (preg_match('/^DC=(.+)$/i', trim($part), $match)) {
                    $labels[] = $match[1];
                }
            }
            $domainSuffix = implode('.', $labels);
        }
    };
    $addDefaultNamingContext();
    $markPhase('user-search');
    $searchUser = static function ($ldapConnection, array $bases, array $searchFilters, array $searchAttributes): array {
        foreach ($bases as $baseDn) {
            foreach ($searchFilters as $candidate) {
                $handle = @ldap_search($ldapConnection, $baseDn, $candidate, $searchAttributes);
                if (!$handle) {
                    continue;
                }
                $result = ldap_get_entries($ldapConnection, $handle);
                if (($result['count'] ?? 0) > 0) {
                    return [$handle, $result];
                }
            }
        }
        return [false, ['count' => 0]];
    };
    $tryDirectBind = static function ($ldapConnection, array $extraCandidates = []) use ($username, $lookup, $password, &$domainSuffix): bool {
        $candidates = [];
        foreach ($extraCandidates as $candidate) {
            if (trim((string) $candidate) !== '') {
                $candidates[] = trim((string) $candidate);
            }
        }
        if ($domainSuffix !== '') {
            $candidates[] = $lookup . '@' . ltrim($domainSuffix, '@');
        }
        if (str_contains($username, '@') || str_contains($username, '\\')) {
            $candidates[] = $username;
        }
        $candidates[] = $lookup;
        foreach (array_values(array_unique($candidates)) as $candidate) {
            if (@ldap_bind($ldapConnection, $candidate, $password)) {
                return true;
            }
        }
        return false;
    };
    $directBind = false;
    if ($serviceBindFailed) {
        $addDefaultNamingContext();
        $directBind = $tryDirectBind($connection);
        if (!$directBind) {
            ldap_auth_log('direct-bind-failed', $username, $connection);
            return null;
        }
    }
    [$search, $entries] = $searchUser($connection, $searchBases, $filters, $attributes);
    $markPhase('user-bind');
    if (!$search) {
        if (!$tryDirectBind($connection)) {
            ldap_auth_log('direct-bind-failed', $username, $connection);
            return null;
        }
        $directBind = true;
        $addDefaultNamingContext();
        [$search, $entries] = $searchUser($connection, $searchBases, $filters, $attributes);
    }
    if (!$search) {
        ldap_auth_log('user-search-failed', $username, $connection);
        return null;
    }
    if (($entries['count'] ?? 0) < 1) {
        if (!$directBind) {
            if ($tryDirectBind($connection)) {
                $directBind = true;
                $addDefaultNamingContext();
                [$search, $entries] = $searchUser($connection, $searchBases, $filters, $attributes);
            } else {
                ldap_auth_log('direct-bind-failed', $username, $connection);
            }
        }
    }
    if (($entries['count'] ?? 0) < 1) {
        if ($directBind) {
            return [
                'username' => $lookup,
                'full_name' => $lookup,
                'email' => '',
                'department' => '',
                'employee_number' => '',
                'phone' => '',
                'groups' => [],
            ];
        }
        ldap_auth_log('user-not-found', $username, $connection);
        return null;
    }
    $entry = $entries[0];
    $userDn = $entry['distinguishedname'][0] ?? ($entry['dn'] ?? '');
    $userUpn = $entry['userprincipalname'][0] ?? '';
    if (!$directBind) {
        $userBindOk = $userUpn !== '' && @ldap_bind($connection, $userUpn, $password);
        if (!$userBindOk && $userDn !== '') {
            $userBindOk = @ldap_bind($connection, $userDn, $password);
        }
        if (!$userBindOk && !$tryDirectBind($connection, [$userUpn, $entry['samaccountname'][0] ?? ''])) {
            ldap_auth_log('user-bind-failed', $username, $connection);
            return null;
        }
    }
    $markPhase('profile-assembly');
    $groups = [];
    for ($groupIndex = 0; $groupIndex < (int) ($entry['memberof']['count'] ?? 0); $groupIndex++) {
        $groups[] = (string) ($entry['memberof'][$groupIndex] ?? '');
    }
    return [
        'username' => $entry['samaccountname'][0] ?? $lookup,
        'full_name' => $entry['displayname'][0] ?? $username,
        'email' => $entry['mail'][0] ?? '',
        'department' => $entry['department'][0] ?? '',
        'employee_number' => $entry['employeeid'][0] ?? '',
        'phone' => $entry['telephonenumber'][0] ?? '',
        'groups' => $groups,
    ];
}

function normalize_employee_number(mixed $value): ?string
{
    $digits = ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'];
    $code = trim(strtr(trim((string) $value), $digits));
    return $code === '' ? null : $code;
}

// شناسه کاربری که این کد پرسنلی را دارد (۰ یعنی آزاد است). $exceptUserId برای ردیف خود کاربر.
function employee_number_owner(?string $code, int $exceptUserId = 0): int
{
    if ($code === null) {
        return 0;
    }
    $query = db()->prepare('SELECT id FROM users WHERE employee_number = ? AND id <> ? LIMIT 1');
    $query->execute([$code, $exceptUserId]);
    return (int) ($query->fetchColumn() ?: 0);
}

function upsert_domain_user(array $identity): array
{
    $username = trim((string) ($identity['username'] ?? ''));
    if ($username === '') {
        throw new RuntimeException('حساب Active Directory نام کاربری معتبر ندارد.');
    }
    $query = db()->prepare('SELECT * FROM users WHERE username = ? AND auth_source = "ldap" LIMIT 1');
    $query->execute([$username]);
    $user = $query->fetch();
    if (!$user) {
        $localQuery = db()->prepare('SELECT id FROM users WHERE username = ? AND auth_source = "local" LIMIT 1');
        $localQuery->execute([$username]);
        if ($localQuery->fetchColumn() !== false) {
            throw new RuntimeException('این نام کاربری قبلاً به یک حساب محلی اختصاص دارد؛ برای جلوگیری از انتقال نقش، حساب LDAP ساخته نشد.');
        }
    }
    $departmentId = find_or_create_department($identity['department'] ?? null);
    if ($user) {
        $effectiveDepartmentId = in_array($user['role'], ['agent', 'manager'], true) && (int) $user['department_id'] > 0
            ? (int) $user['department_id']
            : $departmentId;
        $empNo = normalize_employee_number($identity['employee_number'] ?? '');
        if ($empNo === null) {
            $empNo = normalize_employee_number($user['employee_number'] ?? ''); // اگر AD خالی است کد پرسنلی سامانه حفظ شود
        }
        $codeConflict = false;
        if ($empNo !== null && employee_number_owner($empNo, (int) $user['id']) > 0) {
            // کد AD قبلاً به کاربر دیگری داده شده: کد این کاربر تغییر نمی‌کند و تداخل گزارش می‌شود.
            $codeConflict = true;
            $empNo = normalize_employee_number($user['employee_number'] ?? '');
        }
        // ۱.۵۰: کاربری که از قبل کد پرسنلی دارد، نامش از فهرست رسمی پرسنل غذا می‌آید و از AD بازنویسی نمی‌شود.
        $hasCode = trim((string) ($user['employee_number'] ?? '')) !== '';
        $fullNameToSave = $hasCode ? (string) $user['full_name'] : $identity['full_name'];
        $update = db()->prepare('UPDATE users SET full_name = ?, email = ?, employee_number = ?, phone = ?, department = ?, department_id = ?, auth_source = "ldap" WHERE id = ?');
        $update->execute([$fullNameToSave, $identity['email'], $empNo, $identity['phone'] ?? '', $identity['department'], $effectiveDepartmentId, $user['id']]);
        $user['department_id'] = $effectiveDepartmentId;
        $user['employee_number'] = $empNo;
        link_agent_assets_to_user((int) $user['id'], (string) $identity['username'], $effectiveDepartmentId);
        return array_merge($user, $identity, ['username' => $username, 'employee_number' => $empNo, 'code_conflict' => $codeConflict]);
    }
    $role = ldap_role_for_groups((array) ($identity['groups'] ?? []));
    $handlingUnitId = null;
    if ($role === 'agent') {
        $handlingUnitQuery = db()->prepare('SELECT id FROM handling_units WHERE code = "it" AND is_active = 1 LIMIT 1');
        $handlingUnitQuery->execute();
        $handlingUnitId = (int) ($handlingUnitQuery->fetchColumn() ?: 0) ?: null;
    }
    $newCode = normalize_employee_number($identity['employee_number'] ?? '');
    $codeConflict = $newCode !== null && employee_number_owner($newCode) > 0;
    if ($codeConflict) {
        $newCode = null; // کد تکراری ثبت نمی‌شود؛ ردیف بدون کد ساخته می‌شود
    }
    $insert = db()->prepare('INSERT INTO users (username, full_name, email, employee_number, phone, department, department_id, handling_unit_id, role, is_it_agent, auth_source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "ldap")');
    $insert->execute([$username, $identity['full_name'], $identity['email'], $newCode, $identity['phone'] ?? '', $identity['department'], $departmentId, $handlingUnitId, $role, $role === 'agent' ? 1 : 0]);
    $identity['id'] = (int) db()->lastInsertId();
    $identity['employee_number'] = $newCode;
    $identity['code_conflict'] = $codeConflict;
    $identity['role'] = $role;
    $identity['is_it_agent'] = $role === 'agent' ? 1 : 0;
    $identity['is_active'] = 1;
    $identity['department_id'] = $departmentId;
    $identity['handling_unit_id'] = $handlingUnitId;
    link_agent_assets_to_user((int) $identity['id'], $username, $departmentId);
    $identity['username'] = $username;
    return $identity;
}

function link_agent_assets_to_user(int $userId, string $username, ?int $departmentId): void
{
    if ($username === '') {
        return;
    }
    $userQuery = db()->prepare('SELECT employee_number, phone FROM users WHERE id = ? LIMIT 1');
    $userQuery->execute([$userId]);
    $identity = $userQuery->fetch() ?: [];
    db()->prepare('UPDATE assets SET owner_user_id = ?, department_id = COALESCE(department_id, ?), employee_number = COALESCE(employee_number, ?), phone = COALESCE(phone, ?) WHERE source = "agent" AND domain_username = ? AND owner_user_id IS NULL')->execute([$userId, $departmentId, $identity['employee_number'] ?? null, $identity['phone'] ?? null, $username]);
}

