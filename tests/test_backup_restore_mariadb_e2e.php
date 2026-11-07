<?php
declare(strict_types=1);

/**
 * T08: exercise the real backup.php database and file backup/restore functions
 * against disposable MariaDB databases. No production data or paths are used.
 */

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$adminUser = (string) (getenv('CI_DB_USER') ?: 'root');
$adminPass = (string) (getenv('CI_DB_PASS') ?: '');
$tag = bin2hex(random_bytes(4));
$sourceDb = 'rc_t08_source_' . $tag;
$targetDb = 'rc_t08_target_' . $tag;
$appRoot = sys_get_temp_dir() . '/rc-t08-app-' . $tag;
$backupRoot = sys_get_temp_dir() . '/rc-t08-backups-' . $tag;
$restoreRoot = sys_get_temp_dir() . '/rc-t08-files-' . $tag;
$bundle = null;

$admin = new PDO(
    "mysql:host={$host};port={$port};charset=utf8mb4",
    $adminUser,
    $adminPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$GLOBALS['rcT08Config'] = [
    'database.host' => $host,
    'database.port' => (int) $port,
    'database.user' => $adminUser,
    'database.password' => $adminPass,
    'database.name' => $sourceDb,
];
function cfg(string $key, mixed $default = null): mixed
{
    return $GLOBALS['rcT08Config'][$key] ?? $default;
}

function t08_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException('T08 failed: ' . $message);
    }
    echo 'PASS ' . $message . PHP_EOL;
}

function t08_remove_tree(string $path): void
{
    if (!is_dir($path)) {
        if (is_file($path) || is_link($path)) { @unlink($path); }
        return;
    }
    foreach (new DirectoryIterator($path) as $item) {
        if ($item->isDot()) { continue; }
        $target = $item->getPathname();
        if ($item->isDir() && !$item->isLink()) { t08_remove_tree($target); }
        else { @unlink($target); }
    }
    @rmdir($path);
}

try {
    $admin->exec("CREATE DATABASE `{$sourceDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $admin->exec("CREATE DATABASE `{$targetDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $source = new PDO(
        "mysql:host={$host};port={$port};dbname={$sourceDb};charset=utf8mb4",
        $adminUser,
        $adminPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $target = new PDO(
        "mysql:host={$host};port={$port};dbname={$targetDb};charset=utf8mb4",
        $adminUser,
        $adminPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $source->exec('CREATE TABLE t08_probe (id INT NOT NULL PRIMARY KEY, payload VARCHAR(100) NOT NULL) ENGINE=InnoDB');
    $source->exec("INSERT INTO t08_probe (id, payload) VALUES (1, 'source-backup-restore-ok')");
    $target->exec('CREATE TABLE t08_probe (id INT NOT NULL PRIMARY KEY, payload VARCHAR(100) NOT NULL) ENGINE=InnoDB');
    $target->exec("INSERT INTO t08_probe (id, payload) VALUES (99, 'old-target-row')");
    $target->exec('CREATE TABLE t08_target_only (id INT NOT NULL PRIMARY KEY, keep_value VARCHAR(50) NOT NULL) ENGINE=InnoDB');
    $target->exec("INSERT INTO t08_target_only (id, keep_value) VALUES (7, 'preserve')");

    mkdir($appRoot . '/storage/uploads/department', 0750, true);
    mkdir($backupRoot, 0750, true);
    file_put_contents($appRoot . '/storage/uploads/department/attachment.txt', 'upload-payload-restore-ok');
    file_put_contents($appRoot . '/marker.php', "<?php // system-file-restore-ok\n");
    define('APP_ROOT', $appRoot);
    putenv('ITSM_BACKUP_PATH=' . $backupRoot);

    require dirname(__DIR__) . '/backup.php';

    $bundle = create_backup_bundle();
    t08_check(is_dir($bundle['path']), 'create_backup_bundle creates a real backup directory');
    $sqlDump = $bundle['path'] . DIRECTORY_SEPARATOR . $sourceDb . '.sql';
    $uploadsZip = $bundle['path'] . DIRECTORY_SEPARATOR . 'uploads.zip';
    $systemZip = $bundle['path'] . DIRECTORY_SEPARATOR . 'system-files.zip';
    $manifest = $bundle['path'] . DIRECTORY_SEPARATOR . 'manifest.json';
    t08_check(is_file($sqlDump) && filesize($sqlDump) > 0, 'backup contains a non-empty MariaDB SQL dump');
    t08_check(is_file($uploadsZip) && is_file($systemZip) && is_file($manifest), 'backup contains uploads, system-files and manifest artifacts');

    // The restore action creates a safety bundle before importing the selected backup.
    $GLOBALS['rcT08Config']['database.name'] = $targetDb;
    $safetyBundle = create_backup_bundle();
    t08_check(is_dir($safetyBundle['path']), 'safety backup is created for the target database before restore');

    $target->exec('DROP TABLE t08_probe');
    restore_sql_dump([
        'error' => UPLOAD_ERR_OK,
        'name' => $sourceDb . '.sql',
        'size' => filesize($sqlDump),
        'tmp_name' => $sqlDump,
    ]);
    $restored = $target->query('SELECT payload FROM t08_probe WHERE id = 1')->fetchColumn();
    t08_check($restored === 'source-backup-restore-ok', 'restore_sql_dump restores actual MariaDB table data');
    t08_check((int) $target->query('SELECT COUNT(*) FROM t08_probe')->fetchColumn() === 1, 'restore_sql_dump replaces the existing same-name table contents');
    t08_check($target->query('SELECT keep_value FROM t08_target_only WHERE id = 7')->fetchColumn() === 'preserve', 'restore leaves unrelated target tables untouched');

    $GLOBALS['rcT08Config']['database.name'] = $sourceDb;
    $restoredUploads = restore_upload_archive([
        'error' => UPLOAD_ERR_OK,
        'name' => 'uploads.zip',
        'size' => filesize($uploadsZip),
        'tmp_name' => $uploadsZip,
    ]);
    t08_check($restoredUploads === 1, 'restore_upload_archive restores one upload');
    t08_check(
        file_get_contents($appRoot . '/storage/uploads/department/attachment.txt') === 'upload-payload-restore-ok',
        'restored upload content matches the source file'
    );

    mkdir($restoreRoot, 0750, true);
    $zip = new ZipArchive();
    t08_check($zip->open($systemZip) === true, 'system-files.zip can be opened for the documented manual file restore');
    try {
        t08_check($zip->extractTo($restoreRoot) === true, 'system-files.zip extracts into a separate restore target');
    } finally {
        $zip->close();
    }
    t08_check(
        is_file($restoreRoot . '/marker.php')
            && str_contains((string) file_get_contents($restoreRoot . '/marker.php'), 'system-file-restore-ok'),
        'system file archive restores the marker file content'
    );

    echo 'PASS: T08 actual DB/uploads/system-files backup and restore on MariaDB ' .
        $admin->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
} finally {
    try { $admin->exec("DROP DATABASE IF EXISTS `{$sourceDb}`"); } catch (Throwable) {}
    try { $admin->exec("DROP DATABASE IF EXISTS `{$targetDb}`"); } catch (Throwable) {}
    t08_remove_tree($appRoot);
    t08_remove_tree($backupRoot);
    t08_remove_tree($restoreRoot);
}
