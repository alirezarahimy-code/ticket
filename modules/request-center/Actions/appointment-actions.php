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
 * Phase 5.2 (موج ۲ اصلی): holiday lookup failure is fail-closed for creation
 * (elevated users may still force with allow_outside_business=1, logged);
 * assignee validated against AppointmentRepository::ASSIGNABLE_ROLES;
 * requester is notified in addition to the assignee.
 */

// محافظ: این فایل فقط از طریق شاخهٔ تقویم ماژول بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/AppointmentRepository.php';

// مدیر واحدِ صرف هیچ دسترسی به تقویم/برنامهٔ رسیدگی ندارد (حتی با ticket.edit).
if (function_exists('ticket_sole_unit_head') && ticket_sole_unit_head($user)) {
    http_response_code(403);
    exit('مدیر واحد دسترسی به تقویم ندارد.');
}
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

// مقصد بازگشت پس از عملیات: کلاینت می‌تواند نماهنگ شود ('month' | 'agenda' | 'day')،
// ماه و روز جاری را ارسال کند تا کاربر دقیقاً به همان‌جا برگردد (پیش‌فرض تاریخی: روز).
$rcReturnView = in_array((string) ($_POST['return_view'] ?? ''), ['month', 'agenda', 'day'], true) ? (string) $_POST['return_view'] : '';
$rcReturnJMonth = preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', (string) ($_POST['return_jmonth'] ?? '')) ? (string) $_POST['return_jmonth'] : '';
$rcReturnDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_POST['return_date'] ?? '')) ? (string) $_POST['return_date'] : '';
$rcReturnExtras = [];
if ($rcReturnView !== '') {
    $rcReturnExtras['view'] = $rcReturnView;
}
if ($rcReturnJMonth !== '') {
    $rcReturnExtras['jmonth'] = $rcReturnJMonth;
}
if ($rcReturnView === 'day' && $rcReturnDate !== '') {
    $rcReturnExtras['date'] = $rcReturnDate;
}

$rcRedirect = static function (string $status, ?string $reason = null, array $extra = []) use ($rcReturn, $rcReturnExtras): void {
    $extra = array_merge($rcReturnExtras, $extra);
    $url = $rcReturn . '&appointments=' . rawurlencode($status);
    if ($reason !== null && $reason !== '') {
        $url .= '&reason=' . rawurlencode($reason);
    }
    foreach ($extra as $key => $value) {
        $url .= '&' . rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
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

/** Active holidays for a set of dates (title map). Lookup failure is logged fail-loud; the caller decides how to surface it. */
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
    } catch (Throwable $exception) {
        if (function_exists('activity_log')) {
            try {
                activity_log([
                    'action_code' => 'rc_holidays_lookup_failed',
                    'module' => 'request-center',
                    'result' => 'warning',
                    'meta' => ['error' => $exception->getMessage()],
                ]);
            } catch (Throwable) {}
        }
        // علامت‌گذاری به‌عنوان شکست (fail-closed برای مسیر ثبت نوبت)
        $GLOBALS['rc_holidays_lookup_failed'] = true;
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
            // همچنین ثبت‌کنندهٔ درخواست را مطلع کن (اگر عامل متفاوت است)
            if ($row && function_exists('notify_user')) {
                $stReq = db()->prepare('SELECT requester_id FROM tickets WHERE id = ? LIMIT 1');
                $stReq->execute([(int) $row['ticket_id']]);
                $requesterId = (int) ($stReq->fetchColumn() ?: 0);
                if ($requesterId > 0 && $requesterId !== $rcActorId) {
                    notify_user($requesterId, 'appointment_cancelled', 'نوبت رسیدگی لغو شد',
                        'نوبتی که روی درخواست شما ثبت شده بود لغو شد.',
                        (int) $row['ticket_id'], $idempotency($appointmentId . '|cancelled|requester|' . $requesterId));
                }
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
            // همچنین ثبت‌کنندهٔ درخواست را مطلع کن (اگر عامل متفاوت است)
            if ($row && function_exists('notify_user')) {
                $stReq = db()->prepare('SELECT requester_id FROM tickets WHERE id = ? LIMIT 1');
                $stReq->execute([(int) $row['ticket_id']]);
                $requesterId = (int) ($stReq->fetchColumn() ?: 0);
                if ($requesterId > 0 && $requesterId !== $rcActorId) {
                    notify_user($requesterId, 'appointment_completed', 'نوبت رسیدگی انجام شد',
                        'نوبتی که روی درخواست شما ثبت شده بود «انجام‌شده» علامت خورد.',
                        (int) $row['ticket_id'], $idempotency($appointmentId . '|completed|requester|' . $requesterId));
                }
            }
        } catch (Throwable) {}
    }
    $rcRedirect($result['ok'] ? 'completed' : 'error', $result['ok'] ? null : ($result['error'] ?? null));
}

