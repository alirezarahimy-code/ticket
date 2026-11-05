<?php
declare(strict_types=1);

/*
 * به‌روزرسانی سامانه از فایل ZIP.
 * ساختار پک:
 *   manifest.json            نسخه، توضیح، فهرست مهاجرت‌ها (به ترتیب)
 *   migrations/NNN-name.sql  مهاجرت‌های idempotent و افزایشی
 *   files/...                فایل‌هایی که روی سامانه کپی می‌شوند (شامل VERSION و راهنما/دسترسی‌ها)
 * قواعد: بکاپ قبل از اعمال، ثبت هر مهاجرت در schema_updates، عدم بازنویسی config/storage/uploads.
 */

const SYSTEM_UPDATE_MAX_BYTES = 200 * 1024 * 1024;
const SYSTEM_UPDATE_PROTECTED = ['config.php', '.env', 'storage', 'uploads'];

function system_update_current_version(): string
{
    $file = APP_ROOT . '/VERSION';
    $version = is_file($file) ? trim((string) file_get_contents($file)) : '';
    return $version !== '' ? $version : '0';
}

function system_update_ensure_table(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS schema_updates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        version VARCHAR(32) NOT NULL,
        migration VARCHAR(190) NOT NULL,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_schema_update (version, migration)
    ) DEFAULT CHARSET=utf8mb4');
}

