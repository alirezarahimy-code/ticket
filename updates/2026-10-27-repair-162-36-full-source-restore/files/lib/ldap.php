<?php
declare(strict_types=1);

/**
 * lib/ldap.php — پیکربندی و همگام‌سازی دامین (LDAP) — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: ldap_cfg / نگاشت گروه به نقش / فیلتر اشخاص / لاگ احراز / ldap_diagnose / ldap_sync_all_users (همگام‌سازی کامل کاربران دامین).
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
}

function ldap_cfg(string $key, mixed $default = null): mixed
{
    $map = [
        'enabled' => 'ldap_enabled',
        'host' => 'ldap_host',
        'port' => 'ldap_port',
        'ssl' => 'ldap_ssl',
        'base_dn' => 'ldap_base_dn',
        'bind_dn' => 'ldap_bind_dn',
        'bind_password' => 'ldap_bind_password',
        'domain_suffix' => 'ldap_domain_suffix',
    ];
    $value = isset($map[$key]) ? setting($map[$key]) : null;
    if ($value === null) {
        return cfg('ldap.' . $key, $default);
    }
    if (in_array($key, ['enabled', 'ssl'], true)) {
        return $value === '1';
    }
    return $value;
}

function ldap_role_for_groups(array $groups): string
{
    $mapping = json_decode((string) setting('ldap_group_map', json_encode(cfg('ldap.group_map', []), JSON_UNESCAPED_UNICODE)), true);
    if (!is_array($mapping)) {
        return 'user';
    }
    $groups = array_map(static fn (mixed $group): string => strtolower(trim((string) $group)), $groups);
    foreach (['supervisor', 'agent', 'manager'] as $role) {
        foreach ((array) ($mapping[$role] ?? []) as $mappedGroup) {
            if (in_array(strtolower(trim((string) $mappedGroup)), $groups, true)) {
                return $role;
            }
        }
    }
    return 'user';
}

function ldap_person_filter(string $filter): string
{
    $filter = trim($filter);
    if ($filter === '') {
        return '(&(objectCategory=person)(objectClass=user))';
    }
    if (stripos($filter, 'objectcategory=person') !== false) {
        return $filter;
    }
    return '(&' . $filter . '(objectCategory=person))';
}

function ldap_auth_log(string $stage, string $username, $connection = null): void
{
    $details = [];
    if ($connection !== null) {
        $details[] = ldap_error($connection);
        if (defined('LDAP_OPT_DIAGNOSTIC_MESSAGE')) {
            $diagnostic = '';
            @ldap_get_option($connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $diagnostic);
            if (trim((string) $diagnostic) !== '') {
                $details[] = preg_replace('/\s+/', ' ', trim(substr((string) $diagnostic, 0, 500)));
            }
        }
    }
    $suffix = $details ? ' | ' . implode(' | ', array_filter($details)) : '';
    error_log('ITSM LDAP authentication ' . $stage . ' for ' . substr($username, 0, 120) . $suffix);
}

/**
 * تشخیص گام‌به‌گام اتصال دامنه؛ برای نمایش پیام دقیق خطا در پنل.
 * @return array{ok:bool,summary:string,steps:array<int,array{label:string,ok:bool,detail:string}>}
 */
