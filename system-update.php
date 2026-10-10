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

/** پک را بررسی می‌کند و manifest را برمی‌گرداند. پرتاب خطا یعنی پک معتبر نیست. */
function system_update_read_package(string $zipPath): array
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('افزونهٔ ZipArchive در PHP فعال نیست.');
    }
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        throw new RuntimeException('فایل ZIP باز نشد.');
    }
    try {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if ($name === '' || str_contains($name, '..') || str_starts_with($name, '/') || str_contains($name, '\\')) {
                throw new RuntimeException('مسیر نامعتبر در پک: ' . $name);
            }
            if (!preg_match('#^(manifest\.json|migrations/[^/]+\.sql|files/.+)$#', $name) && !str_ends_with($name, '/')) {
                throw new RuntimeException('فایل خارج از ساختار پک: ' . $name);
            }
        }
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
        if (version_compare($version, $current, '<=')) {
            throw new RuntimeException('نسخهٔ پک (' . $version . ') از نسخهٔ نصب‌شده (' . $current . ') جدیدتر نیست.');
        }
        $migrations = $manifest['migrations'] ?? [];
        if (!is_array($migrations)) {
            throw new RuntimeException('فهرست مهاجرت‌ها در manifest معتبر نیست.');
        }
        foreach ($migrations as $migration) {
            if (!preg_match('/^\d{3}-[a-z0-9-]+\.sql$/', (string) $migration) || $zip->locateName('migrations/' . $migration) === false) {
                throw new RuntimeException('مهاجرت نامعتبر یا فایل آن در پک نیست: ' . $migration);
            }
        }
        return ['manifest' => $manifest, 'version' => $version, 'migrations' => array_values($migrations)];
    } finally {
        $zip->close();
    }
}

/** فایل ZIP را اعتبارسنجی می‌کند، بکاپ می‌گیرد، مهاجرت‌ها را اجرا می‌کند و فایل‌ها را کپی می‌کند. */
function system_update_apply(array $upload, array $actor): array
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
    $package = system_update_read_package((string) $upload['tmp_name']);
    $version = $package['version'];

    $mysqlBinary = backup_mysql_binary('mysql');
    if ($mysqlBinary === null) {
        throw new RuntimeException('mysql پیدا نشد؛ مسیر ITSM_MYSQL_PATH یا XAMPP را بررسی کنید.');
    }

    // ۱. بکاپ قبل از هر تغییر
    $backup = create_backup_bundle();

    // ۲. استخراج در پوشهٔ موقت
    $stage = APP_ROOT . '/storage/updates/' . $version . '-' . date('YmdHis');
    if (!is_dir($stage) && !mkdir($stage, 0775, true) && !is_dir($stage)) {
        throw new RuntimeException('ساخت پوشهٔ موقت به‌روزرسانی ممکن نیست.');
    }
    $zip = new ZipArchive();
    $zip->open((string) $upload['tmp_name']);
    $zip->extractTo($stage);
    $zip->close();

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

    save_audit((int) ($actor['id'] ?? 0), 'system_updated', null, ['version' => $version, 'migrations' => $applied, 'files' => $copied, 'backup' => $backup['name']]);
    return ['version' => $version, 'migrations' => $applied, 'files' => $copied, 'backup' => $backup['name']];
}
