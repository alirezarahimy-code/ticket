<?php
declare(strict_types=1);

/**
 * Expert work scheduling for the Request Center (phase 3).
 *
 * Table: request_center_appointments (migration 035).
 * - Appointments belong to a ticket and an assignee; they never change the
 *   ticket status, SLA or OLA clocks by themselves.
 * - Conflicts are detected inside a transaction with FOR UPDATE on InnoDB
 *   (MariaDB 10.4+ in production/CI; SQLite path used by offline tests has no
 *   row locking and relies on same-transaction consistency instead).
 */
final class AppointmentRepository
{
    public const KINDS = [
        'work'     => 'رسیدگی / اقدام',
        'visit'    => 'بازدید حضوری',
        'await'    => 'انتظار پاسخ',
        'followup' => 'پیگیری',
    ];

    public const STATUS_PLANNED = 'planned';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(private PDO $pdo)
    {
    }

    public function tableExists(): bool
    {
        try {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'request_center_appointments'"
            );
            $st->execute();
            return (bool) $st->fetchColumn();
        } catch (Throwable) {
            try {
                $st = $this->pdo->query(
                    "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'request_center_appointments'"
                );
                return $st ? (bool) $st->fetchColumn() : false;
            } catch (Throwable) {
                return false;
            }
        }
    }

    /** '2026-11-07T14:30' (datetime-local) → '2026-11-07 14:30:00' */
    public static function normalizeDateTime(string $value): ?string
    {
        $value = trim($value);
        $value = str_replace('T', ' ', $value);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})[ ]?(\d{2}):(\d{2})(:(\d{2}))?$/', $value, $m) !== 1) {
            return null;
        }
        $h = (int) $m[4]; $i = (int) $m[5]; $s = isset($m[7]) ? (int) $m[7] : 0;
        if ($h > 23 || $i > 59 || $s > 59) {
            return null;
        }
        $dt = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', sprintf('%04d-%02d-%02d %02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) $m[3], $h, $i, $s));
        if (!$dt) {
            return null;
        }
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Create an appointment. Caller MUST have already checked that the user
     * may see the ticket and schedule for the target assignee.
     *
     * @return array{ok: bool, id?: int, error?: string}
     */
    public function create(int $ticketId, int $assigneeId, string $startsAt, string $endsAt, string $kind, ?string $note, int $actorId): array
    {
        if (!isset(self::KINDS[$kind])) {
            return ['ok' => false, 'error' => 'نوع نوبت نامعتبر است.'];
        }
        $startTs = strtotime($startsAt);
        $endTs = strtotime($endsAt);
        if (!$startTs || !$endTs) {
            return ['ok' => false, 'error' => 'زمان شروع یا پایان نامعتبر است.'];
        }
        if ($endTs <= $startTs) {
            return ['ok' => false, 'error' => 'پایان نوبت باید بعد از شروع آن باشد.'];
        }
        if (($endTs - $startTs) > 8 * 3600) {
            return ['ok' => false, 'error' => 'طول هر نوبت حداکثر ۸ ساعت می‌تواند باشد.'];
        }
        if ($startTs < time() - 3600) {
            return ['ok' => false, 'error' => 'زمان شروع نمی‌تواند در گذشته باشد.'];
        }
        if (!$this->tableExists()) {
            return ['ok' => false, 'error' => 'migration_035_missing'];
        }
        $note = trim((string) $note);
        if (mb_strlen($note) > 500) {
            $note = mb_substr($note, 0, 500);
        }
        $note = $note === '' ? null : $note;

        try {
            $this->pdo->beginTransaction();

            $conflictSql = 'SELECT id FROM request_center_appointments
                            WHERE assignee_id = ? AND status <> ?
                              AND starts_at < ? AND ends_at > ? LIMIT 1 FOR UPDATE';
            try {
                $conflict = $this->pdo->prepare($conflictSql);
                $conflict->execute([$assigneeId, self::STATUS_CANCELLED, $endsAt, $startsAt]);
            } catch (Throwable) {
                // SQLite (offline tests) has no FOR UPDATE — same predicate without the lock.
                $conflict = $this->pdo->prepare('SELECT id FROM request_center_appointments
                    WHERE assignee_id = ? AND status <> ? AND starts_at < ? AND ends_at > ? LIMIT 1');
                $conflict->execute([$assigneeId, self::STATUS_CANCELLED, $endsAt, $startsAt]);
            }
            if ($conflict->fetchColumn() !== false) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'این بازه با نوبت فعال دیگری روی تقویم کارشناس تداخل دارد.'];
            }

            $ins = $this->pdo->prepare('INSERT INTO request_center_appointments
                (ticket_id, assignee_id, starts_at, ends_at, kind, status, note, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $ins->execute([$ticketId, $assigneeId, $startsAt, $endsAt, $kind, self::STATUS_PLANNED, $note, $actorId]);
            $id = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return ['ok' => true, 'id' => $id];
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'ثبت نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }

    /** Next planned appointments (for the signed-in expert, or everyone when $canManageAll). */
    public function listUpcoming(?int $assigneeId, string $now, int $limit = 14): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        $limit = max(1, min(50, $limit));
        $where = "WHERE a.status = ? AND a.starts_at >= ?";
        $params = [self::STATUS_PLANNED, $now];
        if ($assigneeId !== null) {
            $where .= ' AND a.assignee_id = ?';
            $params[] = $assigneeId;
        }
        $st = $this->pdo->prepare("SELECT a.*, t.ticket_no, t.subject, u.full_name AS assignee_name
            FROM request_center_appointments a
            LEFT JOIN tickets t ON t.id = a.ticket_id
            LEFT JOIN users u ON u.id = a.assignee_id
            {$where}
            ORDER BY a.starts_at ASC
            LIMIT {$limit}");
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Cancel an appointment. Owner may cancel own; elevated users may cancel any. */
    public function cancel(int $appointmentId, int $actorId, bool $elevated): array
    {
        if (!$this->tableExists()) {
            return ['ok' => false, 'error' => 'migration_035_missing'];
        }
        try {
            $st = $this->pdo->prepare('SELECT assignee_id, status FROM request_center_appointments WHERE id = ?');
            $st->execute([$appointmentId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return ['ok' => false, 'error' => 'نوبت یافت نشد.'];
            }
            if ((string) $row['status'] !== self::STATUS_PLANNED) {
                return ['ok' => false, 'error' => 'فقط نوبت «برنامه‌ریزی‌شده» قابل لغو است.'];
            }
            if (!$elevated && (int) $row['assignee_id'] !== $actorId) {
                return ['ok' => false, 'error' => 'فقط مالک نوبت یا مدیر می‌تواند آن را لغو کند.'];
            }
            $upd = $this->pdo->prepare("UPDATE request_center_appointments SET status = ? WHERE id = ? AND status = ?");
            $upd->execute([self::STATUS_CANCELLED, $appointmentId, self::STATUS_PLANNED]);
            return ['ok' => $upd->rowCount() > 0];
        } catch (Throwable) {
            return ['ok' => false, 'error' => 'لغو نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }
}