function system_update_migration_applied(string $version, string $migration): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM schema_updates WHERE version = ? AND migration = ?');
    $stmt->execute([$version, $migration]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * آیا نصب این بسته روی نسخهٔ فعلی، سامانه را نیمه‌کاره می‌گذارد؟
 *
 * `requires_version` در manifest یعنی «این بسته فقط فایل‌هایی را دارد که نسبت به آن
 * نسخه عوض شده‌اند». اگر نسخهٔ نصب‌شده پایین‌تر باشد، فایل‌های نسخه‌های میانی در بسته
 * نیستند و نصب، بی‌آنکه هیچ خطایی دیده شود، سامانه را خراب می‌کند. تا پیش از این،
 * requires_version فقط یک یادداشت توضیحی بود و هیچ‌جا بررسی نمی‌شد.
 *
 * @param string $current   نسخهٔ نصب‌شده
 * @param mixed  $requires  مقدار requires_version در manifest (ممکن است نباشد)
 * @param string $mode      upgrade | reinstall | downgrade
 * @param bool   $ignore    ادمین صریحاً «نادیده بگیر» را تیک زده است
 * @return string|null      متن خطا، یا null اگر نصب بی‌خطر است
 */
function system_update_requires_violation(string $current, mixed $requires, string $mode, bool $ignore = false): ?string
{
    if ($ignore || $mode === 'downgrade') {
        return null;
    }
    $requires = trim((string) ($requires ?? ''));
    if ($requires === '' || !preg_match('/^\d+(\.\d+)*$/', $requires)) {
        return null; // بسته‌های قدیمی که این کلید را ندارند، مثل قبل رفتار می‌کنند.
    }
    if (version_compare($current, $requires, '>=')) {
        return null;
    }
    return 'این بسته افزایشی است و فقط روی نسخهٔ ' . $requires . ' به بعد نصب می‌شود، '
        . 'ولی نسخهٔ نصب‌شده ' . $current . ' است. نصب آن فایل‌های نسخه‌های میانی را جا می‌اندازد '
        . 'و سامانه نیمه‌کاره می‌ماند. یا بسته‌های میانی را به ترتیب نصب کنید، '
        . 'یا از بستهٔ کامل سورس استفاده کنید. '
        . 'اگر مطمئن هستید، گزینهٔ «پیش‌نیاز نسخهٔ پک را نادیده بگیر» را تیک بزنید.';
}

/**
 * اعتبارسنجی نام ورودی ZIP با درنظرگرفتن محدودیت‌های Windows و Linux.
 * فقط مسیرهای نسبی POSIX داخل ساختار مجاز بسته پذیرفته می‌شوند.
 */
function system_update_archive_path_is_safe(string $name): bool
{
    if ($name === '' || str_contains($name, "\0") || str_contains($name, '\\\\')
        || str_starts_with($name, '/') || preg_match('/^[A-Za-z]:/', $name)) {
        return false;
    }
    $directory = str_ends_with($name, '/');
    $path = $directory ? substr($name, 0, -1) : $name;
    if ($path === '' || str_contains($path, '//')) { return false; }
    foreach (explode('/', $path) as $segment) {
        if ($segment === '' || $segment === '.' || $segment === '..'
            || preg_match('/[<>:"|?*\\x00-\\x1F]/', $segment)
            || preg_match('/[. ]$/', $segment)
            || preg_match('/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\\..*)?$/i', $segment)) {
            return false;
        }
    }
    return (bool) preg_match('#^(manifest\\.json|migrations/[^/]+\\.sql|files/.+)$#', $path)
        || in_array($path, ['migrations', 'files'], true);
}

/** همه ورودی‌ها پیش از استخراج بررسی می‌شوند. */
function system_update_validate_zip_entries(ZipArchive $zip): void
{
    if ($zip->numFiles < 1 || $zip->numFiles > 5000) {
        throw new RuntimeException('تعداد فایل‌های ZIP خارج از محدوده مجاز است.');
    }
    $seen = [];
    $totalBytes = 0;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = (string) $zip->getNameIndex($i);
        if (!system_update_archive_path_is_safe($name)) {
            throw new RuntimeException('مسیر نامعتبر در پک: ' . $name);
        }
        $key = strtolower(rtrim($name, '/'));
        if (isset($seen[$key])) {
            throw new RuntimeException('نام تکراری یا ناسازگار با Windows در ZIP: ' . $name);
        }
        $seen[$key] = true;
        $stat = $zip->statIndex($i);
        if (!is_array($stat)) {
            throw new RuntimeException('اطلاعات ورودی ZIP قابل خواندن نیست: ' . $name);
        }
        $size = max(0, (int) ($stat['size'] ?? 0));
        $totalBytes += $size;
        if ($size > 128 * 1024 * 1024 || $totalBytes > 512 * 1024 * 1024) {
            throw new RuntimeException('اندازه استخراج ZIP از سقف ایمنی عبور می‌کند.');
        }
        if (method_exists($zip, 'getExternalAttributesIndex')) {
            $opsys = 0; $attributes = 0;
            if ($zip->getExternalAttributesIndex($i, $opsys, $attributes)) {
                $unixMode = ($attributes >> 16) & 0170000;
                if ($opsys === ZipArchive::OPSYS_UNIX && $unixMode === 0120000) {
                    throw new RuntimeException('لینک نمادین در بسته به‌روزرسانی مجاز نیست: ' . $name);
                }
            }
        }
    }
    foreach (array_keys($seen) as $key) {
        $parts = explode('/', $key); array_pop($parts); $parent = '';
        foreach ($parts as $part) {
            $parent = $parent === '' ? $part : $parent . '/' . $part;
            if (isset($seen[$parent])) {
                throw new RuntimeException('تعارض فایل/پوشه در ساختار ZIP: ' . $key);
            }
        }
    }
}

