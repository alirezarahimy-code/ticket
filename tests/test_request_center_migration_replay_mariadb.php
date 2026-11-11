<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'rc_migration_replay_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');

$GLOBALS['rcMigrationReplayPdo'] = new PDO(
    'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4',
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

function db(): PDO
{
    return $GLOBALS['rcMigrationReplayPdo'];
}

require APP_ROOT . '/system-update.php';

function rc_migration_replay_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    echo 'PASS: ' . $message . PHP_EOL;
}

$stage = (string) ($argv[1] ?? 'postflight');
$legacy035 = '035-request-center-appointments.sql';
$backfill037 = '037-request-center-lifecycle-backfill.sql';

if ($stage === 'preflight') {
    rc_migration_replay_check(
        system_update_migration_applied('1.63', $legacy035),
        'legacy appointment-only 035 remains recorded and is not deleted'
    );
    rc_migration_replay_check(
        !system_update_migration_applied('1.63', $backfill037),
        'new 037 backfill is pending even when the older 035 filename is already recorded'
    );
    exit(0);
}

if ($stage !== 'postflight') {
    throw new InvalidArgumentException('Stage must be preflight or postflight.');
}

rc_migration_replay_check(
    system_update_migration_applied('1.63', $legacy035),
    'legacy 035 registry record remains intact'
);
rc_migration_replay_check(
    system_update_migration_applied('1.63', $backfill037),
    '037 backfill is registered after successful application'
);

$custom = db()->prepare('SELECT name, is_active FROM categories WHERE code = ?');
$custom->execute(['IT-CAT-HW']);
$customCategory = $custom->fetch();
rc_migration_replay_check(is_array($customCategory), 'pre-existing category code is preserved');
rc_migration_replay_check(
    (string) $customCategory['name'] === 'دستهٔ سفارشی مالک',
    'existing category name is not overwritten by the backfill'
);
rc_migration_replay_check(
    (int) $customCategory['is_active'] === 0,
    'existing category activation state is not overwritten by the backfill'
);

$seedCount = (int) db()->query(
    "SELECT COUNT(*) FROM categories WHERE code LIKE 'IT-CAT-%' OR code LIKE 'SUP-CAT-%'"
)->fetchColumn();
rc_migration_replay_check($seedCount >= 16, 'missing standard categories are added without replacing customized ones');

$orphans = (int) db()->query(
    'SELECT COUNT(*) FROM service_catalog s LEFT JOIN categories c ON c.id = s.category_id
     WHERE s.category_id IS NOT NULL AND c.id IS NULL'
)->fetchColumn();
rc_migration_replay_check($orphans === 0, 'service catalog category references remain valid');

echo 'PASS: migration 037 safely backfills a database with an already-recorded 035 on MariaDB '
    . db()->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
