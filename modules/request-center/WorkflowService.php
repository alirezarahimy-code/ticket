<?php
declare(strict_types=1);

/**
 * Central Request Center status-transition writer.
 *
 * The caller owns the surrounding transaction and remains responsible for
 * actor authorization, audit/event records and notifications. This service
 * owns the locked state re-read, transition policy and the single status write.
 */
final class RequestCenterSqlExpression
{
    private string $kind;

    private function __construct(string $kind)
    {
        $this->kind = $kind;
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public static function now(): self
    {
        return new self('now');
    }

    public static function coalesceNow(): self
    {
        return new self('coalesce_now');
    }
}

final class RequestCenterWorkflowService
{
    private const STATUSES = [
        'new', 'manager_review', 'assigned', 'in_progress', 'waiting_user',
        'pending', 'resolved', 'closed', 'cancelled', 'rejected',
    ];

    private const REQUESTER_CANCELLABLE_STATUSES = [
        'new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved',
    ];

    private const CANCELLATION_PENDING_SAME_STATE_COLUMNS = [
        'due_at', 'sla_paused_at', 'sla_pause_minutes', 'first_response_at',
        'ola_policy_id', 'ola_response_due_at', 'ola_due_at',
        'ola_escalation_1_at', 'ola_escalation_2_at',
    ];