/** صحت فایل‌های stage و hashهای اختیاری manifest. */
function system_update_verify_staged_package(string $stage, array $manifest): void
{
    $stageReal = realpath($stage);
    if (!is_string($stageReal) || !is_file($stageReal . DIRECTORY_SEPARATOR . 'manifest.json')) {
        throw new RuntimeException('بسته پس از استخراج کامل نیست یا manifest ندارد.');
    }
    $files = $manifest['files'] ?? [];
    if (!is_array($files)) { throw new RuntimeException('فهرست files در manifest معتبر نیست.'); }
    $seen = [];
    foreach ($files as $relative) {
        $relative = (string) $relative;
        if ($relative === '' || !system_update_archive_path_is_safe('files/' . $relative)) {
            throw new RuntimeException('مسیر فایل در manifest نامعتبر است: ' . $relative);
        }
        $key = strtolower($relative);
        if (isset($seen[$key])) { throw new RuntimeException('فایل تکراری در manifest: ' . $relative); }
        $seen[$key] = true;
        $candidate = $stageReal . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $real = realpath($candidate);
        $filesRoot = realpath($stageReal . DIRECTORY_SEPARATOR . 'files');
        if (!is_string($real) || !is_string($filesRoot) || !is_file($real)
            || !str_starts_with($real, $filesRoot . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('فایل اعلام‌شده در manifest موجود نیست یا از stage خارج شده است: ' . $relative);
        }
    }
    $hashes = $manifest['sha256'] ?? [];
    if ($hashes !== [] && !is_array($hashes)) { throw new RuntimeException('بخش sha256 در manifest معتبر نیست.'); }
    foreach ($hashes as $relative => $expected) {
        $relative = (string) $relative; $expected = strtolower((string) $expected);
        if (!isset($seen[strtolower($relative)]) || !preg_match('/^[a-f0-9]{64}$/', $expected)) {
            throw new RuntimeException('تعریف hash نامعتبر در manifest: ' . $relative);
        }
        $file = $stageReal . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $actual = hash_file('sha256', $file);
        if (!is_string($actual) || !hash_equals($expected, strtolower($actual))) {
            throw new RuntimeException('تطبیق SHA-256 فایل ناموفق است: ' . $relative);
        }
    }
}

/** استخراج کنترل‌شده؛ هر خطا مانع اجرای migration می‌شود. */
function system_update_extract_package(string $zipPath, string $stage, array $manifest): void
{
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) { throw new RuntimeException('بازکردن مجدد ZIP برای استخراج ناموفق بود.'); }
    try {
        system_update_validate_zip_entries($zip);
        if (!$zip->extractTo($stage)) { throw new RuntimeException('استخراج ZIP ناموفق بود؛ هیچ مهاجرتی اجرا نشد.'); }
    } finally {
        $zip->close();
    }
    system_update_verify_staged_package($stage, $manifest);
}

