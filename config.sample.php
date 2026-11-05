<?php
declare(strict_types=1);

/* Reference only. install.php creates config.php with fresh random secrets. */
return [
    'app' => [
        'name' => 'سامانه درخواست پشتیبانی',
        'logo' => '',
        'timezone' => 'Asia/Tehran',
        'base_url' => '',
        // کلید رمزگذاری سامانه. با این دستور بسازید:  php -r "echo bin2hex(random_bytes(32));"
        // تا وقتی خالی باشد، رمز حساب سرویس دامین به‌صورت متن ساده در جدول تنظیمات می‌ماند.
        // هشدار: پس از تنظیم، عوض‌کردن این کلید رمزهای رمزشده را غیرقابل بازیابی می‌کند
        // و باید رمز حساب سرویس دامین دوباره در صفحهٔ تنظیمات وارد شود.
        'key' => '',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'persian_ticketing',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'ldap' => [
        'enabled' => false,
        'host' => '',
        'port' => 389,
        'ssl' => false,
        'base_dn' => '',
        'bind_dn' => '',
        'bind_password' => '',
        'domain_suffix' => '',
        'user_filter' => '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))',
        'default_role' => 'user',
        'group_map' => [
            'agent' => [],
            'manager' => [],
            'supervisor' => [],
            'admin' => [],
        ],
    ],
    'security' => [
        // روزهای بی‌فعالیت تا خروج خودکار کاربر. ۰ = غیرفعال (نشست تا یک سال باز می‌ماند).
        // کاربری که هر روز کار می‌کند هیچ‌وقت بیرون انداخته نمی‌شود؛ فقط نشست رهاشده منقضی می‌شود.
        'session_idle_days' => 30,
        'max_upload_mb' => 8,
        'allowed_uploads' => ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'],
        'inventory_token' => '',
    ],
    'food_ticket' => [
        'odbc_driver' => 'Microsoft Access Driver (*.mdb, *.accdb)',
        'web_url' => 'http://127.0.0.1:8080/',
        'sso_key' => '',
    ],
];