    private const UPDATE_COLUMNS = [
        'assigned_to', 'assigned_at', 'due_at', 'sla_minutes', 'sla_paused_at',
        'sla_pause_minutes', 'first_response_at', 'ola_policy_id', 'ola_response_due_at', 'ola_due_at',
        'ola_escalation_1_at', 'ola_escalation_2_at', 'resolved_at', 'closed_at', 'rejected_at',
        'supervisor_id', 'supervisor_approved_at', 'supervisor_note',
        'cancel_requested_at', 'cancel_requested_by', 'cancel_request_reason',
        'priority', 'department_id', 'service_group', 'support_location',
        'support_equipment', 'asset_id', 'requesting_unit_id', 'category_id',
        'service_id', 'custom_fields', 'ticket_type', 'handling_unit_id',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Append a public or internal conversation entry in the caller-owned transaction.
     * Attachment storage and notification/audit side effects remain with the caller.
     */
    public function appendMessage(int $ticketId, int $userId, string $body, bool $internal = false): int
    {
        $this->assertTransaction();
        if ($ticketId <= 0 || $userId <= 0 || trim($body) === '') {
            throw new InvalidArgumentException('شناسه یا متن پیام درخواست معتبر نیست.');
        }
        $statement = $this->pdo->prepare(
            'INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$ticketId, $userId, $body, $internal ? 1 : 0]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Register a cancellation request without changing the ticket's lifecycle status. */
    public function requestCancellation(int $ticketId, int $requesterId, string $expectedStatus, string $reason): bool
    {
        $this->assertTransaction();
        if ($ticketId <= 0 || $requesterId <= 0 || trim($reason) === ''
            || !in_array($expectedStatus, self::REQUESTER_CANCELLABLE_STATUSES, true)) {
            throw new InvalidArgumentException('اطلاعات درخواست لغو معتبر نیست.');
        }
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $lockSql = 'SELECT status, requester_id, cancel_requested_at FROM tickets WHERE id = ? LIMIT 1';
        if ($driver === 'mysql') {
            $lockSql .= ' FOR UPDATE';
        }
        $lock = $this->pdo->prepare($lockSql);
        $lock->execute([$ticketId]);
        $current = $lock->fetch(PDO::FETCH_ASSOC);
        if (!is_array($current) || (string) $current['status'] !== $expectedStatus
            || (int) $current['requester_id'] !== $requesterId || !empty($current['cancel_requested_at'])) {
            return false;
        }
        $now = $driver === 'sqlite' ? 'CURRENT_TIMESTAMP' : 'NOW()';
        $update = $this->pdo->prepare(
            'UPDATE tickets SET cancel_requested_at = ' . $now . ', cancel_requested_by = ?, '
            . 'cancel_request_reason = ?, updated_at = ' . $now
            . ' WHERE id = ? AND requester_id = ? AND status = ? AND cancel_requested_at IS NULL'
        );
        $update->execute([$requesterId, $reason, $ticketId, $requesterId, $expectedStatus]);
        return $update->rowCount() > 0;
    }

    /** Clear a cancellation request only when the exact request snapshot still matches. */
    public function rejectCancellation(
        int $ticketId,
        string $expectedStatus,
        string $expectedRequestedAt,
        array $expectedFields = []
    ): bool {
        $this->assertTransaction();
        if ($ticketId <= 0 || $expectedRequestedAt === ''
            || !in_array($expectedStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException('اطلاعات تصمیم لغو معتبر نیست.');
        }
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $lockSql = 'SELECT status, cancel_requested_at, requester_id, assigned_to, priority, department_id FROM tickets WHERE id = ? LIMIT 1';
        if ($driver === 'mysql') {
            $lockSql .= ' FOR UPDATE';
        }
        $lock = $this->pdo->prepare($lockSql);
        $lock->execute([$ticketId]);
        $current = $lock->fetch(PDO::FETCH_ASSOC);
        if (!is_array($current) || (string) $current['status'] !== $expectedStatus
            || (string) ($current['cancel_requested_at'] ?? '') !== $expectedRequestedAt) {
            return false;
        }
        foreach ($expectedFields as $field => $expectedValue) {
            if (!in_array($field, ['requester_id', 'assigned_to', 'priority', 'department_id'], true)) {
                throw new InvalidArgumentException('فیلد شرط هم‌زمانی تصمیم لغو مجاز نیست: ' . $field);
            }
            $actual = $current[$field] ?? null;
            if ($actual === null || $expectedValue === null) {
                if ($actual !== $expectedValue) {
                    return false;
                }
            } elseif ((string) $actual !== (string) $expectedValue) {
                return false;
            }
        }
        $now = $driver === 'sqlite' ? 'CURRENT_TIMESTAMP' : 'NOW()';
        $update = $this->pdo->prepare(
            'UPDATE tickets SET cancel_requested_at = NULL, cancel_requested_by = NULL, '
            . 'cancel_request_reason = NULL, updated_at = ' . $now
            . ' WHERE id = ? AND status = ? AND cancel_requested_at = ?'
        );
        $update->execute([$ticketId, $expectedStatus, $expectedRequestedAt]);
        return $update->rowCount() > 0;
    }

    private function assertTransaction(): void
    {
        if (!$this->pdo->inTransaction()) {
            throw new LogicException('عملیات گردش‌کار باید داخل تراکنش فراخواننده اجرا شود.');
        }
    }

    public static function allowedTransitions(string $from): array
    {
        $map = [
            'new' => ['manager_review', 'assigned', 'in_progress', 'rejected'],
            'manager_review' => ['assigned', 'in_progress', 'rejected'],
            'assigned' => ['in_progress', 'waiting_user', 'resolved', 'manager_review', 'rejected'],
            'in_progress' => ['waiting_user', 'resolved', 'assigned', 'manager_review'],
            'waiting_user' => ['in_progress', 'resolved', 'assigned', 'manager_review'],
            // pending یک وضعیت میراثی است: خروج باز می‌ماند، اما ورود جدید ممنوع است.
            'pending' => ['in_progress', 'waiting_user', 'resolved', 'assigned', 'manager_review'],
            'resolved' => ['in_progress', 'assigned'],
            'closed' => [],
            'cancelled' => [],
            'rejected' => [],
        ];
        return $map[$from] ?? [];
    }

    /**
     * Apply one locked status transition and its associated ticket-column updates.
     *
     * Supported kinds:
     * - standard: only the documented transition map; cancellation-pending blocks.
     * - requester_cancel: unassigned new/manager_review -> cancelled.
     * - cancellation_approved: an active cancellation request -> cancelled.
     * - supervisor_close: resolved -> closed.
     * - supervisor_reject: pre-service active request -> rejected.
     * - supervisor_reopen: closed -> in_progress or manager_review.
     * - auto_close: resolved -> closed, only when no cancellation is pending.
     *
     * @param array<string,mixed> $updates Only allowlisted ticket columns.
     * @param array<string,mixed> $expectedFields Optional optimistic guards.
     */
    public function transition(
        int $ticketId,
        string $expectedStatus,
        string $targetStatus,
        array $updates = [],
        string $kind = 'standard',
        array $expectedFields = []
    ): bool {
        if ($ticketId <= 0 || !in_array($expectedStatus, self::STATUSES, true)
            || !in_array($targetStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException('شناسه یا وضعیت درخواست برای گذار معتبر نیست.');
        }
        if (!$this->pdo->inTransaction()) {
            throw new LogicException('گذار وضعیت باید داخل تراکنش فراخواننده اجرا شود.');
        }

        foreach ($updates as $column => $_value) {
            if (!in_array($column, self::UPDATE_COLUMNS, true)) {
                throw new InvalidArgumentException('ستون به‌روزرسانی درخواست مجاز نیست: ' . $column);
            }
        }

        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $lockSql = 'SELECT id, status, requester_id, assigned_to, first_response_at, resolved_at, cancel_requested_at, priority, department_id FROM tickets WHERE id = ? LIMIT 1';
        if ($driver === 'mysql') {
            $lockSql .= ' FOR UPDATE';
        }
        $lock = $this->pdo->prepare($lockSql);
        $lock->execute([$ticketId]);
        $current = $lock->fetch(PDO::FETCH_ASSOC);
        if (!is_array($current) || (string) $current['status'] !== $expectedStatus) {
            return false;
        }

        foreach ($expectedFields as $field => $expectedValue) {
            if (!in_array($field, ['requester_id', 'assigned_to', 'first_response_at', 'resolved_at', 'cancel_requested_at', 'priority', 'department_id'], true)) {
                throw new InvalidArgumentException('فیلد شرط هم‌زمانی مجاز نیست: ' . $field);
            }
            $actual = $current[$field] ?? null;
            if ($actual === null || $expectedValue === null) {
                if ($actual !== $expectedValue) {
                    return false;
                }
            } elseif ((string) $actual !== (string) $expectedValue) {
                return false;
            }
        }

        $this->assertTransitionAllowed($current, $expectedStatus, $targetStatus, $kind);
        if ($kind === 'same_state' && !empty($current['cancel_requested_at'])) {
            foreach (array_keys($updates) as $column) {
                if (!in_array($column, self::CANCELLATION_PENDING_SAME_STATE_COLUMNS, true)) {
                    throw new RuntimeException('در زمان انتظار تصمیم لغو، فقط ثبت پاسخ و به‌روزرسانی زمان‌های مرتبط مجاز است.');
                }
            }
        }

        $nowExpression = $driver === 'sqlite' ? 'CURRENT_TIMESTAMP' : 'NOW()';
        $sets = ['status = ?'];
        $params = [$targetStatus];
        foreach ($updates as $column => $value) {
            if ($value instanceof RequestCenterSqlExpression) {
                if ($value->kind() === 'now') {
                    $sets[] = $column . ' = ' . $nowExpression;
                } elseif ($value->kind() === 'coalesce_now') {
                    $sets[] = $column . ' = COALESCE(' . $column . ', ' . $nowExpression . ')';
                } else {
                    throw new InvalidArgumentException('عبارت SQL برای گذار ناشناخته است.');
                }
            } else {
                $sets[] = $column . ' = ?';
                $params[] = $value;
            }
        }
        $sets[] = 'updated_at = ' . $nowExpression;
        $params[] = $ticketId;
        $params[] = $expectedStatus;

        $statement = $this->pdo->prepare(
            'UPDATE tickets SET ' . implode(', ', $sets) . ' WHERE id = ? AND status = ?'
        );
        $statement->execute($params);

        // A same-state metadata update may be a no-op within DATETIME second
        // precision. The row is locked and all optimistic guards already passed.
        return $statement->rowCount() > 0 || $expectedStatus === $targetStatus;
    }

    /** @param array<string,mixed> $current */
    private function assertTransitionAllowed(
        array $current,
        string $from,
        string $to,
        string $kind
    ): void {
        $cancelPending = !empty($current['cancel_requested_at']);
        switch ($kind) {
            case 'standard':
                if ($to === 'rejected') {
                    throw new RuntimeException('رد درخواست فقط با فرمان اختصاصی و مجوز سوپروایزر امکان‌پذیر است.');
                }
                if ($cancelPending) {
                    throw new RuntimeException('لغو درخواست در انتظار تصمیم است؛ تغییر وضعیت متوقف شده است.');
                }
                if ($from !== $to && !in_array($to, self::allowedTransitions($from), true)) {
                    throw new RuntimeException('گذار وضعیت درخواست مجاز نیست.');
                }
                return;

            case 'requester_cancel':
                if ($to !== 'cancelled' || !in_array($from, ['new', 'manager_review'], true)
                    || $cancelPending || !empty($current['assigned_to'])
                    || !empty($current['first_response_at']) || !empty($current['resolved_at'])) {
                    throw new RuntimeException('لغو مستقیم فقط پیش از ارجاع و شروع رسیدگی مجاز است.');
                }
                return;

            case 'cancellation_approved':
                if ($to !== 'cancelled' || !$cancelPending
                    || !in_array($from, ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved'], true)) {
                    throw new RuntimeException('درخواست لغو در وضعیت فعلی قابل تأیید نیست.');
                }
                return;

            case 'requester_reopen':
                if ($from !== 'resolved' || !in_array($to, ['in_progress', 'manager_review'], true) || $cancelPending) {
                    throw new RuntimeException('بازگشایی درخواست حل‌شده در وضعیت فعلی مجاز نیست.');
                }
                return;

            case 'supervisor_rework':
                if ($from !== 'resolved' || !in_array($to, ['in_progress', 'manager_review'], true) || $cancelPending) {
                    throw new RuntimeException('برگشت برای اصلاح فقط از وضعیت حل‌شده و بدون لغو در انتظار مجاز است.');
                }
                return;

            case 'same_state':
                if ($from !== $to) {
                    throw new RuntimeException('فرمان بدون تغییر وضعیت نباید وضعیت مقصد متفاوت داشته باشد.');
                }
                return;

            case 'supervisor_reject':
                if (!in_array($from, ['new', 'manager_review', 'assigned'], true)
                    || $to !== 'rejected' || $cancelPending
                    || !empty($current['first_response_at']) || !empty($current['resolved_at'])) {
                    throw new RuntimeException('رد درخواست فقط پیش از شروع رسیدگی و با تصمیم مقام مجاز امکان‌پذیر است.');
                }
                return;

            case 'supervisor_close':
                if ($from !== 'resolved' || $to !== 'closed' || $cancelPending) {
                    throw new RuntimeException('بستن نهایی فقط برای درخواست حل‌شده و بدون لغو در انتظار مجاز است.');
                }
                return;

            case 'supervisor_reopen':
                if ($from !== 'closed' || !in_array($to, ['in_progress', 'manager_review'], true)) {
                    throw new RuntimeException('بازگشایی رسمی فقط از وضعیت بسته‌شده مجاز است.');
                }
                return;

            case 'auto_close':
                if ($from !== 'resolved' || $to !== 'closed' || $cancelPending) {
                    throw new RuntimeException('بستن خودکار فقط برای درخواست حل‌شده و بدون لغو در انتظار مجاز است.');
                }
                return;

            default:
                throw new InvalidArgumentException('نوع گذار گردش‌کار ناشناخته است.');
        }
    }
}