function ldap_diagnose(): array
{
    $steps = [];
    $add = static function (string $label, bool $ok, string $detail = '') use (&$steps): void {
        $steps[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
    };

    if (!function_exists('ldap_connect')) {
        $add('افزونه LDAP پیاچ‌پی', false, 'نصب نیست. در php.ini خط extension=ldap را فعال و وب‌سرور را ری‌استارت کنید.');
        return ['ok' => false, 'summary' => 'افزونه LDAP پیاچ‌پی نصب نیست.', 'steps' => $steps];
    }
    $add('افزونه LDAP پیاچ‌پی', true);

    $enabled = (bool) ldap_cfg('enabled', false);
    $host = trim((string) ldap_cfg('host', ''));
    $port = (int) ldap_cfg('port', 389);
    $ssl = (bool) ldap_cfg('ssl', false);
    $baseDn = trim((string) ldap_cfg('base_dn', ''));
    $bindDn = trim((string) ldap_cfg('bind_dn', ''));
    $bindPassword = (string) ldap_cfg('bind_password', '');

    $add('فعال بودن LDAP', $enabled, $enabled ? '' : 'در تنظیمات، «ورود کاربران شبکه فعال باشد» را تیک بزنید.');
    $add('آدرس کنترلر', $host !== '', $host !== '' ? $host : 'خالی است.');
    $add('Base DN', $baseDn !== '', $baseDn !== '' ? $baseDn : 'خالی است.');
    if (!$enabled || $host === '' || $baseDn === '') {
        return ['ok' => false, 'summary' => 'تنظیمات دامنه کامل نیست.', 'steps' => $steps];
    }

    $connection = @ldap_connect(($ssl ? 'ldaps://' : 'ldap://') . $host, $port);
    if (!$connection) {
        $add('اتصال به کنترلر', false, 'برقرار نشد؛ آدرس/پورت/فایروال را بررسی کنید.');
        return ['ok' => false, 'summary' => 'اتصال به کنترلر دامین برقرار نشد.', 'steps' => $steps];
    }
    @ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    @ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) { @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 8); }
    $add('اتصال به کنترلر', true, $host . ':' . $port);

    if ($bindDn !== '') {
        $bound = @ldap_bind($connection, $bindDn, $bindPassword);
        $add('ورود حساب سرویس', (bool) $bound, $bound ? '' : (function_exists('ldap_error') ? ldap_error($connection) : 'ناموفق'));
        if (!$bound) {
            return ['ok' => false, 'summary' => 'رمز یا DN حساب سرویس اشتباه است.', 'steps' => $steps];
        }
    } else {
        $bound = @ldap_bind($connection);
        $add('ورود ناشناس', (bool) $bound, $bound ? '' : 'کنترلر bind ناشناس را رد می‌کند؛ حساب سرویس وارد کنید.');
        if (!$bound) {
            return ['ok' => false, 'summary' => 'کنترلر bind ناشناس را رد می‌کند؛ حساب سرویس لازم است.', 'steps' => $steps];
        }
    }

    $configuredFilter = (string) cfg('ldap.user_filter', '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))');
    $filter = ldap_person_filter(str_contains($configuredFilter, '%s')
        ? str_replace('%s', '*', $configuredFilter)
        : '(&(objectClass=user)(sAMAccountName=*))');
    $search = @ldap_search($connection, $baseDn, $filter, ['sAMAccountName'], 0, 5);
    if (!$search) {
        $add('جست‌وجوی کاربران', false, function_exists('ldap_error') ? ldap_error($connection) : 'ناموفق');
        if (function_exists('ldap_unbind')) { @ldap_unbind($connection); }
        return ['ok' => false, 'summary' => 'جست‌وجوی کاربران دامین ناموفق بود.', 'steps' => $steps];
    }
    $entries = @ldap_get_entries($connection, $search);
    $count = (int) ($entries['count'] ?? 0);
    $add('جست‌وجوی کاربران', true, $count . ' کاربر نمونه پیدا شد');
    if (function_exists('ldap_unbind')) { @ldap_unbind($connection); }

    return [
        'ok' => $count > 0,
        'summary' => $count > 0 ? ('اتصال سالم است و ' . $count . ' کاربر نمونه پیدا شد.') : 'اتصال سالم است اما هیچ کاربری پیدا نشد؛ Base DN یا فیلتر را بررسی کنید.',
        'steps' => $steps,
    ];
}