/** پک را بررسی می‌کند و manifest را برمی‌گرداند. پرتاب خطا یعنی پک معتبر نیست. */
function system_update_read_package(string $zipPath, bool $allowDowngrade = false, bool $ignoreRequires = false): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('افزونهٔ ZipArchive در PHP فعال نیست.');
    }
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        throw new RuntimeException('فایل ZIP باز نشد.');
    }
    try {
        system_update_validate_zip_entries($zip);
        $manifestRaw = $zip->getFromName('manifest.json');
        if ($manifestRaw === false) {
            throw new RuntimeException('پک فاقد manifest.json است.');
        }
        $manifest = json_decode($manifestRaw, true);
        if (!is_array($manifest) || empty($manifest['version'])) {
            throw new RuntimeException('manifest.json معتبر نیست یا نسخه ندارد.');
        }
        $version = (string) $manifest['version'];
        if (!preg_match('/^\d+(\.\d+)*$/', $version)) {
            throw new RuntimeException('شمارهٔ نسخهٔ پک معتبر نیست.');
        }
        $current = system_update_current_version();
        $cmp = version_compare($version, $current);
        if ($cmp > 0) {
            $mode = 'upgrade';
        } elseif ($cmp === 0) {
            // نصب مجدد همان نسخه (ترمیم فایل‌ها)؛ مهاجرت‌ها با schema_updates تکراری نمی‌شوند.
            $mode = 'reinstall';
        } else {
            if (!$allowDowngrade) {
                throw new RuntimeException('نسخهٔ پک (' . $version . ') قدیمی‌تر از نسخهٔ نصب‌شده (' . $current . ') است. برای برگشت به نسخهٔ قدیمی‌تر، گزینهٔ تأیید برگشت را فعال کنید.');
            }
            $mode = 'downgrade';
        }
        $requiresError = system_update_requires_violation($current, $manifest['requires_version'] ?? null, $mode, $ignoreRequires);
        if ($requiresError !== null) {
            throw new RuntimeException($requiresError);
        }

        $migrations = $manifest['migrations'] ?? [];
        if (!is_array($migrations)) {
            throw new RuntimeException('فهرست مهاجرت‌ها در manifest معتبر نیست.');
        }
        $migrationNames = [];
        foreach ($migrations as $migration) {
            $migration = (string) $migration;
            if (!preg_match('/^\\d{3}-[a-z0-9-]+\\.sql$/', $migration)
                || $zip->locateName('migrations/' . $migration) === false
                || isset($migrationNames[strtolower($migration)])) {
                throw new RuntimeException('مهاجرت نامعتبر، تکراری یا فایل آن در پک نیست: ' . $migration);
            }
            $migrationNames[strtolower($migration)] = true;
        }
        $files = $manifest['files'] ?? [];
        if (!is_array($files)) { throw new RuntimeException('فهرست فایل‌ها در manifest معتبر نیست.'); }
        foreach ($files as $relative) {
            $relative = (string) $relative;
            if ($relative === '' || !system_update_archive_path_is_safe('files/' . $relative)
                || $zip->locateName('files/' . $relative) === false) {
                throw new RuntimeException('فایل اعلام‌شده در manifest نامعتبر یا مفقود است: ' . $relative);
            }
        }
        return ['manifest' => $manifest, 'version' => $version, 'mode' => $mode, 'current' => $current, 'migrations' => array_values($migrations)];
    } finally {
        $zip->close();
    }
}

/** فایل ZIP را اعتبارسنجی می‌کند، بکاپ می‌گیرد، مهاجرت‌ها را اجرا می‌کند و فایل‌ها را کپی می‌کند. */

/** ۱.۶۲.۴۱ — اسنپشات ZIP از فایلهای سامانه، مستقل از توابع backup.php. */
function system_update_files_snapshot(string $destination): array
{
    if ($destination === '' || !class_exists('ZipArchive')) { return []; }
    $root = APP_ROOT;
    $skip = ['storage','uploads','downloads','.git','node_modules','vendor','.cache','preview','updates','temp','tmp'];
    $path = $destination . DIRECTORY_SEPARATOR . 'system-files-before-update.zip';
    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { return []; }
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $rel = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
        if (in_array(explode('/', $rel)[0], $skip, true) || $item->isDir()) { continue; }
        if ($item->getSize() > 25 * 1024 * 1024) { continue; }
        if ($zip->addFile($item->getPathname(), $rel)) { $count++; }
    }
    $zip->addFromString('BACKUP-INFO.txt',
        "بکاپ فایلها قبل از آپدیت\nتاریخ: " . date('Y-m-d H:i:s') . "\nفایلها: {$count}\n");
    $zip->close();
    return ['file' => basename($path), 'files' => $count];
}

