<?php
declare(strict_types=1);

/**
 * کارگر هم‌زمانی نوبت‌ها (T13) — اجرای یک تلاش ثبت برای یک بازهٔ مشخص.
 * استفاده:
 *   php tests/fixtures/request-center-appointment-worker.php <host> <port> <db> <user> <pass> <assigneeId> <actorId> <startsAt> <endsAt> <kind>
 * خروجی یک خط: «RESULT ok id=N» یا «RESULT error <پیام>»
 */

[$host, $port, $name, $user, $pass, $assigneeId, $actorId, $startsAt, $endsAt, $kind] = array_slice($argv, 1);

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// پیوستن به «سد هم‌زمانی»: آخر آماده‌شدنِ همه را سیگنال می‌دهد
$pdo->prepare('UPDATE rc_t13_appt_barrier SET ready_count = ready_count + 1 WHERE id = 1')->execute();
$deadline = microtime(true) + 30.0;
while (true) {
    $flag = (int) $pdo->query('SELECT release_flag FROM rc_t13_appt_barrier WHERE id = 1')->fetchColumn();
    if ($flag === 1) {
        break;
    }
    if (microtime(true) > $deadline) {
        echo "RESULT error barrier_timeout\n";
        exit(0);
    }
    usleep(20_000);
}

require_once dirname(__DIR__, 2) . '/modules/request-center/AppointmentRepository.php';

try {
    $repo = new AppointmentRepository($pdo);
    $result = $repo->create(101, (int) $assigneeId, (string) $startsAt, (string) $endsAt, (string) $kind, 't13-concurrency', (int) $actorId);
    echo 'RESULT ' . ($result['ok'] ? 'ok id=' . $result['id'] : 'error ' . ($result['error'] ?? 'unknown')) . "\n";
} catch (Throwable $exception) {
    echo 'RESULT error exception: ' . $exception->getMessage() . "\n";
}
exit(0);