function ldap_sync_all_users(): array
{
    if (!ldap_cfg('enabled', false) || !function_exists('ldap_connect')) {
        throw new RuntimeException('LDAP فعال نیست یا افزونه LDAP PHP نصب نشده است.');
    }
    $host = trim((string) ldap_cfg('host', ''));
    $baseDn = trim((string) ldap_cfg('base_dn', ''));
    if ($host === '') {
        throw new RuntimeException('آدرس کنترلر دامین را تنظیم کنید.');
    }

    $ssl = (bool) ldap_cfg('ssl', false);
    $scheme = $ssl ? 'ldaps://' : 'ldap://';
    $connection = @ldap_connect($scheme . $host, (int) ldap_cfg('port', $ssl ? 636 : 389));
    if (!$connection) {
        throw new RuntimeException('اتصال به کنترلر دامین برقرار نشد.');
    }
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) { @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 8); }
    if (defined('LDAP_OPT_TIMELIMIT')) { @ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 8); }
    $markPhase('service-bind');
    $bindDn = (string) ldap_cfg('bind_dn', '');
    if ($bindDn !== '' && !@ldap_bind($connection, $bindDn, (string) ldap_cfg('bind_password', ''))) {
        throw new RuntimeException('ورود حساب سرویس LDAP ناموفق بود. DN و رمز حساب سرویس را بررسی کنید.');
    }
    if ($bindDn === '' && !@ldap_bind($connection)) {
        throw new RuntimeException('کنترلر دامین اجازهٔ اتصال ناشناس نمی‌دهد؛ یک حساب سرویس با رمز در تنظیمات ثبت کنید.');
    }

    // اگر Base DN خالی باشد، از defaultNamingContext دامین استفاده کن.
    if ($baseDn === '') {
        $rootDse = @ldap_read($connection, '', '(objectClass=*)', ['defaultNamingContext']);
        if ($rootDse) {
            $rootEntries = ldap_get_entries($connection, $rootDse);
            $discovered = trim((string) ($rootEntries[0]['defaultnamingcontext'][0] ?? ''));
            if ($discovered !== '') {
                $baseDn = $discovered;
            }
        }
        if ($baseDn === '') {
            throw new RuntimeException('Base DN خالی است و از دامین هم کشف نشد؛ آن را دستی وارد کنید (مثلاً DC=example,DC=local).');
        }
    }

    $configuredFilter = (string) cfg('ldap.user_filter', '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))');
    $filter = ldap_person_filter(str_contains($configuredFilter, '%s')
        ? str_replace('%s', '*', $configuredFilter)
        : '(&(objectClass=user)(sAMAccountName=*))');
    $attributes = ['distinguishedName', 'displayName', 'mail', 'department', 'sAMAccountName', 'employeeID', 'telephoneNumber', 'userAccountControl', 'memberOf'];
    $seen = [];
    $result = ['created' => 0, 'updated' => 0, 'disabled' => 0, 'skipped' => 0, 'code_conflict' => 0, 'skipped_disabled' => 0, 'errors' => []];
    $cookie = '';
    $processed = 0;

    do {
        // صفحه‌بندی: در PHP < 8.4 با ldap_control_paged_result و در PHP >= 8.4 با LDAP controls.
        if (function_exists('ldap_control_paged_result')) {
            ldap_control_paged_result($connection, 500, false, $cookie);
            $search = @ldap_search($connection, $baseDn, $filter, $attributes);
        } else {
            $pageControl = [
                'oid' => '1.2.840.113556.1.4.319',
                'iscritical' => false,
                'value' => ['size' => 500, 'cookie' => $cookie],
            ];
            $search = @ldap_search($connection, $baseDn, $filter, $attributes, 0, 0, 8, LDAP_DEREF_NEVER, [$pageControl]);
        }
        if (!$search) {
            throw new RuntimeException('جست‌وجوی کاربران دامین ناموفق بود.');
        }
        $entries = ldap_get_entries($connection, $search);
        for ($index = 0; $index < (int) ($entries['count'] ?? 0); $index++) {
            $entry = $entries[$index];
            $username = trim((string) ($entry['samaccountname'][0] ?? ''));
            if ($username === '') {
                $result['skipped']++;
                continue;
            }
            $usernameKey = strtolower($username);
            $seen[$usernameKey] = true;
            $groups = [];
            for ($groupIndex = 0; $groupIndex < (int) ($entry['memberof']['count'] ?? 0); $groupIndex++) {
                $groups[] = (string) ($entry['memberof'][$groupIndex] ?? '');
            }
            $identity = [
                'username' => $username,
                'full_name' => (string) ($entry['displayname'][0] ?? $username),
                'email' => (string) ($entry['mail'][0] ?? ''),
                'department' => (string) ($entry['department'][0] ?? ''),
                'employee_number' => (string) ($entry['employeeid'][0] ?? ''),
                'phone' => (string) ($entry['telephonenumber'][0] ?? ''),
                'groups' => $groups,
            ];
            $existingQuery = db()->prepare('SELECT id, is_active FROM users WHERE username = ? AND auth_source = "ldap" LIMIT 1');
            $existingQuery->execute([$username]);
            $existingUser = $existingQuery->fetch() ?: null;
            $wasExisting = $existingUser !== null;
            // حساب غیرفعال در دامین: اگر در سامانه نیست، اصلاً ایمپورت نمی‌شود.
            $adDisabled = (((int) ($entry['useraccountcontrol'][0] ?? 0)) & 2) === 2;
            if ($adDisabled && !$wasExisting) {
                $result['skipped_disabled']++;
                $processed++;
                continue;
            }
            try {
                $domainUser = upsert_domain_user($identity);
            } catch (RuntimeException $userError) {
                // یک کاربر مشکل‌دار (مثلاً نام کاربری با حساب محلی برخورد دارد) نباید کل همگام‌سازی را متوقف کند.
                $result['skipped']++;
                $processed++;
                if (count($result['errors']) < 50) {
                    $result['errors'][] = $username . ': ' . $userError->getMessage();
                }
                continue;
            }
            if (!empty($domainUser['code_conflict'])) {
                $result['code_conflict']++;
            }
            $disabled = (((int) ($entry['useraccountcontrol'][0] ?? 0)) & 2) === 2;
            $shouldBeActive = !$disabled && (!$wasExisting || (int) ($existingUser['is_active'] ?? 0) === 1);
            db()->prepare('UPDATE users SET is_active = ? WHERE id = ?')->execute([$shouldBeActive ? 1 : 0, (int) $domainUser['id']]);
            $result[$wasExisting ? 'updated' : 'created']++;
            if ($disabled) {
                $result['disabled']++;
            }
            $processed++;
        }
        if (function_exists('ldap_control_paged_result_response')) {
            $estimated = 0;
            ldap_control_paged_result_response($connection, $search, $cookie, $estimated);
        } elseif (function_exists('ldap_parse_result')) {
            // PHP >= 8.4: خواندن cookie صفحه‌بندی از کنترل‌های نتیجه
            $cookie = '';
            $matchedDn = null;
            $errorCode = 0;
            $errorMessage = '';
            $referrals = [];
            $controls = [];
            if (@ldap_parse_result($connection, $search, $errorCode, $matchedDn, $errorMessage, $referrals, $controls) && is_array($controls)) {
                foreach ($controls as $control) {
                    if (is_array($control) && ($control['oid'] ?? '') === '1.2.840.113556.1.4.319' && is_array($control['value'] ?? null)) {
                        $cookie = (string) ($control['value']['cookie'] ?? '');
                        break;
                    }
                }
            }
        } else {
            $cookie = '';
        }
    } while ($cookie !== '');

    if ($processed === 0) {
        throw new RuntimeException('جست‌وجوی دامین هیچ کاربری برنگرداند؛ برای جلوگیری از غیرفعال‌سازی اشتباه، عملیات متوقف شد.');
    }

    // فقط وقتی صفحه‌بندی کامل محتمل است کاربران غایب را غیرفعال کن
    $pagingOk = function_exists('ldap_control_paged_result') || function_exists('ldap_parse_result') || $processed >= 500;
    if ($pagingOk && $processed > 0) {
        $existingUsers = db()->query('SELECT id, username FROM users WHERE auth_source = "ldap"')->fetchAll();
        foreach ($existingUsers as $existingUser) {
            if (!isset($seen[strtolower((string) $existingUser['username'])])) {
                db()->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([(int) $existingUser['id']]);
                $result['disabled']++;
            }
        }
    }
    return $result;
}

