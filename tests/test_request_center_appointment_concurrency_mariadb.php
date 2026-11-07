<?php
declare(strict_types=1);

/**
 * T13: هم‌زمانی ثبت نوبت روی MariaDB واقعی (اجرا روی هر دو نسخهٔ CI).
 *
 * چند کارگر PHP مستقل در یک «سد هم‌زمانی» صف می‌کشند و سپس همزمان ثبت نوبت
 * روی یک کارشناس را می‌آزمایند. نتیجهٔ درست: در بازهٔ هم‌پوشان، دقیقاً یک برنده.
 * همچنین سناریوهای «بازه‌های غیرهم‌پوشان» (همه موفق) و «لغو سپس ثبت دوباره»
 * (باز هم دقیقاً یک برنده) آزموده می‌شوند.
 *
 * این آزمون روی مخزن واقعی کد محصولی (AppointmentRepository::create) اجرا می‌شود،
 * نه یک بازنویسی موازی. بنابراین اثبات‌کنندهٔ رفتار قفل ردیف والد است.
 * فقط داخل CI یا با CI_DB_NAME تنظیم‌شده اجرا می‌شود؛ هیچ دادهٔ عملیاتی دست‌کاری نمی‌شود.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: '');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');

if ($name === '' && getenv('GITHUB_ACTIONS') !== 'true') {
    fwrite(STDERR, "refusing to run without CI_DB_NAME or GITHUB_ACTIONS\n");
    exit(2);
}
if ($name === '') {
    $name = 'persian_ticketing_ci';
}

function t13_check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('T13 failed: ' . $message);
    }
    echo "PASS {$message}\n";
}

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

try {
    $pdo->exec('DROP TABLE IF EXISTS rc_t13_appt_barrier');
    $pdo->exec('CREATE TABLE rc_t13_appt_barrier (
        id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
        ready_count INT UNSIGNED NOT NULL DEFAULT 0,
        release_flag TINYINT UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=InnoDB');
    $pdo->exec('INSERT INTO rc_t13_appt_barrier (id, ready_count, release_flag) VALUES (1, 0, 0)');

    $tag = strtoupper(bin2hex(random_bytes(4)));
    $agentUser = 'rc-t13-' . strtolower($tag) . '-agent';
    $actorUser = 'rc-t13-' . strtolower($tag) . '-actor';
    $userInsert = $pdo->prepare('INSERT INTO users (username, full_name, role, is_active) VALUES (?, ?, ?, 1)');
    $userInsert->execute([$agentUser, 'RC T13 Agent', 'agent']);
    $assigneeId = (int) $pdo->lastInsertId();
    $userInsert->execute([$actorUser, 'RC T13 Actor', 'agent']);
    $actorId = (int) $pdo->lastInsertId();

    $workerPath = __DIR__ . '/fixtures/request-center-appointment-worker.php';
    $phpBinary = PHP_BINARY;
    $env = ['host' => $host, 'port' => $port, 'name' => $name, 'user' => $user, 'pass' => $pass];

    /** $windows: آرایه‌ای از [startsAt, endsAt, kind] — همه روی همان کارشناس */
    $race = static function (array $windows) use ($pdo, $phpBinary, $workerPath, $assigneeId, $actorId, $env): array {
        $count = count($windows);
        $workers = [];
        foreach ($windows as $idx => $w) {
            [$s, $e, $k] = $w;
            $cmd = escapeshellarg($phpBinary) . ' ' . escapeshellarg($workerPath) . ' '
                . escapeshellarg($env['host']) . ' ' . escapeshellarg($env['port']) . ' '
                . escapeshellarg($env['name']) . ' ' . escapeshellarg($env['user']) . ' ' . escapeshellarg($env['pass']) . ' '
                . escapeshellarg((string) $assigneeId) . ' ' . escapeshellarg((string) $actorId) . ' '
                . escapeshellarg((string) $s) . ' ' . escapeshellarg((string) $e) . ' ' . escapeshellarg((string) $k);
            $pipes = [];
            $proc = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (!is_resource($proc)) {
                throw new RuntimeException('failed to spawn worker');
            }
            $workers[] = ['proc' => $proc, 'pipes' => $pipes];
        }
        $deadline = microtime(true) + 60.0;
        while (true) {
            $ready = (int) $pdo->query('SELECT ready_count FROM rc_t13_appt_barrier WHERE id = 1')->fetchColumn();
            if ($ready >= $count) {
                break;
            }
            if (microtime(true) > $deadline) {
                throw new RuntimeException('workers did not become ready within 60s (ready=' . $ready . '/' . $count . ')');
            }
            usleep(20_000);
        }
        $pdo->exec('UPDATE rc_t13_appt_barrier SET release_flag = 1 WHERE id = 1');
        $results = [];
        foreach ($workers as $w) {
            $out = trim((string) stream_get_contents($w['pipes'][1]));
            fclose($w['pipes'][0]);
            fclose($w['pipes'][1]);
            fclose($w['pipes'][2]);
            proc_close($w['proc']);
            $results[] = $out;
        }
        // بازنشانی سد برای دور بعدی
        $pdo->exec('UPDATE rc_t13_appt_barrier SET ready_count = 0, release_flag = 0 WHERE id = 1');
        return $results;
    };

    $analyze = static function (array $lines): array {
        $ok = count(array_filter($lines, static fn (string $l): bool => str_contains($l, 'ok id=')));
        $overlap = count(array_filter($lines, static fn (string $l): bool => str_contains($l, 'تداخل')));
        $unexpected = array_filter($lines, static fn (string $l): bool => !str_contains($l, 'ok id=') && !str_contains($l, 'تداخل'));
        $deadlock = count(array_filter($lines, static fn (string $l): bool => preg_match('/deadlock|lock wait|1213/i', $l) === 1));
        return ['ok' => $ok, 'overlap' => $overlap, 'unexpected' => $unexpected, 'deadlock' => $deadlock];
    };

    $base = new DateTimeImmutable('tomorrow 09:00:00');

    // ——— سناریو A: ۱۶ کارگر، یک کارشناس، بازهٔ یکسان ———
    $sameStart = $base->format('Y-m-d H:i:s');
    $sameEnd = $base->modify('+60 minutes')->format('Y-m-d H:i:s');
    $linesA = $race(array_fill(0, 16, [$sameStart, $sameEnd, 'work']));
    foreach ($linesA as $line) {
        fwrite(STDERR, 'workerA: ' . $line . "\n");
    }
    $a = $analyze($linesA);
    t13_check($a['ok'] === 1, "exactly one winner books the identical window (got {$a['ok']})");
    t13_check($a['overlap'] === 15, "the other 15 racers receive a clean overlap rejection (got {$a['overlap']})");
    t13_check($a['unexpected'] === [], 'no unexpected worker failures: ' . implode(' | ', $a['unexpected']));
    t13_check($a['deadlock'] === 0, 'zero lock-wait deadlock/cancellation events');
    $p1 = $pdo->prepare("SELECT COUNT(*) FROM request_center_appointments WHERE assignee_id = ? AND status = 'planned' AND starts_at = ?");
    $p1->execute([$assigneeId, $sameStart]);
    t13_check((int) $p1->fetchColumn() === 1, 'database contains exactly one planned appointment for the racing window');

    // ——— سناریو B: بازه‌های غیرهم‌پوشان — همه باید موفق شوند ———
    // نکته: روزِ جدا (دو روز بعد) تا ردیفِ برندهٔ سناریو A (فردا ۰۹:۰۰، planned) با B
    // تداخل پیدا نکند؛ ردیف‌های A تا پایان آزمایش حفظ می‌شوند.
    $baseB = $base->modify('+2 days');
    $linesB = [];
    $windowsB = [];
    for ($i = 0; $i < 16; $i++) {
        $s = $baseB->modify('+' . ($i * 70) . ' minutes')->format('Y-m-d H:i:s');
        $e = $baseB->modify('+' . ($i * 70 + 60) . ' minutes')->format('Y-m-d H:i:s');
        $windowsB[] = [$s, $e, 'work'];
    }
    $linesB = $race($windowsB);
    foreach ($linesB as $line) {
        fwrite(STDERR, 'workerB: ' . $line . "\n");
    }
    $b = $analyze($linesB);
    t13_check($b['ok'] === 16, "all 16 non-overlapping appointments succeed (got {$b['ok']})");
    t13_check($b['unexpected'] === [], 'no unexpected failures in scenario B: ' . implode(' | ', $b['unexpected']));

    // ——— سناریو C: لغو برندهٔ سناریو A سپس ثبت دوبارهٔ هم‌زمان (۶ کارگر) ———
    $winner = $pdo->prepare("SELECT id FROM request_center_appointments WHERE assignee_id = ? AND starts_at = ? AND status = 'planned' ORDER BY id DESC LIMIT 1");
    $winner->execute([$assigneeId, $sameStart]);
    $winnerId = (int) ($winner->fetchColumn() ?: 0);
    t13_check($winnerId > 0, 'winner appointment from scenario A is found for cancellation');
    $pdo->prepare("UPDATE request_center_appointments SET status = 'cancelled' WHERE id = ?")->execute([$winnerId]);
    $linesC = $race(array_fill(0, 6, [$sameStart, $sameEnd, 'work']));
    foreach ($linesC as $line) {
        fwrite(STDERR, 'workerC: ' . $line . "\n");
    }
    $c = $analyze($linesC);
    t13_check($c['ok'] === 1, 'after cancellation exactly one of 6 racers wins again (got ' . $c['ok'] . ')');
    t13_check($c['overlap'] === 5, 'the rest receive overlap rejection (got ' . $c['overlap'] . ')');
    t13_check($c['unexpected'] === [], 'no unexpected failures in scenario C: ' . implode(' | ', $c['unexpected']));

    echo "ALL-T13-APPOINTMENT-CONCURRENCY-SCENARIOS-PASSED\n";

    // ——— پاک‌سازی داده‌های آزمایشی ———
    $cleanup = $pdo->prepare('DELETE FROM request_center_appointments WHERE created_by = ?');
    $cleanup->execute([$actorId]);
    $pdo->exec("DELETE FROM users WHERE username IN ('$agentUser', '$actorUser')");
    $pdo->exec('DROP TABLE IF EXISTS rc_t13_appt_barrier');
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . "\n");
    try { $pdo->exec('DROP TABLE IF EXISTS rc_t13_appt_barrier'); } catch (Throwable) {}
    exit(1);
}