/**
 * Creates one appointment with the full guard chain. Returns:
 *   ['ok' => bool, 'error' => ?string, 'id' => ?int, 'ticket' => ?array]
 * Holiday/weekend lookup failure is fail-closed unless elevated + allow_outside_business.
 * Notifications (assignee + requester) are sent on success.
 */
$rcCreateOne = static function (int $ticketId, string $startsRaw, string $endsRaw, string $kind, string $note, int $assigneeId, bool $outsideFlag) use ($repo, $user, $rcAppointments, $rcElevated, $rcActorId, $rcLog, $rcDatesBetween, $rcHolidaysOn, $idempotency): array {
    $ticket = $ticketId > 0 ? $repo->findVisible($ticketId, $user) : null;
    if (!$ticket) {
        return ['ok' => false, 'error' => 'درخواست پیدا نشد یا به حوزهٔ شما تعلق ندارد.', 'id' => null, 'ticket' => null];
    }

    // —— تعیین کارشناس مسئول (فقط مدیر می‌تواند برای دیگری ثبت کند) ——
    if (!$rcElevated || $assigneeId <= 0) {
        $assigneeId = $rcActorId;
    } else {
        $targetStmt = db()->prepare('SELECT role, is_active FROM users WHERE id = ? LIMIT 1');
        $targetStmt->execute([$assigneeId]);
        $targetRow = $targetStmt->fetch(PDO::FETCH_ASSOC);
        if (!$targetRow
            || (int) ($targetRow['is_active'] ?? 0) !== 1
            || !in_array((string) ($targetRow['role'] ?? ''), AppointmentRepository::assignableRoleCodes(), true)) {
            return ['ok' => false, 'error' => 'کارشناس هدف معتبر نیست (حساب باید فعال و عضو نقش‌های رسیدگی باشد).', 'id' => null, 'ticket' => $ticket];
        }
    }

    $startsAt = AppointmentRepository::normalizeDateTime($startsRaw);
    $endsAt = AppointmentRepository::normalizeDateTime($endsRaw);
    if ($startsAt === null || $endsAt === null) {
        return ['ok' => false, 'error' => 'زمان شروع/پایان را کامل و معتبر وارد کنید.', 'id' => null, 'ticket' => $ticket];
    }

    // —— نگهبان تعطیلات و آخرهفته (Fail-closed) ——
    $business = function_exists('sla_business_hours') ? sla_business_hours() : ['start' => 8, 'end' => 16, 'weekend' => [4, 5]];
    $weekendIso = array_map('intval', (array) ($business['weekend'] ?? [4, 5]));
    $rangeDays = $rcDatesBetween(substr($startsAt, 0, 10), substr($endsAt, 0, 10));
    $holidays = $rcHolidaysOn($rangeDays);
    if (!empty($GLOBALS['rc_holidays_lookup_failed'])) {
        $elevatedAllows = $rcElevated && $outsideFlag;
        if (!$elevatedAllows) {
            $rcLog('rc_appointment_create', 'denied', ['ticket_id' => $ticketId, 'reason' => 'holiday lookup failed']);
            return ['ok' => false, 'error' => 'بررسی جدول تعطیلات ممکن نشد؛ نوبت ثبت نشد. با مدیر سامانه هماهنگ کنید.', 'id' => null, 'ticket' => $ticket];
        }
        $rcLog('rc_appointment_create', 'warning', ['ticket_id' => $ticketId, 'reason' => 'holiday lookup failed', 'elevated_override' => true]);
    }
    $outsideProblems = [];
    foreach ($rangeDays as $day) {
        if (isset($holidays[$day])) {
            $outsideProblems[] = $day . ' (تعطیل: ' . $holidays[$day] . ')';
            continue;
        }
        $isoDay = (int) date('N', strtotime($day));
        if (in_array($isoDay, $weekendIso, true)) {
            $outsideProblems[] = $day . ' (تعطیل هفتگی)';
        }
    }
    if ($outsideProblems !== [] && (!$rcElevated || !$outsideFlag)) {
        return ['ok' => false, 'error' => 'این بازه بیرون از تقویم کاری شماست: ' . implode('، ', array_slice($outsideProblems, 0, 3)) . '.', 'id' => null, 'ticket' => $ticket];
    }

    $result = $rcAppointments->create($ticketId, $assigneeId, $startsAt, $endsAt, $kind, $note, $rcActorId, ($rcElevated && $outsideFlag));
    $rcLog('rc_appointment_create', $result['ok'] ? 'ok' : 'denied', [
        'ticket_id' => $ticketId,
        'assignee_id' => $assigneeId,
        'appointment_id' => $result['id'] ?? null,
    ]);
    if (!$result['ok']) {
        return ['ok' => false, 'error' => $result['error'] ?? 'ثبت نوبت انجام نشد.', 'id' => null, 'ticket' => $ticket];
    }

    // —— اعلان‌ها (کارشناس مسئول + ثبت‌کنندهٔ درخواست) ——
    $apptId = (int) ($result['id'] ?? 0);
    try {
        if ($assigneeId !== $rcActorId && function_exists('notify_user')) {
            notify_user($assigneeId, 'appointment_created', 'نوبت رسیدگی جدید',
                'نوبتی برای شما روی درخواست ' . (string) ($ticket['ticket_no'] ?? ('#' . $ticketId)) . ' ثبت شد: ' . substr($startsAt, 0, 10) . ' — ' . substr($startsAt, 11, 5) . ' تا ' . substr($endsAt, 11, 5) . '.',
                $ticketId, $idempotency($apptId . '|created|' . $assigneeId));
        }
        $requesterId = isset($ticket['requester_id']) ? (int) $ticket['requester_id'] : 0;
        if ($requesterId > 0 && $requesterId !== $rcActorId && $requesterId !== $assigneeId && function_exists('notify_user')) {
            notify_user($requesterId, 'appointment_created', 'نوبت رسیدگی روی درخواست شما ثبت شد',
                'برای درخواست ' . (string) ($ticket['ticket_no'] ?? ('#' . $ticketId)) . ' نوبتی ثبت شد: ' . substr($startsAt, 0, 10) . ' ساعت ' . substr($startsAt, 11, 5) . '.',
                $ticketId, $idempotency($apptId . '|created|requester|' . $requesterId));
        }
    } catch (Throwable) {}

    return ['ok' => true, 'error' => null, 'id' => $apptId, 'ticket' => $ticket];
};

