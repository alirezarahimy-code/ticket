<?php
declare(strict_types=1);

/**
 * POST handler for Request Center expert appointments (calendar page).
 * Runs only inside the calendar branch of modules/request-center/Pages/index.php
 * (which creates $user, $repo, $workspace, $agentView and $escape).
 * Redirects back to the calendar on completion; never renders output.
 *
 * Phase 4: holiday/weekend guard (Persian calendar; weekend from sla_business_hours()),
 * assignee allowlist, and notifications/audit for create/cancel/complete.
 */

require_once dirname(__DIR__) . '/AppointmentRepository.php';

$rcCanSchedule = user_can($user, 'ticket.edit') || user_can($user, 'queue.view')
    || user_can($user, 'ticket.view_unit') || user_can($user, 'ticket.view_all')
    || user_can($user, 'supervisor.panel') || user_is_primary_admin($user);

if (!$rcCanSchedule) {
    http_response_code(403);
    exit('دسترسی به برنامه‌ریزی رسیدگی مجاز نیست.');
}

require_csrf();

$rcAppointments = new AppointmentRepository(db());
$rcElevated = user_can($user, 'supervisor.panel') || user_is_primary_admin($user);
$rcActorId = (int) $user['id'];
$rcReturn = 'request-center.php?workspace=' . rawurlencode($workspace)
    . '&calendar=1&agent_view=' . rawurlencode($agentView);

$rcRedirect = static function (string $status, ?string $reason = null) use ($rcReturn): void {
    $url = $rcReturn . '&appointments=' . rawurlencode($status);
    if ($reason !== null && $reason !== '') {
        $url .= '&reason=' . rawurlencode($reason);
    }
    header('Location: ' . $url);
    exit;
};

$rcLog = static function (string $actionCode, string $result, array $meta = []): void {
    if (function_exists('activity_log')) {
        try {
            activity_log([
                'action_code' => $actionCode,
                'module' => 'request-center',
                'result' => $result,
                'meta' => $meta,
            ]);
        } catch (Throwable) {}
    }
};

/** Dates (Gregorian Y-m-d) covered by [start, end]. Appointments spanning midnight are rare but covered. */
$rcDatesBetween = static function (string $startDate, string $endDate): array {
    $days = [];
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    if ($start === false || $end === false || $end < $start) {
        return [$startDate];
    }
    $cursor = $start;
    $iter = 0;
    while ($cursor <= $end && $iter < 32) {
        $days[] = date('Y-m-d', $cursor);
        $cursor = strtotime('+1 day', $cursor);
        $iter++;
    }
    return $days;
};

/** Active holidays for a set of dates (title map), or [] when the table is missing. */
$rcHolidaysOn = static function (array $dates): array {
    if ($dates === []) {
        return [];
    }
    try {
        $placeholders = implode(',', array_fill(0, count($dates), '?'));
        $st = db()->prepare("SELECT holiday_date, title FROM holidays WHERE is_active = 1 AND holiday_date IN ($placeholders)");
        $st->execute($dates);
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $map[(string) $row['holiday_date']] = (string) $row['title'];
        }
        return $map;
    } catch (Throwable) {
        return [];
    }
};

$idempotency = static fn(string $parts): string => hash('sha256', 'appointment|' . $parts);

