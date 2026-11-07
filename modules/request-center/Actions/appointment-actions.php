<?php
declare(strict_types=1);

/**
 * POST handler for Request Center expert appointments (calendar page).
 * Runs only inside the calendar branch of modules/request-center/Pages/index.php
 * (which creates $user, $repo, $workspace, $agentView and $escape).
 * Redirects back to the calendar on completion; never renders output.
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

if (isset($_POST['rc_appointment_cancel'])) {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $result = $rcAppointments->cancel($appointmentId, (int) $user['id'], $rcElevated);
    if (function_exists('activity_log')) {
        try { activity_log(['action_code' => 'rc_appointment_cancel', 'module' => 'request-center',
            'result' => $result['ok'] ? 'ok' : 'denied', 'meta' => ['appointment_id' => $appointmentId]]); } catch (Throwable) {}
    }
    $rcRedirect($result['ok'] ? 'cancelled' : 'error', $result['ok'] ? null : ($result['error'] ?? null));
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
    $assigneeId = (int) $user['id'];
} else {
    $targetExists = db()->prepare('SELECT 1 FROM users WHERE id = ? LIMIT 1');
    $targetExists->execute([$assigneeId]);
    if (!$targetExists->fetchColumn()) {
        $rcRedirect('error', 'کارشناس هدف معتبر نیست.');
    }
}

if ($startsAt === null || $endsAt === null) {
    $rcRedirect('error', 'زمان شروع/پایان را کامل و معتبر وارد کنید.');
}

$result = $rcAppointments->create($ticketId, $assigneeId, $startsAt, $endsAt, $kind, $note, (int) $user['id']);

if (function_exists('activity_log')) {
    try { activity_log(['action_code' => 'rc_appointment_create', 'module' => 'request-center',
        'result' => $result['ok'] ? 'ok' : 'denied',
        'meta' => ['ticket_id' => $ticketId, 'assignee_id' => $assigneeId, 'appointment_id' => $result['id'] ?? null]]); } catch (Throwable) {}
}

$rcRedirect(
    $result['ok'] ? 'created' : 'error',
    $result['ok'] ? null : ($result['error'] ?? 'ثبت نوبت انجام نشد.')
);