$rcKindFromPost = static function (): string {
    $kind = (string) ($_POST['kind'] ?? 'work');
    return isset(AppointmentRepository::KINDS[$kind]) ? $kind : 'work';
};

// ——— ثبت گروهی نوبت‌ها (نمای روز تقویم): یک تاریخ، چند درخواست — «همه یا هیچ» (تصمیم مالک ۲۰۲۶-۱۱-۱۰) ———
if (isset($_POST['rc_appointment_create_batch'])) {
    $date = trim((string) ($_POST['date'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
        $rcRedirect('error', 'تاریخ روز نامعتبر است.');
    }
    $kind = $rcKindFromPost();
    $note = trim((string) ($_POST['note'] ?? ''));
    $assigneeId = (int) ($_POST['assignee_id'] ?? 0);
    $outsideFlag = ((string) ($_POST['allow_outside_business'] ?? '')) === '1';

    $picks = $_POST['pick'] ?? [];
    $starts = $_POST['starts'] ?? [];
    $ends = $_POST['ends'] ?? [];
    if (!is_array($picks) || $picks === []) {
        $rcRedirect('batch', null, ['ok' => 0, 'failed' => 0, 'empty' => 1, 'view' => $rcReturnView !== '' ? $rcReturnView : 'day', 'date' => $date]);
    }
    $pickIds = array_slice(array_keys($picks), 0, 50); // سقف سخت ۵۰ نوبت در یک بازهٔ ارسال

    // کارشناس مسئول: یک‌بار برای کل دسته اعتبارسنجی می‌شود (همان قاعدهٔ ثبت تکی).
    $resolvedAssigneeId = $rcActorId;
    if ($rcElevated && $assigneeId > 0) {
        $targetStmt = db()->prepare('SELECT role, is_active FROM users WHERE id = ? LIMIT 1');
        $targetStmt->execute([$assigneeId]);
        $targetRow = $targetStmt->fetch(PDO::FETCH_ASSOC);
        if (!$targetRow
            || (int) ($targetRow['is_active'] ?? 0) !== 1
            || !in_array((string) ($targetRow['role'] ?? ''), AppointmentRepository::assignableRoleCodes(), true)) {
            $rcRedirect('error', 'کارشناس هدف معتبر نیست؛ هیچ نوبتی ثبت نشد (حساب باید فعال و عضو نقش‌های رسیدگی باشد).');
        }
        $resolvedAssigneeId = $assigneeId;
    }

    // نگهبان تعطیلات و آخرهفته برای «این تاریخ» — همان قاعدهٔ ثبت تکی، یک‌بار برای کل دسته.
    $business = function_exists('sla_business_hours') ? sla_business_hours() : ['start' => 8, 'end' => 16, 'weekend' => [4, 5]];
    $weekendIso = array_map('intval', (array) ($business['weekend'] ?? [4, 5]));
    $holidays = $rcHolidaysOn([$date]);
    if (!empty($GLOBALS['rc_holidays_lookup_failed'])) {
        if (!($rcElevated && $outsideFlag)) {
            $rcLog('rc_appointment_create_batch', 'denied', ['reason' => 'holiday lookup failed']);
            $rcRedirect('error', 'بررسی جدول تعطیلات ممکن نشد؛ هیچ نوبتی ثبت نشد. با مدیر سامانه هماهنگ کنید.');
        }
    } else {
        $holidayTitle = $holidays[$date] ?? null;
        $isoDay = (int) date('N', strtotime($date));
        if (!($rcElevated && $outsideFlag)) {
            if ($holidayTitle !== null) {
                $rcRedirect('error', 'این تاریخ تعطیل است (' . $holidayTitle . ')؛ هیچ نوبتی ثبت نشد.');
            }
            if (in_array($isoDay, $weekendIso, true)) {
                $rcRedirect('error', 'این روز تعطیل هفتگی است؛ هیچ نوبتی ثبت نشد.');
            }
        }
    }

    // ── فاز ۱: اعتبارسنجی همهٔ ردیف‌ها بدون نوشتن (همه یا هیچ) ──
    $timeOk = static fn (string $t): bool => (bool) preg_match('/^([01]?\d|2[0-3]):[0-5]\d$/', $t);
    $batchErrors = [];
    $items = [];
    foreach ($pickIds as $rawId) {
        $ticketId = (int) $rawId;
        $rowLabel = 'درخواست #' . ($ticketId > 0 ? $ticketId : (string) $rawId);
        $ticket = $ticketId > 0 ? $repo->findVisible($ticketId, $user) : null;
        if (!$ticket) {
            $batchErrors[] = $rowLabel . ': پیدا نشد یا به حوزهٔ شما تعلق ندارد.';
            continue;
        }
        $startRaw = is_array($starts) ? trim((string) ($starts[$rawId] ?? '')) : '';
        $endRaw = is_array($ends) ? trim((string) ($ends[$rawId] ?? '')) : '';
        if (!$timeOk($startRaw) || !$timeOk($endRaw)) {
            $batchErrors[] = $rowLabel . ': ساعت شروع/پایان معتبر نیست.';
            $rcLog('rc_appointment_create_batch', 'denied', ['ticket_id' => $ticketId, 'reason' => 'invalid time format']);
            continue;
        }
        [$sh, $sm] = array_map('intval', explode(':', $startRaw));
        [$eh, $em] = array_map('intval', explode(':', $endRaw));
        $items[] = [
            'ticket_id' => $ticketId,
            'assigneeId' => $resolvedAssigneeId,
            'startsAt' => sprintf('%s %02d:%02d:00', $date, $sh, $sm),
            'endsAt' => sprintf('%s %02d:%02d:00', $date, $eh, $em),
            'kind' => $kind,
            'note' => $note,
            'actorId' => $rcActorId,
            'allowOutsideBusinessHours' => $outsideFlag,
            'ticket' => $ticket,
        ];
    }
    if ($batchErrors !== []) {
        $summary = implode(' | ', array_slice($batchErrors, 0, 3))
            . (count($batchErrors) > 3 ? ' | و ' . (count($batchErrors) - 3) . ' مورد دیگر' : '');
        $rcRedirect('error', 'ثبت گروهی انجام نشد؛ هیچ نوبتی ثبت نشد. ' . $summary);
    }

    // ── فاز ۲: درج در یک تراکنش (همه یا هیچ) ──
    $batchResult = $rcAppointments->createBatch($items);
    if (!$batchResult['ok']) {
        $rcLog('rc_appointment_create_batch', 'denied', ['reason' => (string) ($batchResult['error'] ?? 'batch failed')]);
        $rcRedirect('error', 'ثبت گروهی انجام نشد؛ هیچ نوبتی ثبت نشد. ' . (string) ($batchResult['error'] ?? ''));
    }
    $ids = array_values($batchResult['ids'] ?? []);
    foreach ($ids as $index => $appointmentId) {
        $entry = $items[$index];
        $ticket = $entry['ticket'];
        $startsAt = $entry['startsAt'];
        $endsAt = $entry['endsAt'];
        $rcLog('rc_appointment_create_batch', 'ok', ['ticket_id' => (int) $ticket['id'], 'appointment_id' => (int) $appointmentId]);
        // —— اعلان‌ها (کارشناس مسئول + ثبت‌کنندهٔ درخواست) ——
        try {
            if ($resolvedAssigneeId !== $rcActorId && function_exists('notify_user')) {
                notify_user($resolvedAssigneeId, 'appointment_created', 'نوبت رسیدگی جدید',
                    'نوبتی برای شما روی درخواست ' . (string) ($ticket['ticket_no'] ?? ('#' . (int) $ticket['id'])) . ' ثبت شد: ' . substr($startsAt, 0, 10) . ' — ' . substr($startsAt, 11, 5) . ' تا ' . substr($endsAt, 11, 5) . '.',
                    (int) $ticket['id'], $idempotency($appointmentId . '|created|' . $resolvedAssigneeId));
            }
            $requesterId = isset($ticket['requester_id']) ? (int) $ticket['requester_id'] : 0;
            if ($requesterId > 0 && $requesterId !== $rcActorId && $requesterId !== $resolvedAssigneeId && function_exists('notify_user')) {
                notify_user($requesterId, 'appointment_created', 'نوبت رسیدگی روی درخواست شما ثبت شد',
                    'برای درخواست ' . (string) ($ticket['ticket_no'] ?? ('#' . (int) $ticket['id'])) . ' نوبتی ثبت شد: ' . substr($startsAt, 0, 10) . ' ساعت ' . substr($startsAt, 11, 5) . '.',
                    (int) $ticket['id'], $idempotency($appointmentId . '|created|requester|' . $requesterId));
            }
        } catch (Throwable) {}
    }
    $rcRedirect('batch', null, ['ok' => count($ids), 'failed' => 0, 'view' => $rcReturnView !== '' ? $rcReturnView : 'day', 'date' => $date]);
}

// ——— ثبت تکی نوبت (فرم کلاسیک) ———
if (!isset($_POST['rc_appointment_create'])) {
    $rcRedirect('error', 'درخواست نامعتبر است.');
}

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$kind = $rcKindFromPost();
$note = trim((string) ($_POST['note'] ?? ''));
$assigneeId = (int) ($_POST['assignee_id'] ?? 0);
$outsideFlag = ((string) ($_POST['allow_outside_business'] ?? '')) === '1';

$result = $rcCreateOne($ticketId, (string) ($_POST['starts_at'] ?? ''), (string) ($_POST['ends_at'] ?? ''), $kind, $note, $assigneeId, $outsideFlag);
if (!$result['ok']) {
    $rcRedirect('error', (string) ($result['error'] ?? 'ثبت نوبت انجام نشد.'));
}

$rcRedirect('created');