function system_update_apply(array $upload, array $actor, bool $allowDowngrade = false, bool $ignoreRequires = false): array
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('فایل به‌روزرسانی آپلود نشد. ZIP را انتخاب کنید.');
    }
    if (strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION)) !== 'zip') {
        throw new RuntimeException('فایل به‌روزرسانی باید ZIP باشد.');
    }
    if ((int) $upload['size'] <= 0 || (int) $upload['size'] > SYSTEM_UPDATE_MAX_BYTES) {
        throw new RuntimeException('حجم فایل به‌روزرسانی مجاز نیست.');
    }
    $package = system_update_read_package((string) $upload['tmp_name'], $allowDowngrade, $ignoreRequires);
    $version = $package['version'];

    $mysqlBinary = backup_mysql_binary('mysql');
    if ($mysqlBinary === null) {
        throw new RuntimeException('mysql پیدا نشد؛ مسیر ITSM_MYSQL_PATH یا XAMPP را بررسی کنید.');
    }

    // ۱. بکاپ قبل از هر تغییر (دیتابیس + uploads + فایلهای سامانه)
    $backup = create_backup_bundle();
    // ۱.۶۲.۴۱ — پشتیبان مستقل از فایلها، حتی اگر نسخهٔ قدیمی backup.php لود شده باشد.
    $fileSnapshot = system_update_files_snapshot($backup['path'] ?? '');
    if ($fileSnapshot) { $backup['files_snapshot'] = $fileSnapshot; }

    // ۲. استخراج در پوشهٔ موقت
    $stageRoot = APP_ROOT . '/storage/updates';
    if (!is_dir($stageRoot) && !mkdir($stageRoot, 0700, true) && !is_dir($stageRoot)) {
        throw new RuntimeException('ساخت ریشهٔ stage به‌روزرسانی ممکن نیست.');
    }
    $stage = $stageRoot . '/' . $version . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
    if (!mkdir($stage, 0700, true)) {
        throw new RuntimeException('ساخت پوشهٔ یکتای stage به‌روزرسانی ممکن نیست.');
    }
    system_update_extract_package((string) $upload['tmp_name'], $stage, $package['manifest']);

    // ۳. مهاجرت‌ها به ترتیب؛ هر کدام فقط یک بار ثبت می‌شود
    system_update_ensure_table();
    $applied = [];
    foreach ($package['migrations'] as $migration) {
        if (system_update_migration_applied($version, (string) $migration)) {
            continue;
        }
        try {
            backup_run_process($mysqlBinary, [
                '--host=' . (string) cfg('database.host', '127.0.0.1'),
                '--port=' . (int) cfg('database.port', 3306),
                '--user=' . (string) cfg('database.user', ''),
                '--default-character-set=utf8mb4',
                (string) cfg('database.name', 'persian_ticketing'),
            ], $stage . '/migrations/' . $migration);
        } catch (RuntimeException $migrationError) {
            // فقط خطوط ERROR نمایش داده شود؛ هشدارهای ابزار MySQL حذف می‌شوند
            $lines = array_filter(explode("\n", $migrationError->getMessage()), static fn(string $line): bool => str_contains($line, 'ERROR'));
            $detail = $lines ? implode(' | ', $lines) : $migrationError->getMessage();
            throw new RuntimeException('مهاجرت ' . $migration . ' ناموفق بود (بقیهٔ تغییرات اعمال نشد): ' . $detail);
        }
        db()->prepare('INSERT INTO schema_updates (version, migration) VALUES (?, ?)')->execute([$version, (string) $migration]);
        $applied[] = (string) $migration;
    }

    // ۴. کپی فایل‌ها (بدون مسیرهای محافظت‌شده)
    $copied = 0;
    $filesDir = $stage . '/files';
    if (is_dir($filesDir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filesDir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($filesDir))), '/');
            $top = explode('/', $relative)[0];
            if (in_array($top, SYSTEM_UPDATE_PROTECTED, true)) {
                continue;
            }
            $target = APP_ROOT . '/' . $relative;
            if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true)) {
                throw new RuntimeException('ساخت پوشه برای ' . $relative . ' ممکن نیست.');
            }
            if (!copy($file->getPathname(), $target)) {
                throw new RuntimeException('کپی فایل ' . $relative . ' ممکن نیست.');
            }
            $copied++;
        }
    }

    file_put_contents(APP_ROOT . '/VERSION', $version . "\n");

    save_audit((int) ($actor['id'] ?? 0), 'system_updated', null, ['version' => $version, 'mode' => $package['mode'], 'from' => $package['current'], 'migrations' => $applied, 'files' => $copied, 'backup' => $backup['name']]);
    return ['version' => $version, 'mode' => $package['mode'], 'from' => $package['current'], 'migrations' => $applied, 'files' => $copied, 'backup' => $backup['name']];
}