if (isset($_POST['rc_appointment_cancel'])) {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $result = $rcAppointments->cancel($appointmentId, $rcActorId, $rcElevated);
    $rcLog('rc_appointment_cancel', $result['ok'] ? 'ok' : 'denied', ['appointment_id' => $appointmentId]);
    if ($result['ok']) {
        // Notify the assignee when someone else cancelled their appointment.
        try {
            $st = db()->prepare('SELECT assignee_id, ticket_id FROM request_center_appointments WHERE id = ? LIMIT 1');
            $st->execute([$appointmentId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row && (int) $row['assignee_id'] !== $rcActorId && function_exists('notify_user')) {
                $assigneeId = (int) $row['assignee_id'];
                notify_user($assigneeId, 'appointment_cancelled', 'نوبت رسیدگی لغو شد',
                    'نوبتِ قراردادی شما (#' . $appointmentId . ') لغو شد. برای جزئیات، تقویم را ببینید.',
                    (int) $row['ticket_id'], $idempotency($appointmentId . '|cancelled|' . $assigneeId));
            }
        } catch (Throwable) {}
    }
    $rcRedirect($result['ok'] ? 'cancelled' : 'error', $result['ok'] ? null : ($result['error'] ?? null));
}

if (isset($_POST['rc_appointment_complete'])) {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $result = $rcAppointments->complete($appointmentId, $rcActorId, $rcElevated);
    $rcLog('rc_appointment_complete', $result['ok'] ? 'ok' : 'denied', ['appointment_id' => $appointmentId]);
    if ($result['ok']) {
        try {
            $st = db()->prepare('SELECT assignee_id, ticket_id FROM request_center_appointments WHERE id = ? LIMIT 1');
            $st->execute([$appointmentId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row && (int) $row['assignee_id'] !== $rcActorId && function_exists('notify_user')) {
                $assigneeId = (int) $row['assignee_id'];
                notify_user($assigneeId, 'appointment_completed', 'نوبت رسیدگی انجام شد',
                    'نوبتِ قراردادی شما (#' . $appointmentId . ') «انجام‌شده» علامت خورد.',
                    (int) $row['ticket_id'], $idempotency($appointmentId . '|completed|' . $assigneeId));
            }
        } catch (Throwable) {}
    }
    $rcRedirect($result['ok'] ? 'completed' : 'error', $result['ok'] ? null : ($result['error'] ?? null));
}

if (!isset($_POST['rc_appointment_create'])) {
    $rcRedirect('error', 'درخواست نامعتبر است.');
}

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$ticket = $ticketId > 0 ? $repo->findVisible($ticketId, $user) : null;
if (!$ticket) {
    $rcRedirect('error', 'درخواست پیدا نشد یا به حوزهٔ شما تعلق ندارد.');
}

$startsAt = AppointmentRepository::normalizeDateTime((string) ($_POST['starts_at'] ?? ''));
$endsAt = AppointmentRepository::normalizeDateTime((string) ($_POST['ends_at'] ?? ''));
$kind = (string) ($_POST['kind'] ?? 'work');
$note = trim((string) ($_POST['note'] ?? ''));

$assigneeId = (int) ($_POST['assignee_id'] ?? 0);
if (!$rcElevated || $assigneeId <= 0) {
    $assigneeId = $rcActorId;
} else {
    $targetExists = db()->prepare('SELECT 1 FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
    $targetExists->execute([$assigneeId]);
    if (!$targetExists->fetchColumn()) {
        $rcRedirect('error', 'کارشناس هدف معتبر نیست.');
    }
}

if ($startsAt === null || $endsAt === null) {
    $rcRedirect('error', 'زمان شروع/پایان را کامل و معتبر وارد کنید.');
}

// —— نگهبان تعطیلات و روزهای تعطیل هفتگی (از تقویم کاری/تعطیلات همین سامانه) ——
$business = function_exists('sla_business_hours') ? sla_business_hours() : ['start' => 8, 'end' => 16, 'weekend' => [4, 5]];
$weekendIso = array_map('intval', (array) ($business['weekend'] ?? [4, 5]));
$rangeDays = $rcDatesBetween(substr($startsAt, 0, 10), substr($endsAt, 0, 10));
$holidays = $rcHolidaysOn($rangeDays);
$outsideFlag = ((string) ($_POST['allow_outside_business'] ?? '')) === '1';
$weekendHits = [];
foreach ($rangeDays as $day) {
    $isoDay = (int) date('N', strtotime($day)); // 1=Mon … 7=Sun (ISO-8601, like sla_business_hours)
    if (in_array($isoDay, $weekendIso, true)) {
        $weekendHits[] = $day;
    }
}
$outsideProblems = [];
foreach ($rangeDays as $day) {
    if (isset($holidays[$day])) {
        $outsideProblems[] = $day . ' (تعطیل: ' . $holidays[$day] . ')';
    }
}
foreach ($weekendHits as $day) {
    $outsideProblems[] = $day . ' (تعطیل هفتگی)';
}
if ($outsideProblems !== []) {
    if (!$rcElevated || !$outsideFlag) {
        $rcRedirect('error', 'این بازه بیرون از تقویم کاری شماست: ' . implode('، ', array_slice($outsideProblems, 0, 3)) . '. ثبت روی این روزها مجاز نیست؛ مدیر می‌تواند با گزینهٔ «ثبت حتی بیرون از تقویم کاری/تعطیلات» انجام دهد.');
    }
}

$result = $rcAppointments->create($ticketId, $assigneeId, $startsAt, $endsAt, $kind, $note, $rcActorId);
$rcLog('rc_appointment_create', $result['ok'] ? 'ok' : 'denied', [
    'ticket_id' => $ticketId,
    'assignee_id' => $assigneeId,
    'appointment_id' => $result['id'] ?? null,
]);

if (!$result['ok']) {
    $rcRedirect('error', $result['error'] ?? 'ثبت نوبت انجام نشد.');
}

if ($assigneeId !== $rcActorId && function_exists('notify_user')) {
    $apptId = (int) ($result['id'] ?? 0);
    try {
        notify_user($assigneeId, 'appointment_created', 'نوبت رسیدگی جدید',
            'نوبتی برای شما روی درخواست ' . (string) ($ticket['ticket_no'] ?? ('#' . $ticketId)) . ' ثبت شد: ' . substr($startsAt, 0, 10) . ' — ' . substr($startsAt, 11, 5) . ' تا ' . substr($endsAt, 11, 5) . '.',
            $ticketId, $idempotency($apptId . '|created|' . $assigneeId));
    } catch (Throwable) {}
}

$rcRedirect('created');
